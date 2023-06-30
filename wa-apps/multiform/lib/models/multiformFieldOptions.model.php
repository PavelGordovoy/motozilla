<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFieldOptionsModel extends waModel
{

    protected $table = 'multiform_field_options';

    /**
     * Add options
     * @param int $field_id
     * @param array $options
     * @return boolean
     */
    public function add($field_id, $options)
    {
        $insert = array();
        if ($options) {
            foreach ($options as $option_key => $option) {
                // Если поле превышает допустимые размеры - обрезаем его
                if (mb_strlen($option['value'], "UTF-8") > 255) {
                    $option['value'] = mb_substr($option['value'], 0, 255, "UTF-8");
                }
                if (!empty($option['key']) && mb_strlen($option_key, "UTF-8") > 255) {
                    $option['key'] = mb_substr($option['key'], 0, 255, "UTF-8");
                }
                if (!empty($option['ext']) && mb_strlen($option['ext'], "UTF-8") > 30) {
                    $option['ext'] = mb_substr($option['ext'], 0, 30, "UTF-8");
                }
                $insert[] = array(
                    "field_id" => $field_id,
                    "key" => !empty($option['key']) ? $option['key'] : "",
                    "value" => $option['value'],
                    "image" => !empty($option['image']) ? $option['image'] : "",
                    "checked" => isset($option['checked']) ? $option['checked'] : 0,
                    "sort" => $option['sort'],
                    "ext" => !empty($option['ext']) ? $option['ext'] : '',
                );
            }
            return $this->multipleInsert($insert);
        }
        return true;
    }

    /**
     * Get options for fields
     * @param array[int] $fields
     * @return array
     */
    public function getOptions($fields)
    {
        $sql = "SELECT * FROM {$this->table} WHERE field_id IN ('" . implode("','", $fields) . "')ORDER BY sort ASC";
        $options = array();
        foreach ($this->query($sql) as $option) {
            if ($option['ext']) {
                $options[$option['field_id']][$option['ext']][$option['id']] = $option;
            } else {
                $options[$option['field_id']][$option['id']] = $option;
            }
        }
        return $options;
    }

    /**
     * Get option IDs
     * @param int $field_id
     * 
     * @return array
     */
    public function getOptionIDs($field_id)
    {
        return $this->select('id')->where("field_id = i:f_id", array("f_id" => $field_id))->fetchAll(null, true);
    }

    /**
     * Delete field options
     * 
     * @param array|int $option_ids
     * @param int $field_id
     * @return bool
     */
    public function delete($option_ids, $field_id)
    {
        if (is_array($option_ids)) {
            $ids = "IN ('" . implode("','", $this->escape($option_ids, 'int')) . "')";
            foreach ($option_ids as $i) {
                multiformImage::deleteImage($i, $field_id, true);
            }
        } else {
            $ids = "='" . (int) $option_ids . "'";
            multiformImage::deleteImage($option_ids, $field_id, true);
        }

        $sql = "DELETE o FROM {$this->table} o WHERE o.id $ids";
        return $this->exec($sql);
    }

}
