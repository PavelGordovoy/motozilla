<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionGroupModel extends waModel
{

    protected $table = 'multiform_action_group';

    /**
     * Delete action group
     * @param int $group_id
     * @param int $form_id
     * @return boolean
     */
    public function delete($group_id, $form_id)
    {
        $maf = new multiformActionFormModel();
        $group = $this->getById($group_id);
        if ($this->deleteByField(array("id" => $group_id, "form_id" => $form_id))) {
            $maf->updateByField(array("form_id" => $form_id), array('group_id' => 0));
            // Меняем порядок сортировки
            $sql = "UPDATE {$maf->getTableName()} SET sort = sort -1 WHERE sort > '".(int)$group['sort']."'";
            $maf->exec($sql);
        }

        return true;
    }
}
