<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformViewHelper extends waAppViewHelper
{

    /**
     * Get html form
     * @param int|string $id - id of form
     * @param array $options
     *      $options => array(
     *          'params' => array|string - скрытые параметры, передаваемые в форму.
     *                      Н-р, чтобы передать одно значение: 'params' => 'Мое значение'
     *                           чтобы передать несколько значений: 'params' => array('Цвет' => 'Красный', 'Размер' => 'S')
     *          'default' => [
     *              unique_id => array|string
     *                           В поля можно передавать произвольные параметры, если это не запрещено полем.
     *                           Для этого нужно передать массив, в качестве значения которого будет поле value, а остальные поля - параметры.
     *                           Н-р, для поля "Число": ['value' => 2, 'min' => 4, 'max' => 10]
     *                           Если нет необходимости менять значение поля, но параметры передать нужно, используйте значение *default*
     *                           Н-р, ['value' => '*default*', 'min' => 4, 'max' => 10]
     *
     *          ]
     *      );
     *
     * @return string
     * @throws waException
     */
    public function form($id, $options = array())
    {
        $multiform_model = new multiformFormModel();

        if ($form_settings = $multiform_model->getByField("url", $id)) {
            $id = $form_settings['id'];
        }
        $form = $multiform_model->getFullForm($id);

        // Если форма не общедоступна
        if (empty($form['type']) && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$form['id']}") <= 1) {
            return '';
        }

        // Проверяем существует ли поселение
        $route_url = wa()->getRouteUrl("multiform/frontend", array("domain" => wa('multiform')->getConfig()->getDomain()), true);

        if (!empty($form['status']) && $route_url) {
            return "<div class='multiform-body" . (!empty($form['popup']) ? ' is-popup' : '') . "' data-id='{$form['id']}' data-theme-id='".(!empty($form['theme_id']) ? $form['theme_id'] : 0)."'>" . multiformFormBuilder::buildForm($form, $options) . "</div>";
        }
    }

    /**
     * Get form records
     * @param int $form_id
     * @param array $options
     *      $options => array(
     *          'order' => 'desc' || 'asc',
     *          'page' => int,
     *          'records_per_page' => int,
     *          'contact_id' => int,
     *          'code_as_key' => bool,
     *          'show_files' => bool
     *      );
     * 
     * @return array
     */
    public function records($form_id, $options = array())
    {
        $multiform_model = new multiformFormModel();
        $form = $multiform_model->getFullForm($form_id);

        $mrm = new multiformRecordModel();
        // Проверка доступности формы
        $output_records = $return = array();
        if ($form && $form['status'] == '1' && !empty($form['record_output'])) {

            // Если форма не общедоступна
            if (!$form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$form['id']}") <= 1) {
                return array();
            }

            $records_per_page = !empty($options['records_per_page']) ? max(1, (int) $options['records_per_page']) : 50;
            $current_page = !empty($options['page']) ? max(1, (int) $options['page']) : (waRequest::request("page_".$form_id, 1));
            $count_filter = array("form_id" => $form_id, "status" => 1);
            if (!empty($options['contact_id'])) {
                $count_filter['create_contact_id'] = $options['contact_id'];
            }
            $records_count = $mrm->countByField($count_filter);
            $max_page = ceil($records_count / $records_per_page);
            if ($current_page > $max_page && $max_page > 0) {
                return array();
            }

            $return = array(
                "records_per_page" => $records_per_page,
                "page" => $current_page,
                "max_page" => $max_page,
            );

            $filter = array(
                'form_id' => $form_id,
                'order_by' => 'id',
                'order_direction' => !empty($options['order']) && in_array($options['order'], array('desc', 'asc')) ? $options['order'] : 'desc',
                'limit' => array("offset" => ($current_page - 1) * $records_per_page, "length" => $records_per_page),
                'status' => 1,
                'contact_id' => !empty($options['contact_id']) ? $options['contact_id'] : 0,
            );
            $records = $mrm->getRecords($filter);
            if ($records) {
                $private_field_access = multiformHelper::privateFieldAccess($form);
                // Создаем массив записей
                foreach ($records as $r) {
                    // Новый массив с записями
                    $output_records[$r['id']] = array(
                        "id" => $r['id'],
                        "request_id" => multiformHelper::formatRecordId($r['id'], $form),
                        "create_datetime" => $r['create_datetime'],
                        "create_contact_id" => $r['create_contact_id'],
                        "ip" => $r['ip'],
                        "comments" => !empty($r['comments']) ? $r['comments'] : array(),
                        "fields" => array()
                    );
                    if (!empty($r['fields'])) {
                        // Обрабатываем поля, делаем unique_id в качестве ключей полей 
                        foreach ($form['fields'] as $rf_id => $ff) {
                            if (!empty($r['fields'][$rf_id])) {
                                $field = $form['fields'][$rf_id];
                                $rf = $r['fields'][$rf_id];
                                // Если у контакта нет доступа к частным полям, то пропускаем поле
                                if ($field['private'] && !$private_field_access) {
                                    continue;
                                }
                                $key = !empty($options['code_as_key']) ? $field['code'] : $field['unique_id'];
                                $output_records[$r['id']]['fields'][$key] = array(
                                    "name" => $field['title'],
                                    "code" => $field['code'],
                                    "type" => $field['type']
                                );
                                if ($field['type'] == 'table_grid' || $field['type'] == 'checkbox') {
                                    $output_records[$r['id']]['fields'][$key]['value'] = array();
                                    $option_field = $field['type'] == 'checkbox' ? $field['option_fields'] : $field['option_fields']['statement'];
                                    if (!empty($rf) && !empty($option_field)) {
                                        foreach ($rf as $option_id => $option_val) {
                                            if (!empty($option_field[$option_id])) {
                                                $option = $option_field[$option_id];
                                                $output_records[$r['id']]['fields'][$key]['value'][] = array(
                                                    "name" => $option['value'],
                                                    "value" => $option_val
                                                );
                                            }
                                        }
                                    }
                                } elseif ($field['type'] == 'file' && !empty($rf)) {
                                    foreach ($rf as $file) {
                                        $output_records[$r['id']]['fields'][$key]['value'][] = array(
                                            "name" => $file['filename'],
                                            "value" => !empty($options['show_files']) ? self::getPublicPath($file['filename'], $r['id'], $field['id']) : wa()->getRouteUrl('multiform/frontend/downloadFile', array("hash" => $file['hash'], "domain" => wa('multiform')->getConfig()->getDomain()), true)
                                        );
                                    }
                                } else {
                                    if (is_array($rf)) {
                                        $output_records[$r['id']]['fields'][$key]['value'] = reset($rf);
                                    } else {
                                        $output_records[$r['id']]['fields'][$key]['value'] = $rf;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $return['records'] = $output_records;
        return $return;
    }

    /**
     * Get records html template
     * @param int $form_id
     * @param array $options
     *      $options => array(
     *          'order' => 'desc' || 'asc',
     *          'page' => int,
     *          'records_per_page' => int
     *      );
     * 
     * @return array
     */
    public function recordsTemplate($form_id, $options = array())
    {
        $multiform_model = new multiformFormModel();
        $form = $multiform_model->getFullForm($form_id);

        $mrm = new multiformRecordModel();
        $html = '';
        // Проверка доступности формы
        if ($form && $form['status'] == '1' && !empty($form['record_output']) && !empty($form['record_output_template'])) {
            $form_unique_id = $multiform_model->getFullForm($form_id, true);

            // Если форма не общедоступна
            if (!$form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$form['id']}") <= 1) {
                return '';
            }

            $records_per_page = !empty($options['records_per_page']) ? max(1, (int) $options['records_per_page']) : 50;
            $current_page = !empty($options['page']) ? max(1, (int) $options['page']) : 1;
            $count_filter = array("form_id" => $form_id, "status" => 1);
            if (!empty($options['contact_id'])) {
                $count_filter['create_contact_id'] = $options['contact_id'];
            }
            $records_count = $mrm->countByField($count_filter);
            $max_page = ceil($records_count / $records_per_page);
            if ($current_page > $max_page && $max_page > 0) {
                $current_page = $max_page;
            }
            $filter = array(
                'form_id' => $form_id,
                'order_by' => 'id',
                'order_direction' => !empty($options['order']) && in_array($options['order'], array('desc', 'asc')) ? $options['order'] : 'desc',
                'limit' => array("offset" => ($current_page - 1) * $records_per_page, "length" => $records_per_page),
                'status' => 1,
                'contact_id' => !empty($options['contact_id']) ? $options['contact_id'] : 0,
            );
            $records = $mrm->getRecords($filter);
            if ($records) {
                // Создаем массив записей
                foreach ($records as $r) {
                    $template_params = array('record' => $r, 'form' => $form_unique_id);
                    $html .= multiformTemplate::process($form['record_output_template'], $template_params);
                }
            }
        }
        return $html;
    }

    /**
     * Create public version of file.
     * 
     * @param string $file
     * @param int $record_id
     * @param int $field_id
     * @return string
     */
    private static function getPublicPath($file, $record_id, $field_id)
    {
        $public_path = wa()->getDataPath('files/' . (int) $record_id . '/' . (int) $field_id . '/' . $file, true, 'multiform');
        $private_path = wa()->getDataPath('files/' . (int) $record_id . '/' . (int) $field_id . '/' . $file, false, 'multiform');
        if (!file_exists($public_path) && file_exists($private_path)) {
            try {
                waFiles::copy($private_path, $public_path);
                return wa()->getDataUrl('files/' . (int) $record_id . '/' . (int) $field_id . '/' . $file, true, 'multiform', true);
            } catch (Exception $e) {
                return '';
            }
        } elseif (file_exists($public_path)) {
            return wa()->getDataUrl('files/' . (int) $record_id . '/' . (int) $field_id . '/' . $file, true, 'multiform', true);
        } else {
            return '';
        }
    }

}
