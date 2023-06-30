<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionFormModel extends waModel
{

    protected $table = 'multiform_action_form';

    public function createAction($action_code, $form_id, $parent_id)
    {
        return $this->insert(array("form_id" => $form_id, "action_code" => $action_code, "sort" => $this->getMaxSort($form_id) + 1, "parent_id" => $parent_id));
    }

    public function getFormActions($form_id, $action_id = 0, $parent_id = null)
    {
        $form_actions = array();
        $mafs = new multiformActionFormSettingsModel();
        // Список всех действий
        $ma = new multiformActions();
        $actions = $ma->getActions();
        $sql = "SELECT maf.*, mafs.* FROM {$this->table} maf "
                . "LEFT JOIN {$mafs->getTableName()} mafs ON maf.id = mafs.action_id "
                . "WHERE maf.form_id = '" . (int) $form_id . "' ";
        if ($action_id) {
            $sql .= "AND maf.id = '" . (int) $action_id . "' ";
        }
        if ($parent_id !== null) {
            $sql .= "AND maf.parent_id = '" . (int) $parent_id . "' ";
        }
        $sql .= "ORDER BY maf.sort ASC";
        foreach ($this->query($sql) as $r) {
            // Обрабатываем только существующие действия
            if (isset($actions[$r['action_code']])) {
                if (!isset($form_actions[$r['id']])) {
                    $form_actions[$r['id']] = array(
                        "id" => $r['id'], 
                        "status" => $r['status'], 
                        "parent_id" => $r['parent_id'], 
                        "group_id" => $r['group_id'], 
                        "sort" => $r['sort'], 
                        "form_id" => $r['form_id'], 
                        "action_code" => $r['action_code'], 
                        "fields" => array()
                        );
                    $form_actions[$r['id']] += $actions[$r['action_code']];
                }
                if (!empty($r['ext'])) {
                    if (!isset($form_actions[$r['id']]['fields'][$r['param']])) {
                        $form_actions[$r['id']]['fields'][$r['param']] = array();
                    }
                    $form_actions[$r['id']]['fields'][$r['param']][$r['ext']] = $r['value'];
                } else {
                    $form_actions[$r['id']]['fields'][$r['param']] = $r['value'];
                }
            }
        }
        if ($action_id && isset($form_actions[$action_id])) {
            $return = new multiformAction($form_actions[$action_id]['action_code']);
            $action = $form_actions[$action_id];
            foreach ($action as $k => $a) {
                $return[$k] = $a;
            }
        } else {
            $return = $form_actions;
        }
        return $return;
    }

    /**
     * Maximum sort number of the actions
     * 
     * @param int $form_id
     * @return int
     */
    public function getMaxSort($form_id)
    {
        return (int) $this->select("MAX(sort)")->where("form_id = i:form", array("form" => $form_id))->fetchField();
    }

    /**
     * Delete action
     * @param int $form_id
     * @param int $action_id
     * @return boolean
     */
    public function delete($form_id, $action_id)
    {
        $action = $this->getFormActions($form_id, $action_id);

        // Удаляем прикрепленные файлы
        if (!empty($action['settings'])) {
            foreach ($action['settings'] as $param => $as) {
                if ($as['control_type'] == 'file') {
                    wa()->getDataPath('files/actions/' . $action_id . '/' . $param . '/', empty($as['private']), 'multiform');
                }
            }
        }

        $mafs = new multiformActionFormSettingsModel();
        $sql = "DELETE maf, mafs FROM {$this->table} maf
                LEFT JOIN {$mafs->getTableName()} mafs ON mafs.action_id = maf.id
                WHERE maf.id = '" . (int) $action_id . "' OR maf.parent_id = '" . (int) $action_id . "'";
        $this->exec($sql);

        /**
         * @event multiform_action_form_delete
         * @return void
         */
        $event_params = array("action_id" => $action_id, "action" => $action);
        wa('multiform')->getConfig()->callActionEvent('multiform_action_form_delete', $event_params);

        return true;
    }

}
