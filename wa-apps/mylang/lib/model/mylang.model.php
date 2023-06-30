<?php

/**
 * Class mylangModel
 */
class mylangModel extends waModel
{
    protected $table = 'mylang';
    protected $wa;

    public $elements = array(
        'product' => array(
            'table' => 'shop_product',
        ),
        'category' => array(
            'table' => 'shop_category',
        ),
        'feature' => array(
            'table' => 'shop_feature',
        ),
        'feature_value' => array(
            'varchar' => 'shop_feature_values_varchar',
            'text' => 'shop_feature_values_text',
        ),
        'service' => array(
            'table' => 'shop_service',
        ),
        'service_value' => array(
            'table' => 'shop_service_variants',
        ),
        'tag' => array(
            'table' => 'shop_tag',
        ),
        'stock' => array(
            'table' => 'shop_stock',
        ),
    );

    protected $cache = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function dataInsert($data)
    {
        if (!$data) {
            return true;
        }
        $values = array();
        $second = $multi_field = false;
        $row_values = array();
        foreach ($data as $field => $value) {
            if (isset($this->fields[$field])) {
                if (is_array($value)) {
                    if (!$multi_field) {
                        $multi_field = $field;
                    } else {
                        $second = $field;
                    }
                    $row_values[$this->escapeField($field)] = '';
                } else {
                    $row_values[$this->escapeField($field)] = $this->getFieldValue($field, $value);
                }
            }
        }
        $fields = array_keys($row_values);
        if ($multi_field) {
            foreach ($data[$multi_field] as $key => $v) {
                $row_values[$this->escapeField($multi_field)] = $this->getFieldValue($multi_field, $v);
                $row_values[$this->escapeField($second)] = $this->getFieldValue($second, $data[$second][$key]);
                $values[] = implode(',', $row_values);
            }
        } else {
            $values[] = implode(',', $row_values);
        }
        if ($values) {
            $sql = 'INSERT INTO ' . $this->table . ' (' . implode(',', $fields) . ') VALUES (' . implode(
                    '), (',
                    $values
                ) . ') ON DUPLICATE KEY UPDATE text = VALUES(text)';
            return $this->query($sql);
        }
        return true;
    }

    // CSV import still use it
    public function saveProductLocale($params)
    {
        foreach ($params['data']['mylang'] as $k => $loc) {
            $data = array();
            $data['type'] = 'product';
            $data['type_id'] = $params['data']['id'];
            $data['subtype'] = array_keys($loc);
            $data['text'] = array_values($loc);
            $data['locale'] = $k;
            $this->dataInsert($data);
        }
        $this->removeEmpty();
    }

    public function saveLocale($params, $app_id = null)
    {
        if (isset($params['data'])) {
            $params = $params['data'];
        }
        foreach ($params['mylang'] as $type => $locales) {
            foreach ($locales as $locale => $type_ids) {
                foreach ($type_ids as $type_id => $value) {
                    foreach ($value as $subtype => $text) {
                        $data['type'] = $type;
                        $data['locale'] = $locale;
                        $data['type_id'][] = $ids[] = $type_id;
                        $data['subtype'][] = $subtype;
                        $data['text'][] = $text;
                    }
                }
                $stringed = false;
                foreach ($data as $field => $value) {
                    if (!$stringed && $field !== 'text' && is_array($value) && (count($value) === 1 || count(
                                array_unique($value)
                            ) === 1)) {
                        $data[$field] = $value[0];
                        $stringed = true;
                    }
                }
                if ($app_id) {
                    $data['app_id'] = $app_id;
                }
                $this->dataInsert($data);
                $data = array();
                $data['type'] = $type;
            }
        }
        //TODO make less frequent
        $this->removeEmpty();
    }

    public function removeEmpty()
    {
        $this->exec('DELETE FROM ' . $this->table . " WHERE text = ''");
    }

    /**
     * @param string $type
     * @param string|array $type_ids
     * @param string|null $locale If null we're translating product card in admin panel, so we need all locales and no cache
     * @param null $key
     * @return array Plain strings from table
     */
    public function getPlainValues($type, $type_ids, $locale = null, $key = null)
    {
        if (!is_array($type_ids)) {
            $type_ids = array($type_ids);
        }
        if ($locale) {
            return $this->query(
                'SELECT * FROM ' . $this->table . ' WHERE type = ? AND type_id IN (?) AND locale IN (?)',
                $type,
                $type_ids,
                $locale
            )->fetchAll($key);
        }
        return $this->query(
            'SELECT * FROM ' . $this->table . ' WHERE type = ? AND type_id IN (?)',
            $type,
            $type_ids
        )->fetchAll($key);
    }

    /**
     * @param string $type
     * @param $subtype
     * @param string|array $type_ids
     * @param string|null $locale If null we're translating product card in admin panel, so we need all locales and no cache
     * @return array Plain strings from table
     */
    public function getPlainValuesSubtype($type, $subtype, $type_ids, $locale = null)
    {
        if (!is_array($type_ids)) {
            $type_ids = array($type_ids);
        }
        $values = array();
        if (is_array(reset($type_ids))) {
            foreach ($type_ids as $val) {
                $values = array_merge($values, $val);
            }
            $type_ids = array_unique($values);
        }
        if (empty($type_ids)) {
            return [];
        }
        $sql = 'SELECT text, subtype, type_id FROM ' . $this->table . ' WHERE type = ? AND subtype IN(?) AND type_id IN (?)';
        if ($locale) {
            $sql .= ' AND locale IN (?)';
            $result = $this->query($sql, $type, $subtype, $type_ids, $locale)->fetchAll('subtype', 2);
        } else {
            $result = $this->query($sql, $type, $subtype, $type_ids)->fetchAll('subtype', 2);
        }
        foreach ($result as $type_id => $ids) {
            $new_data[$type_id] = array_combine(array_column($ids, 'type_id'), array_column($ids, 'text'));
        }
        return empty($new_data) ? array() : $new_data;
    }

    /**
     * @param string $type
     * @param string|array $type_ids
     * @param string $locale
     * @param string $app_id
     * @return array
     */
    public function getValues($type, $type_ids, $locale, $app_id = null)
    {
        if ($type === 'feature_value') {
            //will be not cached
            return $this->getFeatureValuesByProducts($type_ids, $locale);
        }
        if (empty($type_ids)) {
            return [];
        }
        $v = ($type === 'product' || $type === 'category') ? 2 : 1;
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE type = ? AND locale IN (?) AND type_id IN (?)';
        if ($app_id) {
            $sql .= " AND app_id = '" . $app_id . "'";
        }
        return $this->query($sql, $type, $locale, $type_ids)->fetchAll('type_id', $v);
    }

    /**
     * @param array $product_ids
     * @param string $locale
     * @return array
     */
    public function getFeatureValuesByProducts($product_ids, $locale)
    {
        $cache_key = $locale . '_' . implode('_', (array)$product_ids);
        if ($cache = new mylangCache()) {
            $featureValues = $cache->get($cache_key, 'mylang_model_features');
            if ($featureValues !== null) {
                return $featureValues;
            }
        }

        $sql = "SELECT pf.product_id, sf.code, fv.id, spm.text, spm.locale FROM shop_product_features pf JOIN shop_feature_values_varchar fv
            ON pf.feature_value_id = fv.id JOIN shop_feature sf ON pf.feature_id = sf.id JOIN mylang spm
            ON (spm.type_id = fv.id AND spm.type='feature_value' AND spm.subtype = sf.type ) WHERE (pf.product_id IN (i:p_ids) AND spm.locale = s:locale)
            UNION SELECT pf.product_id, sf.code, fv.id, spm.text, spm.locale FROM shop_product_features pf JOIN shop_feature_values_text fv
            ON pf.feature_value_id = fv.id JOIN shop_feature sf ON pf.feature_id = sf.id JOIN mylang spm
            ON (spm.type_id = fv.id AND spm.type='feature_value' AND spm.subtype=sf.type) WHERE (pf.product_id IN (i:p_ids) AND spm.locale = s:locale)
            UNION SELECT pf.product_id, sf.code, fv.id, spm.text, spm.locale FROM shop_product_features pf JOIN shop_feature_values_color fv
            ON pf.feature_value_id = fv.id JOIN shop_feature sf ON pf.feature_id = sf.id JOIN mylang spm
            ON (spm.type_id = fv.id AND spm.type='feature_value' AND spm.subtype=sf.type) WHERE (pf.product_id IN (i:p_ids) AND spm.locale = s:locale)";
        $featureValues = $this->query($sql, array('p_ids' => $product_ids, 'locale' => $locale))->fetchAll('code', 2);
        if ($cache !== null) {
            $cache->set($cache_key, $featureValues, 3600, 'mylang_model_features');
        }
        return $featureValues;
    }

    public function purge()
    {
        foreach ($this->elements as $el => $v) {
            if (isset($v['table'])) {
                $this->query(
                    'DELETE FROM ' . $this->table . ' WHERE type = s:type AND type_id IN (SELECT * FROM (SELECT spm.type_id FROM ' . $this->escape(
                        $v['table']
                    ) . ' t2 RIGHT JOIN ' . $this->table . ' spm ON t2.id = spm.type_id WHERE spm.type = s:type AND t2.id IS NULL) AS sub)',
                    array('type' => $el)
                );
            } else {
                foreach ($v as $subtype => $table) {
                    $this->query(
                        'DELETE FROM ' . $this->table . ' WHERE type = s:type AND subtype = s:subtype AND type_id IN (SELECT * FROM (SELECT spm.type_id FROM ' . $this->escape(
                            $table
                        ) . ' t2 RIGHT JOIN ' . $this->table . ' spm ON t2.id = spm.type_id AND spm.subtype = s:subtype WHERE spm.type = s:type AND t2.id IS NULL) AS sub)',
                        array('subtype' => $subtype, 'type' => $el)
                    );
                }
            }
        }
    }

    public function getCount()
    {
        return $this->query(
            'SELECT `locale`,count(locale) as `counter` FROM ' . $this->table . ' GROUP BY locale'
        )->fetchAll('locale', true);
    }

    //TODO. get APP
    public function getTheme($theme_id, $locale = null)
    {
        $sql = 'SELECT type_id, locale, text FROM ' . $this->table . " WHERE type = 'theme' and subtype = ?";
        if ($locale) {
            $sql .= ' AND locale = ?';
        }
        $result = $this->query($sql, $theme_id, $locale)->fetchAll('type_id', 2);
        foreach ($result as $type => $ids) {
            if ($locale) {
                $new_data[$type] = $ids[0]['text'];
            } else {
                $new_data[$type] = array_combine(array_column($ids, 'locale'), array_column($ids, 'text'));
            }
        }
        return ifset($new_data);
    }
}
