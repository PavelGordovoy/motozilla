<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformMaskModel extends waModel
{

    protected $table = 'multiform_mask';

    /**
     * Get all masks
     * @return array
     */
    public function getMasks()
    {
        return $this->getAll('id');
    }

    /**
     * Get form keys
     * @return array
     */
    public function getKeys()
    {
        $keys = array();
        $sql = "SELECT id FROM {$this->table}";
        $result = $this->query($sql);
        foreach ($result as $row) {
            $keys[] = $row['id'];
        }
        return $keys;
    }

    /**
     * Save / update mask info
     * @param int|string $id - Mask id. May be int, it means update, or string, it means save
     * @param array $mask - Mask data
     * @return int|false 
     */
    public function save($id, $mask)
    {
        try {
            // Проверяем имя формы на количество символов
            if (mb_strlen($mask['name'], "UTF-8") > 50) {
                $mask['name'] = mb_substr($mask['name'], 0, 50, "UTF-8");
            }
            if (mb_strlen($mask['mask'], "UTF-8") > 255) {
                $mask['mask'] = mb_substr($mask['mask'], 0, 255, "UTF-8");
            }
            if (mb_strlen($mask['error'], "UTF-8") > 255) {
                $mask['error'] = mb_substr($mask['error'], 0, 255, "UTF-8");
            }
            // Если поле новое типа new%id%, то добавляем данные
            // если %id% - обновляем
            if (!is_int($id)) {
                $mask_id = $this->insert($mask);
            } else {
                $this->updateById($id, $mask);
                $mask_id = $id;
            }
            return $mask_id;
        } catch (Exception $e) {
            multiformHelper::log($e->getMessage());
            return false;
        }
    }

    /**
     * Delete masks
     * @param array[int]|int $ids
     * @return boolean
     */
    public function delete($ids)
    {
        $this->deleteById($ids);
        return true;
    }

}
