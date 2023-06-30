<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFieldModel extends waModel
{

    protected $table = 'multiform_field';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get full field info
     * 
     * @param int $id
     * @param string $type - Can be 'field' or 'form'. Depends on the id
     * @param array $filter
     * @return array
     */
    public function getDataById($id, $type = 'field', $filter = array())
    {
        $form_id = "";
        $fields = $fields_unique_id = $field_unique = array();

        $mo = new multiformFieldOptionsModel();
        $mfp = new multiformFieldParamsModel();

        if (!empty($filter['form_id'])) {
            $form_id .= " AND f.form_id = '" . (int) $filter['form_id'] . "'";
        }

        // Получаем все поля
        $sql = "SELECT f.*, fp.param, fp.ext, fp.value, IF(f.unique_id = '0', f.id, f.unique_id) as unique_id
                FROM {$this->table} f 
                LEFT JOIN {$mfp->getTableName()} fp ON f.id = fp.field_id
                WHERE " . ($type == 'form' ? "f.form_id = " : "f.id = ") . "'" . (int) $id . "' $form_id 
                ORDER BY f.sort";
        foreach ($this->query($sql) as $r) {
            if (!isset($fields[$r['id']])) {
                $fields[$r['id']] = $r;
            }
            // Формирование параметров поля
            if ($r['param']) {
                if ($r['param'] && $r['ext']) {
                    $fields[$r['id']][$r['param']][$r['ext']] = $r['value'];
                    if (!empty($filter['unique_id_key']) && $r['param'] == 'additional' && $r['ext'] == 'section') {
                        $fields[$r['id']][$r['param']][$r['ext']] = isset($fields[$r['value']]) ? $fields[$r['value']]['unique_id'] : $r['value'];
                    }
                } else {
                    $fields[$r['id']][$r['param']] = $r['value'];
                }
            }
            unset($fields[$r['id']]['param'], $fields[$r['id']]['ext'], $fields[$r['id']]['value']);

            if (!empty($filter['unique_id_key'])) {
                $fields_unique_id[$r['unique_id']] = $fields[$r['id']];
                $field_unique[$r['id']] = $r['unique_id'];
            }
        }
        if ($fields) {
            // Получаем опции полей
            $options = $mo->getOptions(array_keys($fields));
            foreach ($options as $field_id => $o) {
                if (isset($fields[$field_id])) {
                    $fields[$field_id]['option_fields'] = $o;
                    $fields[$field_id]['options_sort'] = array_keys($o);

                    if (!empty($filter['unique_id_key'])) {
                        $fields_unique_id[$field_unique[$field_id]]['option_fields'] = $o;
                        $fields_unique_id[$field_unique[$field_id]]['options_sort'] = array_keys($o);
                    }
                }
            }
        }
        $fields = !empty($filter['unique_id_key']) ? $fields_unique_id : $fields;
        return $type == 'field' && $fields ? reset($fields) : $fields;
    }

    /**
     * Create field by the type
     * 
     * @param string $type
     * @param int $form_id
     * @return int - field ID
     */
    public function createField($type, $form_id)
    {
        $data = array();

        // Типы полей
        $types = wa('multiform')->getConfig()->getFieldTypes();

        // Если тип поля не существует, то прерываем обработку
        if (!isset($types[$type])) {
            return false;
        }
        $data['form_id'] = $form_id;
        $data['type'] = substr($type, 0, 50);
        $data['title'] = !empty($types[$type]['save_params']['name']) ? $types[$type]['save_params']['name'] : _w('Unknown field');
        $data['code'] = !empty($types[$type]['save_params']['code']) ? preg_replace('/[^a-zA-Z\-_]/', '', $types[$type]['save_params']['code']) : 'unknown_code';
        $data['sort'] = $this->getMaxSort($form_id) + 1;
        $data['unique_id'] = $this->getUniqueId($form_id);

        $id = $this->insert($data);

        $data['id'] = $id;
        // Добавляем поля выбора для списков
        if ($id && isset($types[$type]['save_params']['options'])) {
            $option_model = new multiformFieldOptionsModel();
            $option_model->add($id, $types[$type]['save_params']['options']);
        }

        // Дополнителная обработка поля при создании
        if ($instance = multiformFormBuilder::getFieldInstance($type)) {
            $name = "before" . ucfirst($type) . "Create";
            if (method_exists($instance, $name)) {
                $result = $instance->$name($data);
            }
        }

        return $id;
    }

    /**
     * Maximum sort number of the form fields
     * 
     * @param int $form_id
     * @return int
     */
    private function getMaxSort($form_id)
    {
        return (int) $this->select("MAX(sort)")->where("form_id = i:form", array("form" => $form_id))->fetchField();
    }

    /**
     * Maximum unique ID of the form fields
     * 
     * @param int $form_id
     * @return int
     */
    private function getUniqueId($form_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE form_id = '" . (int) $form_id . "'";
        $unique_id = 0;
        $unique_ids = array();
        foreach ($this->query($sql) as $r) {
            if ($r['unique_id']) {
                $unique_ids[] = $r['unique_id'];
            } else {
                $unique_ids[] = $r['id'];
            }
        }

        do {
            $unique_id++;
        } while (in_array($unique_id, $unique_ids));

        return $unique_id;
    }

    /**
     * Get field keys of form
     * @param int $form_id
     * @return array
     */
    public function getFieldKeys($form_id)
    {
        $keys = array();
        $sql = "SELECT id FROM {$this->table} WHERE form_id = '" . (int) $form_id . "'";
        $result = $this->query($sql);

        foreach ($result as $row) {
            $keys[] = $row['id'];
        }
        return $keys;
    }

    /**
     * Delete fields 
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        $mom = new multiformFieldOptionsModel();
        $mfieldp = new multiformFieldParamsModel();
        $mc = new multiformConditionModel();
        $mcp = new multiformConditionParamsModel();
        $mfp = new multiformFormParamsModel();
        $mrv = new multiformRecordValuesModel();
        $mfiles = new multiformFilesModel();

        if ($field = $this->getDataById($id)) {
            // Удаляем варианты поля
            if (!empty($field['options_sort'])) {
                $mom->delete($field['options_sort'], $id);
                waFiles::delete(wa()->getDataPath('fields/' . $id, true, 'multiform'), true);
            }
            // Удаляем прикрепленные файлы полей
            if (!empty($field['display']['attach_file'])) {
                waFiles::delete(wa()->getDataPath('fields/' . $id, false, 'multiform'), true);
            }
            // Удаляем значение из таблицы параметров формы
            $mfp->deleteByField(array("param" => "confirmation_send_to", "value" => $id));

            // Удаляем файлы, переданные при помощи этого поля
            $record_ids = $mrv->select("DISTINCT(record_id)")->where("field_id = :id AND ext = 'file'", array("id" => $id))->fetchAll(null, true);
            if ($record_ids) {
                foreach ($record_ids as $r_id) {
                    waFiles::delete(wa()->getDataPath('files/' . $r_id . '/' . $id . '/', false, 'multiform'), true);
                    waFiles::delete(wa()->getDataPath('files/' . $r_id . '/' . $id . '/', true, 'multiform'), true);
                }
            }

            $sql = "DELETE f, mom, mfieldp, mfiles, mrv, mc, mcp FROM {$this->table} f
                    LEFT JOIN {$mom->getTableName()} mom ON f.id = mom.field_id
                    LEFT JOIN {$mfieldp->getTableName()} mfieldp ON f.id = mfieldp.field_id
                    LEFT JOIN {$mrv->getTableName()} mrv ON f.id = mrv.field_id
                    LEFT JOIN {$mc->getTableName()} mc ON f.id = mc.target
                    LEFT JOIN {$mcp->getTableName()} mcp ON f.id = mcp.source
                    LEFT JOIN {$mfiles->getTableName()} mfiles ON mfiles.record_id = mrv.id
                    WHERE f.id = '" . (int) $id . "'";

            $result = $this->exec($sql);

            // Дополнителные действия после удаления поля
            $prepare_type = "after" . ucfirst($field['type']) . "Delete";
            $instance = multiformFormBuilder::getFieldInstance($field['type']);
            if (method_exists($instance, $prepare_type)) {
                $instance->$prepare_type($field);
            }

            /**
             * @event multiform_field_delete
             * @return void
             */
            $event_params = array("id" => $id, "field" => $field);
            wa()->event('multiform_field_delete', $event_params);

            return $result;
        }
        return false;
    }

    /**
     * Duplicate field
     * 
     * @param int $field_id
     * @return boolean|int
     */
    public function duplicate($field_id)
    {
        if ($field = $this->getById($field_id)) {
            if ($field['type'] == 'section') {
                return $this->duplicateSection($field, $field_id);
            } else {
                return $this->duplicateField($field, $field_id);
            }
        } else {
            return false;
        }
    }

    public function duplicateField($field, $field_id)
    {
        $clon_id = $this->createField($field['type'], $field['form_id']);

        try {
            // Обновляем основную информацию о поле
            $data = $field;
            unset($data['id'], $data['form_id'], $data['unique_id']);
            $this->updateById($clon_id, $data);

            // Параметры поля
            $data_params = array();
            $params_model = new multiformFieldParamsModel();
            $sql = "SELECT * FROM {$params_model->getTableName()} WHERE field_id = '" . (int) $field_id . "'";
            foreach ($params_model->query($sql) as $r) {
                $data_params[] = array(
                    'field_id' => $clon_id,
                    'param' => $r['param'],
                    'ext' => $r['ext'],
                    'value' => $r['value']
                );
                // Копируем прикрепленный файл
                if ($r['ext'] == 'attach_file') {
                    $attach_path = multiformFiles::getAttachmentPath($field_id);
                    if (file_exists($attach_path)) {
                        waFiles::copy($attach_path, multiformFiles::getAttachmentPath($clon_id));
                    }
                }
            }
            $params_model->deleteByField('field_id', $clon_id);
            if ($data_params) {
                $params_model->multipleInsert($data_params);
            }

            if (in_array($field['type'], array('radio', 'checkbox', 'select', 'table_grid'))) {
                // Варианты поля
                $options_model = new multiformFieldOptionsModel();
                $data_options = $options_model->getByField('field_id', $field_id, true);

                $options_model->deleteByField('field_id', $clon_id);

                foreach ($data_options as $o) {
                    $data_opt = $o;
                    $data_opt['field_id'] = $clon_id;
                    unset($data_opt['id']);
                    $option_clon_id = $options_model->insert($data_opt);
                    if ($o['image']) {
                        waFiles::copy(multiformImage::getOptionImagePath($o['id'], $o['field_id']), multiformImage::getOptionImagePath($option_clon_id, $clon_id));
                    }
                }
            }
            return $clon_id;
        } catch (Exception $e) {
            $this->delete($clon_id);
            multiformHelper::log($e->getMessage());
            return false;
        }
    }

    private function duplicateSection($field, $field_id)
    {
        // Дублируем саму секцию
        $section_id = $this->duplicateField($field, $field_id);
        // Получаем список полей, принадлежащий секции
        $params_model = new multiformFieldParamsModel();
        $fields = $params_model->select('field_id')->where("param = 'additional' AND ext = 'section' AND value = '" . (int) $field_id . "'")->fetchAll(null, true);
        if ($fields) {
            foreach ($fields as $f_id) {
                $id = $this->duplicate($f_id);
                $params_model->updateByField(array('field_id' => $id, 'param' => 'additional', 'ext' => 'section'), array('value' => $section_id));
            }
        }
        return $section_id;
    }

    /**
     * Build tree
     * 
     * @param array $data
     * @return array
     */
    public function getTree($data, $parent_id = 0)
    {
        $tree = array();

        foreach ($data as &$element) {
            $element['parent_id'] = !empty($element['additional']['section']) ? $element['additional']['section'] : 0;
            if ($element['parent_id'] == $parent_id) {
                $children = $this->getTree($data, $element['id']);
                if ($children) {
                    $element['fields'] = $children;
                }
                $tree[] = $element;
            }
        }
        return $tree;
    }

}
