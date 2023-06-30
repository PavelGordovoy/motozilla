<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

use Igaponov\Hash;

class multiformRecordModel extends waModel
{

    protected $table = 'multiform_record';

    /**
     * Get records by filter
     *
     * @param array $filter
     * @return array
     */
    public function getRecords($filter = array())
    {
        $mrv = new multiformRecordValuesModel();

        $where = array();

        $order_join = $order = "";

        if (!empty($filter['query']) || (empty($filter['query']) && !empty($filter['query_filter']))) {
            $query_filter = array();
            if (!empty($filter['query_filter'])) {
                foreach ($filter['query_filter'] as $qf) {
                    if (strpos($qf, '-') !== false) {
                        $parts = explode("-", $qf);
                        $query_filter[] = "(mrv.field_id = '" . (int) $parts[0] . "' AND mrv.ext = '" . (int) $parts[1] . "')";
                    } else {
                        $query_filter[] = "mrv.field_id = '" . (int) $qf . "'";
                    }
                }
            }
            $order_join .= "JOIN {$mrv->getTableName()} mrv ON mrv.record_id = r.id ";
            $where[] = "mrv.value " . (!empty($filter['query']) ? " LIKE '%" . multiformHelper::secureString($filter['query']) . "%'" : "= ''") . ($query_filter ? " AND ((" . implode(') OR (', $query_filter) . "))" : '');
        }

        if (!empty($filter['order_by'])) {
            $order .= " ORDER BY ";
            switch ($filter['order_by']) {
                case "status":
                    $order .= "r.status ";
                    break;
                case "ip":
                    $order .= "r.ip ";
                    break;
                case "create_datetime":
                    $order .= "r.create_datetime ";
                    break;
                case "update_datetime":
                    $order .= "r.update_datetime ";
                    break;
                case "create_by":
                case "update_by":
                    $order .= "wc.name ";
                    $order_join .= "LEFT JOIN wa_contact wc ON wc.id = f." . ($filter['order_by'] == 'create_by' ? 'create' : 'update') . "_contact_id ";
                    break;
                case "id":
                    $order .= "r.id ";
                    break;
                default:
                    $order .= (!empty($filter['ext']) && $filter['ext'] == 'file') ? "mfiles.filename " : "mrv2.value ";
                    if (!empty($filter['ext']) && $filter['ext'] == 'file') {
                        $files_model = new multiformFilesModel();
                        $order_join .= " LEFT JOIN {$mrv->getTableName()} mrv2 ON (mrv2.record_id = r.id AND mrv2.field_id = '" . (int) $filter['order_by'] . "')
                        LEFT JOIN {$files_model->getTableName()} mfiles ON mfiles.record_id = mrv2.id ";
                    } else {
                        $order_join .= "LEFT JOIN {$mrv->getTableName()} mrv2 ON (mrv2.record_id = r.id AND mrv2.field_id = '" . (int) $filter['order_by'] . "'";
                        if (!empty($filter['ext'])) {
                            $order_join .= " AND mrv2.ext = '" . $mrv->escape($filter['ext']) . "'";
                        }
                        $order_join .= ') ';
                    }
            }
            $order .= $filter['order_direction'];
        }

        $sql = "SELECT r.* FROM {$this->getTableName()} r $order_join";

        if (isset($filter['form_id'])) {
            $where[] = "r.form_id = '" . (int) $filter['form_id'] . "'";
        }
        if (!empty($filter['contact_id'])) {
            $where[] = "r.create_contact_id = '" . (int) $filter['contact_id'] . "'";
        }
        if (isset($filter['status'])) {
            $where[] = "r.status = '" . (int) $filter['status'] . "'";
        }

        if ($where) {
            $sql .= " WHERE (" . implode(') AND (', $where) . ")";
        }

        $sql .= " GROUP BY r.id $order";

        // Выбираем определенное количество записей
        if (!empty($filter['limit'])) {
            $sql .= " LIMIT " . (int) $filter['limit']['offset'] . "," . (int) $filter['limit']['length'];
        }

        $records = $this->query($sql)->fetchAll('id');

        $ids = array_keys($records);

        if ($ids) {
            $mfiles = new multiformFilesModel();
            $mc = new multiformRecordCommentModel();

            // Комментарии
            $sql = "SELECT * FROM {$mc->getTableName()} WHERE record_id IN (?)";
            foreach ($mc->query($sql, array($ids)) as $com) {
                $records[$com['record_id']]['comments'][] = $com;
            }

            $sql = "SELECT mrv.*, mf.hash, mf.filename, mf.id as file_id FROM {$mrv->getTableName()} mrv 
                LEFT JOIN {$mfiles->getTableName()} mf ON mf.record_id=mrv.id
                WHERE mrv.record_id IN (?)";

            foreach ($mrv->query($sql, array($ids)) as $r) {
                if (isset($records[$r['record_id']])) {
                    if ($r['ext'] !== '') {
                        // Дополнительные параметры
                        if ($r['field_id'] == '0') {
                            $replaced_param = str_replace(" ", "_", $r['ext']);
                            $records[$r['record_id']]['fields'][$r['field_id']][$replaced_param] = $r['value'];
                        } elseif ($r['ext'] == 'file') {
                            if ($r['hash']) {
                                $records[$r['record_id']]['fields'][$r['field_id']][$r['file_id']] = [
                                    'filename' => $r['filename'],
                                    'hash' => $r['hash']
                                ];
                            }
                        } else {
                            $records[$r['record_id']]['fields'][$r['field_id']][$r['ext']] = $r['value'];
                        }
                    } else {
                        $records[$r['record_id']]['fields'][$r['field_id']] = $r['value'];
                    }
                    if ($r['cloned_index'] !== '0') {
                        $records[$r['record_id']]['has_cloned_fields'] = 1;
                    }
                }
            }
        }

        return $records;
    }

    /**
     * Count records by filter
     *
     * @param array $filter
     * @return array
     */
    public function countRecords($filter = array())
    {
        $mrv = new multiformRecordValuesModel();

        $where = array();

        $order_join = "";
        if (!empty($filter['query']) || (empty($filter['query']) && !empty($filter['query_filter']))) {
            $query_filter = array();
            if (!empty($filter['query_filter'])) {
                foreach ($filter['query_filter'] as $qf) {
                    if (strpos($qf, '-') !== false) {
                        $parts = explode("-", $qf);
                        $query_filter[] = "(mrv.field_id = '" . (int) $parts[0] . "' AND mrv.ext = '" . (int) $parts[1] . "')";
                    } else {
                        $query_filter[] = "mrv.field_id = '" . (int) $qf . "'";
                    }
                }
            }
            $order_join .= "JOIN {$mrv->getTableName()} mrv ON mrv.record_id = r.id ";
            $where[] = "mrv.value " . (!empty($filter['query']) ? " LIKE '%" . multiformHelper::secureString($filter['query']) . "%'" : "= ''") . ($query_filter ? " AND ((" . implode(') OR (', $query_filter) . "))" : '');
        }
        if (isset($filter['form_id'])) {
            $where[] = "r.form_id = '" . (int) $filter['form_id'] . "'";
        }

        $sql = "SELECT COUNT(DISTINCT(r.id)) FROM {$this->getTableName()} r {$order_join}";
        if ($where) {
            $sql .= " WHERE (" . implode(') AND (', $where) . ")";
        }
        return $this->query($sql)->fetchField();
    }

    /**
     * Get record
     *
     * @param int $record_id
     * @return array
     */
    public function getRecord($record_id)
    {
        $record = $this->getById($record_id);
        $record['fields'] = $record['cloned_fields'] = array();

        $mrv = new multiformRecordValuesModel();
        $mfiles = new multiformFilesModel();
        $mc = new multiformRecordCommentModel();

        // Комментарии
        $record['comments'] = $mc->getByField("record_id", $record_id, 'id');

        $record_values = [];
        $sql = "SELECT mrv.*, mf.hash, mf.filename, mf.id as file_id FROM {$mrv->getTableName()} mrv 
                LEFT JOIN {$mfiles->getTableName()} mf ON mf.record_id=mrv.id
                WHERE mrv.record_id = i:record_id";
        foreach ($mrv->query($sql, ['record_id' => $record_id]) as $r) {
            $data = [];
            if ($r['ext'] !== '') {
                // Дополнительные параметры
                if ($r['field_id'] == '0') {
                    $replaced_param = str_replace(" ", "_", $r['ext']);
                    $data[$r['field_id']][$replaced_param] = $r['value'];
                } elseif ($r['ext'] == 'file') {
                    if ($r['hash']) {
                        $data[$r['field_id']] = [
                            $r['file_id'] => [
                                'filename' => $r['filename'],
                                'hash' => $r['hash']
                            ]
                        ];
                    }
                } else {
                    $data[$r['field_id']][$r['ext']] = $r['value'];
                }
            } else {
                $data[$r['field_id']] = $r['value'];
            }

            if (!isset($record_values[$r['cloned_index']])) {
                $record_values[$r['cloned_index']] = [];
            }

            $record_values[$r['cloned_index']] = Hash::array_merge_deep($record_values[$r['cloned_index']], $data);
        }
        $expanded_values = Hash::expand($record_values);
        // Значения обычных полей
        if (isset($expanded_values[0])) {
            $record['fields'] = $expanded_values[0];
            unset($expanded_values[0]);
        }
        // Значения дублированных полей
        if (!empty($expanded_values)) {
            $record['cloned_fields'] = $expanded_values;
        }
        return $record;
    }

    /**
     * Get form custom params
     *
     * @param int $form_id
     * @return array
     */
    public function getCustomParams($form_id)
    {
        $custom_params = array();
        $mrv = new multiformRecordValuesModel();
        $sql = "SELECT * FROM {$mrv->getTableName()} mrv LEFT JOIN {$this->getTableName()} mr ON mr.id = mrv.record_id WHERE mr.form_id = i:id AND mrv.field_id = 0";
        foreach ($this->query($sql, array("id" => $form_id)) as $cp) {
            $replaced_param = str_replace(" ", "_", $cp['ext']);
            $custom_params[$replaced_param] = $cp['ext'];
        }
        return $custom_params;
    }

    /**
     * Delete records
     *
     * @param array|int $ids
     * @return bool
     */
    public function delete($ids)
    {
        if (is_array($ids)) {
            $record_ids = "IN (" . implode(",", $this->escape($ids, 'int')) . ")";
            // Удаляем файлы
            foreach ($ids as $id) {
                waFiles::delete(wa()->getDataPath('files/' . $id . '/', false, 'multiform'), true);
            }
        } else {
            $record_ids = "='" . (int) $ids . "'";
            // Удаляем файлы
            waFiles::delete(wa()->getDataPath('files/' . $ids . '/', false, 'multiform'), true);
        }

        $mrv = new multiformRecordValuesModel();
        $mfiles = new multiformFilesModel();
        $mc = new multiformRecordCommentModel();
        $sql = "DELETE r, rv, mfiles, mc FROM {$this->table} r
                LEFT JOIN {$mrv->getTableName()} rv ON r.id = rv.record_id
                LEFT JOIN {$mfiles->getTableName()} mfiles ON rv.id = mfiles.record_id
                LEFT JOIN {$mc->getTableName()} mc ON r.id = mc.record_id
                WHERE r.id $record_ids";
        return $this->exec($sql);
    }

    /**
     * Get all record attachments
     *
     * @param array $record
     * @param array $form
     * @return array
     */
    public function getRecordAttachments($record, $form)
    {
        $attachments = [];
        if (!empty($form['fields'])) {
            $hashes = [];
            $file_fields = [];

            // Находим все поля файлов
            foreach ($form['fields'] as $f) {
                if ($f['type'] == 'file') {
                    $file_fields[$f['id']] = $f['id'];
                }
            }

            foreach ($file_fields as $field_id) {
                if (!empty($record['fields'][$field_id])) {
                    foreach ($record['fields'][$field_id] as $file) {
                        if (!empty($file['hash'])) {
                            $hashes[$file['hash']] = $file['hash'];
                        }
                    }
                }
            }

            if (!empty($record['cloned_fields'])) {
                $hashes += $this->getClonedFiles($record['cloned_fields'], $file_fields);
            }

            if ($hashes) {
                $files = (new multiformFilesModel())->getFileByHash($hashes);
                foreach ($files as $file) {
                    $filepath = multiformFiles::getFilePath($file);
                    if (file_exists($filepath)) {
                        $attachments[] = array(
                            "name" => multiformHelper::secureString($file['filename']),
                            "path" => $filepath
                        );
                    }
                }
            }
        }
        return $attachments;
    }

    /**
     * Get information about contacts in records
     *
     * @param array $records - Array of records
     * @return array
     * @throws waException
     */
    public function getRecordContacts($records)
    {
        $contacts = array();
        foreach ($records as $r) {
            if ($r['create_contact_id']) {
                $contacts[$r['create_contact_id']] = $r['create_contact_id'];
            }
            if ($r['update_contact_id']) {
                $contacts[$r['update_contact_id']] = $r['update_contact_id'];
            }
        }

        $collection = new waContactsCollection('/id/' . implode(",", $contacts) . '/', array('check_rights' => true));
        return $collection->getContacts('id,name');
    }

    /**
     * Helper for getRecordAttachments
     *
     * @param array $cloned_fields
     * @param array $file_fields
     * @return array
     */
    private function getClonedFiles($cloned_fields, $file_fields)
    {
        $hashes = [];
        foreach ($cloned_fields as $cloned_field) {
            foreach ($cloned_field as $cloned) {
                foreach ($file_fields as $field_id) {
                    if (!empty($cloned[$field_id])) {
                        foreach ($cloned[$field_id] as $file) {
                            if (!empty($file['hash'])) {
                                $hashes[$file['hash']] = $file['hash'];
                            }
                        }
                    }
                }
                if (!empty($cloned['_'])) {
                    $hashes += $this->getClonedFiles($cloned['_'], $file_fields);
                }
            }
        }
        return $hashes;
    }
}
