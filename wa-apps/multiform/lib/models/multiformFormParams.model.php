<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormParamsModel extends waModel
{

    protected $table = 'multiform_form_params';

    /**
     * Insert params
     * 
     * @param int $id
     * @param array $params
     * @param string $ext
     * @return boolean
     */
    public function save($id, $params, $ext = 'settings')
    {
        // Очищаем таблицу
        $this->deleteByField(array("form_id" => $id, "ext" => $ext));
        // Формируем данные для встраивания
        $data = array();
        foreach ($params as $param => $value) {
            $data[] = array(
                "form_id" => $id,
                "param" => $param,
                "ext" => $ext,
                "value" => $value
            );
        }
        // Вставляем данные
        if ($data) {
            return $this->multipleInsert($data);
        }
        return true;
    }

    /**
     * Get params
     * 
     * @param int $form_id
     * @return array
     */
    public function getParams($form_id)
    {
        $result = array();
        $sql = "SELECT * FROM {$this->table} WHERE form_id = '" . (int) $form_id . "'";
        foreach ($this->query($sql) as $r) {
            if ($r['param'] == 'domain_limitations' && $r['value']) {
                $r['value'] = unserialize($r['value']);
            }
            $result[$r['param']] = $r['value'];
        }
        return $result;
    }

}
