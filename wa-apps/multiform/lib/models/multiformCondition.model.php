<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformConditionModel extends waModel
{

    protected $table = 'multiform_condition';

    /**
     * Get conditions for the form
     * @param int $id
     * @return boolean
     */
    public function getConditions($id, $tab = '', $extra_tab = '')
    {
        $conditions = $result = array();
        $sql = "SELECT * FROM {$this->getTableName()} WHERE form_id = '" . (int) $id . "'";
        if ($tab) {
            $sql .= " AND tab = '" . $this->escape($tab) . "'";
        }
        if ($extra_tab) {
            $sql .= " AND extra_tab = '" . $this->escape($extra_tab) . "'";
        }
        $sql .= "ORDER BY sort";
        foreach ($this->query($sql) as $r) {
            if ($r['tab'] == 'form' && $r['target']) {
                $data = @unserialize($r['target']);
                if ($data !== false) {
                    $r['target'] = $data;
                }
            }
            $result[$r['id']] = $r;
        }
        if ($result) {
            $params_model = new multiformConditionParamsModel();
            $params_results = $params_model->query("SELECT * FROM {$params_model->getTableName()} WHERE condition_id IN(?)", array(array_keys($result)));
            foreach ($params_results as $pr) {
                $result[$pr['condition_id']]['params'][] = $pr;
            }
            foreach ($result as $res) {
                $conditions[$res['tab']][$res['id']] = $res;
            }
        }
        return $tab ? (isset($conditions[$tab]) ? $conditions[$tab] : array()) : $conditions;
    }

    /**
     * Delete conditions 
     * @param int $id
     * @param string $tab - if specified, than delete by tab also
     * @param string $extra_tab - if specified, than delete by extra_tab also
     * @return boolean
     */
    public function delete($id, $tab = null, $extra_tab = null)
    {
        $params_model = new multiformConditionParamsModel();
        $where = "";

        if ($tab) {
            $where .= " AND c.tab = '" . $this->escape($tab) . "'";
        }
        if ($extra_tab) {
            $where .= " AND c.extra_tab = '" . $this->escape($extra_tab) . "'";
        }

        $sql = "DELETE c, cp FROM {$this->table} c
                LEFT JOIN {$params_model->getTableName()} cp ON c.id = cp.condition_id
                WHERE c.form_id = '" . (int) $id . "'$where";
        return $this->exec($sql);
    }

}
