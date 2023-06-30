<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFieldParamsModel extends waModel
{

    protected $table = 'multiform_field_params';

    /**
     * Save field params
     * 
     * @param int $field_id
     * @param array $data
     */
    public function save($field_id, $data)
    {
        // Очищаем старую информацию
        $this->exec("DELETE FROM {$this->table} WHERE field_id = '".(int) $field_id."' AND param <> 'additional'");

        $params = array();
        foreach ($data as $f_id => $value) {
            if (is_array($value)) {
                foreach ($value as $v_id => $v) {
                    $params[] = array('field_id' => $field_id, 'param' => $f_id, 'ext' => $v_id, 'value' => $v);
                }
            } else {
                $params[] = array('field_id' => $field_id, 'param' => $f_id, 'ext' => '', 'value' => $value);
            }
        }
        if ($params) {
            $this->multipleInsert($params);
        }
    }
    
}
