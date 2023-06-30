<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormModel extends waModel
{

    protected $table = 'multiform_form';

    /**
     * Create new form
     *
     * @return int - form ID
     */
    public function createForm()
    {
        $id = $this->insert(array(
            'name' => _w('Untitled form'),
            'description' => _w('This is my new form!'),
            'create_datetime' => date("Y-m-d H:i:s"),
            'create_contact_id' => wa()->getUser()->getId()
        ));

        $hash = md5($id . date("Y-m-d H:i:s"));
        $form_hash = substr($hash, rand(0, 7), 7) . substr($hash, rand(15, 22), 7);
        $this->updateById($id, array("hash" => $form_hash));

        // Устанавливаем время последнего посещения 
        $contact_model = new waContactSettingsModel();
        if (wa()->getUser()->getId()) {
            $contact_model->set(wa()->getUser()->getId(), 'multiform', 'multiform_last_datetime_' . $id, date("Y-m-d H:i:s"));
        }

        return $id;
    }

    /**
     * Get forms
     * @param array $filter
     * @return array
     */
    public function getForms($filter = array())
    {
        $order = "";
        $order_join = "";
        $forms = array();

        $form_records = new multiformRecordModel();

        if (!empty($filter['order_by'])) {
            $order .= " ORDER BY ";
            switch ($filter['order_by']) {
                case "name":
                    $order .= "f.name ";
                    break;
                case "count":
                    $order .= "count ";
                    break;
                case "create_datetime":
                    $order .= "f.create_datetime ";
                    break;
                case "last_record":
                    $order .= "MAX(r.create_datetime) ";
                    break;
                case "start":
                    $order .= "f.start ";
                    break;
                case "end":
                    $order .= "f.end ";
                    break;
                case "create_by":
                    $order .= "wc.name ";
                    $order_join .= "LEFT JOIN wa_contact wc ON wc.id = f.create_contact_id ";
                    break;
                default:
                    $order .= "f.id ";
            }
            $order .= $filter['order_direction'];
        }

        // Последнее время просмотра записей
        $contact_model = new waContactSettingsModel();
        $contact_settings = $contact_model->get(wa()->getUser()->getId(), 'multiform');
        $last_form_record = array();

        $sql = "SELECT f.*, COUNT(r.id) as count, MAX(r.create_datetime) as last_record FROM {$this->table} f "
            . "LEFT JOIN {$form_records->getTableName()} r ON f.id = r.form_id "
            . $order_join
            . " WHERE 1";

        if (isset($filter['ids'])) {
            $sql .= " AND " . (isset($filter['contact_id']) ? "(" : "") . "f.id IN ('" . implode("','", $this->escape($filter['ids'], 'int')) . "')";
        }
        if (isset($filter['contact_id'])) {
            $sql .= (isset($filter['ids']) ? " OR" : " AND") . " f.create_contact_id = '" . (int) $filter['contact_id'] . "'" . (isset($filter['ids']) ? ")" : "");
        }
        $sql .= " GROUP BY f.id $order";
        foreach ($this->query($sql) as $r) {
            $forms[$r['id']] = $r;
            // Последнее время просмотра записей
            if (isset($contact_settings['multiform_last_datetime_' . $r['id']])) {
                $last_form_record[$r['id']] = $contact_settings['multiform_last_datetime_' . $r['id']];
            }
        }
        // Вычисляем количество непросмотренных записей        
        $sql2 = "SELECT COUNT(r.id) as count, f.id FROM {$this->getTableName()} f"
            . " LEFT JOIN {$form_records->getTableName()} r ON f.id = r.form_id  WHERE ";

        if ($last_form_record) {
            $values = array();
            foreach ($last_form_record as $lfr_id => $lfr) {
                $values[] = "(f.id = '" . (int) $lfr_id . "' AND r.create_datetime > '" . $this->escape($lfr) . "')";
            }
            $sql2 .= implode(" OR ", $values);
        } else {
            $sql2 .= "1";
        }
        $sql2 .= " GROUP BY f.id";
        foreach ($this->query($sql2) as $r2) {
            if (isset($forms[$r2['id']])) {
                $forms[$r2['id']]['unviewed_records'] = $r2['count'];
            }
        }

        if ($forms) {
            // Выбираем определенное количество записей
            if (isset($filter['limit']['offset']) && isset($filter['limit']['length'])) {
                $forms = array_slice($forms, $filter['limit']['offset'], $filter['limit']['length'], true);
            }
            if (isset($filter['with_params'])) {
                $form_params = new multiformFormParamsModel();
                foreach ($forms as &$f) {
                    $f += $form_params->getParams($f['id']);
                }
            }
        }

        return $forms;
    }

    public function countAllForms($filter)
    {
        $sql = "SELECT COUNT(*) FROM {$this->getTableName()} WHERE 1";
        if (isset($filter['ids'])) {
            $sql .= " AND " . (isset($filter['contact_id']) ? "(" : "") . "id IN ('" . implode("','", $this->escape($filter['ids'], 'int')) . "')";
        }
        if (isset($filter['contact_id'])) {
            $sql .= (isset($filter['ids']) ? " OR" : " AND") . " create_contact_id = '" . (int) $filter['contact_id'] . "'" . (isset($filter['ids']) ? ")" : "");
        }
        return $this->query($sql)->fetchField();
    }

    /**
     * Get form information
     *
     * @param int $id
     * @return array
     */
    public function getFormSettings($id)
    {
        $form_params = new multiformFormParamsModel();
        $form = $this->getById($id);
        if ($form) {
            $form += $form_params->getParams($id);

            // Последнее время просмотра записей
            $contact_model = new waContactSettingsModel();
            $last_visit = $contact_model->getOne(wa()->getUser()->getId(), 'multiform', 'multiform_last_datetime_' . $id);
            if ($last_visit) {
                $form_records = new multiformRecordModel();
                // Вычисляем количество непросмотренных записей        
                $sql = "SELECT COUNT(r.id) as count, f.id FROM {$this->getTableName()} f"
                    . " LEFT JOIN {$form_records->getTableName()} r ON f.id = r.form_id "
                    . "WHERE f.id = '" . (int) $id . "' AND r.create_datetime > '" . $this->escape($last_visit) . "' GROUP BY f.id";
                $form['unviewed_records'] = $this->query($sql)->fetchField();
            }
        }
        return $form;
    }

    /**
     * Get form settings and fields
     *
     * @param int $id
     * @param int $field_unique_id_key
     * @return array
     * @throws waException
     */
    public function getFullForm($id, $field_unique_id_key = 0)
    {
        static $forms = array();

        // Если уже была выборка по данной форме
        if (isset($forms[$id][$field_unique_id_key])) {
            return $forms[$id][$field_unique_id_key];
        } else {
            $mf = new multiformFieldModel();
            // Настройки формы
            $form = $this->getFormSettings($id);
            // Поля формы
            $form['fields'] = $mf->getDataById($id, 'form', array("unique_id_key" => $field_unique_id_key));

            if (!empty($form['fields'])) {
                // Для секций получаем детей
                $instance = multiformFormBuilder::getFieldInstance('section');
                $instance->addSectionChildsForFields($form['fields'], 'collect', [], $field_unique_id_key);
            }

            // Если имеются условия, то получаем их
            if (!empty($form['condition_status'])) {
                $condition_model = new multiformConditionModel();
                $form['conditions'] = $condition_model->getConditions($id);
            }

            // Действия формы
            $form['actions'] = wao(new multiformActionFormModel())->getFormActions($id, 0, 0);
            if ($form['actions']) {
                $actions = wao(new multiformActions())->getActions();
                foreach ($form['actions'] as &$fa) {
                    $fa['info'] = $actions[$fa['action_code']];
                    unset($fa['info']['settings']);
                }
            }

            $forms[$id][$field_unique_id_key] = $form;
        }
        return $form;
    }

    /**
     * Get form id by URL or ID
     * @param int|string $field
     * @param array $filter - [status]
     * @return int|false
     */
    public function getFormIdByField($field, $filter = array())
    {
        $status = "";
        if (isset($filter['status'])) {
            $status = " AND status = '" . (int) $filter['status'] . "'";
        }
        if ($field) {
            $id = $this->select("id")->where("id = '" . (int) $field . "'" . $status)->fetchField();
            if ($id) {
                return $id;
            } else {
                return $this->select("id")->where("url = '" . $this->escape($field) . "'" . $status)->fetchField();
            }
        } else {
            return false;
        }
    }

    /**
     * Get form ID by url
     * @param string $url
     * @return int
     */
    public function getIdByUrl($url)
    {
        if ($url) {
            if ($form = $this->getByField("hash", $url)) {
                return $form['id'];
            } elseif ($form = $this->getByField("url", $url)) {
                return $form['id'];
            }
        }
        return 0;
    }

    /**
     * Get form keys
     * @return array
     */
    public function getFormKeys()
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
     * Delete forms
     * @param int $id
     * @return boolean
     */
    public function delete($id)
    {
        $mfm = new multiformFieldModel();
        $mrm = new multiformRecordModel();

        $form = $this->getFullForm($id);

        // Удаляем поля формы
        if (!empty($form['fields'])) {
            foreach ($form['fields'] as $field) {
                $mfm->delete($field['id']);
            }
        }

        // Удаляем записи
        $records = $mrm->getRecords(array('form_id' => $id));
        if (!empty($records)) {
            $mrm->delete(array_keys($records));
        }

        // Удаляем прикрепленные файлы к HTML полям
        waFiles::delete(wa()->getDataPath('multiform/' . $form['id'], true, 'site', false));

        $mfp = new multiformFormParamsModel();
        $mcm = new multiformConditionModel();
        $mcpm = new multiformConditionParamsModel();
        $sql = "DELETE f, mfp, mcm, mcpm FROM {$this->table} f
                LEFT JOIN {$mfp->getTableName()} mfp ON f.id = mfp.form_id
                LEFT JOIN {$mcm->getTableName()} mcm ON f.id = mcm.form_id
                LEFT JOIN {$mcpm->getTableName()} mcpm ON mcm.id = mcpm.condition_id
                WHERE f.id = '" . (int) $id . "'";
        $this->exec($sql);

        return true;
    }

    /**
     * Create new url from existing, if it duplicates.
     * @param int $id
     * @param string $url
     * @return string
     */
    public function generateUrl($id, $url)
    {
        $urls = array();
        $sql = "SELECT url FROM {$this->table} WHERE id <> '" . (int) $id . "'";
        $result = $this->query($sql);
        foreach ($result as $row) {
            $urls[] = $row['url'];
        }
        $count = 0;
        while (in_array($url, $urls)) {
            if (preg_match("/\_(\d+)/", substr($url, -2), $matches) && !empty($matches[1])) {
                $count = (int) $matches[1] + 1;
                $url = substr($url, 0, strrpos($url, "_")) . '_' . $count;
            } else {
                $count++;
                $url .= '_' . $count;
            }
        }
        return $url;
    }

    public function duplicate($id)
    {
        if ($form = $this->getFullForm($id)) {
            $clon_id = $this->createForm();

            // Основная информация
            $data = array(
                "url" => $this->generateUrl($clon_id, $form['url']),
                "name" => $form['name'],
                "title" => $form['title'],
                "status" => $form['status'],
                "description" => $form['description'],
                "type" => $form['type'],
                "start" => $form['start'],
                "end" => $form['end'],
                "meta_title" => $form['meta_title'],
                "meta_keywords" => $form['meta_keywords'],
                "meta_description" => $form['meta_description'],
            );
            $this->updateById($clon_id, $data);

            $mf = new multiformFieldModel();
            $params_model = new multiformFieldParamsModel();
            // Поля формы
            if (!empty($form['fields'])) {
                $count = 0;
                $field_clone_ids = $active_section = $field_unique_ids = array();
                foreach ($form['fields'] as $f) {
                    if ($field_id = $mf->duplicateField($f, $f['id'])) {
                        // Если поле принадлежит секции, изменяем данные об этом в БД
                        $parent_id = !empty($f['additional']['section']) ? $f['additional']['section'] : 0;
                        if (!empty($active_section[$parent_id])) {
                            $params_model->updateByField(array('field_id' => $field_id, 'param' => 'additional', 'ext' => 'section'), array('value' => $active_section[$parent_id]));
                        }
                        if ($f['type'] == 'section') {
                            $active_section[$f['id']] = $field_id;
                        }
                        $clone_unique_id = $count + 1;
                        $mf->updateById($field_id, array("form_id" => $clon_id, "sort" => $count, "unique_id" => $clone_unique_id));

                        $field_clone_ids[$f['id']] = $field_id;
                        $field_unique_ids[$f['unique_id']] = $clone_unique_id;
                    }
                    $count++;
                }
            }

            // Переменные шаблона для замены unique_id
            $replace_pairs = array();
            foreach ($field_unique_ids as $unique_id => $new_unique_id) {
                $replace_pairs['{Field:' . $unique_id . '}'] = '{Field:' . $new_unique_id . '}';
                $replace_pairs['{Record:' . $unique_id . '}'] = '{Record:' . $new_unique_id . '}';
            }

            // Параметры формы
            $mfp = new multiformFormParamsModel();
            $new_params = $mfp->getByField('form_id', $id, true);
            foreach ($new_params as &$p) {
                $p['form_id'] = $clon_id;
            }
            //Изменение unique_id
            $new_params = json_decode(strtr(json_encode($new_params), $replace_pairs), true);

            $mfp->multipleInsert($new_params);

            // Условия
            if (!empty($form['conditions'])) {
                // Получаем все поля новой формы
                $clone_form = $this->getFullForm($clon_id);
                $condition_model = new multiformConditionModel();
                $params_model = new multiformConditionParamsModel();
                foreach ($form['conditions'] as $tab => $conditions) {
                    $c = 0;
                    foreach ($conditions as $condition) {
                        $params = array();
                        // Основные данные условия
                        if ($tab == 'field') {
                            $target = isset($field_clone_ids[$condition['target']]) ? $field_clone_ids[$condition['target']] : 0;
                        } else {
                            $target = json_decode(strtr(json_encode($condition['target']), $replace_pairs), true);
                            if (is_array($target)) {
                                $target = serialize($target);
                            }
                        }
                        $data = array(
                            'form_id' => $clon_id,
                            'action' => $condition['action'],
                            'target' => $target,
                            'andor' => (int) $condition['andor'],
                            'sort' => $c,
                            'tab' => $tab,
                        );
                        $c++;

                        $condition_id = $condition_model->insert($data);
                        if ($condition_id) {
                            // Обрабатываем параметры условия
                            foreach ($condition['params'] as $param) {
                                $source = isset($field_clone_ids[$param['source']]) ? $field_clone_ids[$param['source']] : 0;
                                $source_ext = 0;
                                if (!empty($param['source_ext']) && !empty($form['fields'][$param['source']]['options_sort'])) {
                                    $option_sort_key = array_search($param['source_ext'], $form['fields'][$param['source']]['options_sort']);
                                    $source_ext = !empty($clone_form['fields'][$source]['options_sort'][$option_sort_key]) ? (int) $clone_form['fields'][$source]['options_sort'][$option_sort_key] : 0;
                                }
                                $params[] = array(
                                    'condition_id' => (int) $condition_id,
                                    'source' => $source,
                                    'source_ext' => $source_ext,
                                    'operator' => $param['operator'],
                                    'value' => (mb_strlen($param['value'], "UTF-8") > 255 ? mb_substr($param['value'], 0, 255, "UTF-8") : $param['value']),
                                );
                            }
                            if ($params) {
                                $params_model->multipleInsert($params);
                            }
                        }
                    }
                }
            }

            return $clon_id;
        } else {
            return false;
        }
    }

    /**
     * Get form settings by file
     *
     * @param array $file
     * @return array
     */
    public function getFormByFile($file)
    {
        $mf = new multiformFieldModel();
        $sql = "SELECT f.id FROM {$mf->getTableName()} mf 
                LEFT JOIN {$this->getTableName()} f ON f.id = mf.form_id 
                WHERE mf.id = i:field_id";
        $form_id = $mf->query($sql, ['field_id' => $file['field_id']])->fetchField();
        return $this->getFormSettings($form_id);
    }

}
