<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformProcessingDataAction extends multiformProcessingAction
{

    public function __construct()
    {
        parent::__construct();

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($this->form_id);
        // Если форма не общедоступна
        if (!$form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$this->form_id}") <= 1) {
            throw new waRightsException('Access denied');
        }
    }

    public function execute()
    {
        if (waRequest::method() == 'post') {
            try {
                // Поля формы
                $fields = waRequest::post('fields', '');
                $cloned_section_fields = waRequest::post('cloned_section', array());

                // Скрытые пользовательские поля формы
                $hidden_fields = waRequest::post('params', '');
                // Данные для сохранения в БД
                $values = array();
                // Пропустить шаги проверок, если форма обрабатывается из бекэнда.
                $backend = wa()->getEnv() == 'backend' ? 1 : 0;
                // ID записи. Передается только при редактировании
                $record_id = waRequest::post('record_id', 0, waRequest::TYPE_INT);

                // Обрабатываем поля
                $form_model = new multiformFormModel();
                if ($this->form_id) {
                    // Получаем всю информацию о форме
                    $form = $form_model->getFullForm($this->form_id);
                    if (!empty($form['fields'])) {
                        $form_fields = $form['fields'];
                        // Вывод формы по расписанию
                        $today = time();
                        if (!$backend && ((!empty($form['start']) && $today < strtotime($form['start'])) || (!empty($form['end']) && $today > strtotime($form['end'])))) {
                            $this->addError(_wd('multiform', "Form is not active"));
                            $this->generateResponse();
                        }

                        // Если имеются какие-либо ограничения формы
                        $limit_error = multiformHelper::formLimitationErrors($form);
                        if (!$backend && $limit_error) {
                            $this->addError($limit_error);
                            $this->generateResponse();
                        }

                        // Проверка капчи
                        if (!$backend && !empty($form['captcha']) && ($form['captcha'] == 'basic' || $form['captcha'] == 'advanced')) {
                            if ($form['captcha'] == 'advanced' && !empty($form['recaptcha']) && !empty($form['recaptcha_sitekey']) && !empty($form['recaptcha_secretkey'])) {
                                $secret = $form['recaptcha_secretkey'];
                            } else {
                                $secret = '';
                            }
                            $captcha = waRequest::post($secret ? 'g-recaptcha-response' : 'captcha-' . $this->form_uid);
                            $captcha_error = '';
                            if (!wa()->getCaptcha()->getFactory($secret ? 'recaptcha' : 'captcha')->isValid($captcha, $captcha_error, $secret)) {
                                $this->addError($captcha_error);
                                $this->generateResponse();
                            }
                        }

                        // Обрабатываем переданные поля
                        $process_post_fields = $this->processPostFields($fields, $form['fields']);
                        $post_fields = $process_post_fields['field_ids'];

                        // Обрабатываем дополнительные поля секций
                        $this->processClonedSectionPostFields($cloned_section_fields, $form['fields']);

                        // Осуществляем проверку условий
                        if (!$backend && !empty($form['conditions']['field'])) {
                            $form_fields = $form['fields'] = multiformConditions::check_conditions($form['conditions']['field'], $form_fields, $post_fields);
                        }
                        if (!empty($form['conditions']['form'])) {
                            $form = multiformConditions::check_conditions($form['conditions']['form'], $form_fields, $post_fields, $form);
                        }

                        // Если не используется javascript, подключаем класс для работы с файлами
                        if (!waRequest::isXMLHttpRequest()) {
                            $file_processing = new multiformProcessingFileAction();
                        }

                        // Перебираем поля формы
                        $this->validateFields($form_fields, $process_post_fields, $form, $values, ref(isset($file_processing) ? $file_processing : null), $cloned_section_fields);
                    }
                }

                // Обработка скрытых полей
                if ($hidden_fields && $this->form_id) {
                    foreach ($hidden_fields as $v) {
                        foreach ($v as $name => $value) {
                            if ($value) {
                                $values[] = array("field_id" => 0, "ext" => $name, "value" => $value, "cloned_index" => 0);
                            }
                        }
                    }
                }
                // Если имеются ошибки, то прерываем обработку
                if ($this->hasErrors()) {
                    $this->generateResponse();
                }

                $data = array("form_id" => $this->form_id, "create_datetime" => date("Y-m-d H:i:s"), "create_contact_id" => wa()->getUser()->getId(), 'ip' => waRequest::getIp());

                // Сохраняем основные данные в БД, получаем ID записи
                if ($this->record_id = $this->save($data, $record_id)) {

                    // Если не используется javascript и имеются файлы к сохранению
                    if (isset($file_processing) && $file_processing->count()) {
                        $mrm = new multiformRecordModel();
                        $file_processing->save($this->record_id);
                        if ($this->hasErrors()) {
                            // Если хоть один файл не удалось сохранить, прерываем весь процесс сохранения
                            if (!$backend) {
                                $mrm->delete($this->record_id);
                            }
                            $this->generateResponse();
                        }
                    }

                    // Изменяем ключи полей формы, чтобы использовать их на завершающем этапе обработки формы
                    if (!empty($form)) {
                        $this->form = $form;
                        $temp_form = $form_model->getFullForm($this->form_id, 1);
                        if (!$backend && !empty($form_fields)) {
                            $temp_form['fields'] = array_combine(array_column($form_fields, 'unique_id'), $form_fields);
                        }
                        $this->form['fields'] = $temp_form['fields'];
                    }

                    if ($values) {
                        // Добавляем ID записи ко всем значениям
                        foreach ($values as &$value) {
                            $value['record_id'] = $this->record_id;
                        }
                        // Сохраняем все значения
                        $this->saveValues($values, $this->record_id);
                    }

                    $this->result['data']['id'] = $this->record_id;

                    // Если у формы есть поля файлов
                    if (!empty($form['has_files'])) {
                        $this->result['data']['has_files'] = 1;
                        // Если форма с условиями, сохраняем данные полей, чтобы использовать их при последующей обработке файлов
                        if (!empty($form['active_conditions'])) {
                            $cache_cond = new waSerializeCache('multiform_form_conditions_' . $this->record_id);
                            $cache_cond->set($form['active_conditions']);
                        }
                        // Сохраняем данные формы. Нас интересует параметр multiform_hide при обработке файлов
                        if (!empty($this->form)) {
                            $cache_form = new waSerializeCache('multiform_form_' . $this->record_id);
                            $cache_form->set($this->form);
                        }
                        $this->generateResponse();
                    } else {
                        if (!empty($form['active_conditions'])) {
                            $cache_cond = new waRuntimeCache('multiform_form_conditions_' . $this->record_id);
                            $cache_cond->set($form['active_conditions']);
                        }
                    }
                    return;
                }
            } catch (Exception $e) {
                $this->result['errors']['messages'][] = _wd('multiform', "Sorry, we have some technical problems");
                multiformHelper::log($e->getMessage());
                $this->generateResponse();
            }
        }
    }

    private function validateFields($form_fields, $process_post_fields, &$form, &$values, &$file_processing, $cloned_section_fields = [], $index = 0)
    {
        $post_fields = $process_post_fields['field_ids'];

        $backend = wa()->getEnv() == 'backend' ? 1 : 0;
        // Доступ к частным полям
        $private_field_access = multiformHelper::privateFieldAccess($form);

        $active_section = 0;
        $opened_sections = [];
        foreach ($form_fields as $f) {

            // Если секция закончилась
            if (empty($f['additional']['section'])) {
                $active_section = 0;
            }

            // Закрываем секцию в секции и проверяем наличие дублированных полей
            if ($opened_sections && !empty($f['additional']['section']) && $active_section !== (int) $f['additional']['section']) {
                $section = array_pop($opened_sections);
                $this->processSectionClonedFields(array($section), $cloned_section_fields, $form_fields, $form, $values, $file_processing, $index);
            }
            // Если поле не принадлежит ни одной секции, значит можно закрывать все секции от начала до конца
            if ($opened_sections && empty($f['additional']['section'])) {
                $this->processSectionClonedFields($opened_sections, $cloned_section_fields, $form_fields, $form, $values, $file_processing, $index);
            }
            if ($f['type'] == 'section') {
                $opened_sections[] = $f;
            }

            if (!empty($f['additional']['section']) || $f['type'] == 'section') {
                $active_section = $f['type'] == 'section' ? (int) $f['id'] : (int) $f['additional']['section'];
            }

            if (!$f['status'] || ($f['private'] && !$private_field_access) || (!$backend && $f['hidden']) || ($backend && isset($f['properties']['save_data']) && !$f['properties']['save_data'])) {
                continue;
            }

            if (!$instance = multiformFormBuilder::getFieldInstance($f['type'])) {
                $this->addError(array('type_' . $f['type'] => sprintf(_wd('multiform', 'Field type %s is undefined'), $f['type'])), $f['id'], $index);
                continue;
            }
            // Проверка обязательных полей
            $name = "validate_required";
            // Для бекенда есть возможность отмены проверки обязательного поля
            if ($backend) {
                $skip_required = waRequest::post('skip_required', array());
            }
            if ($f['required'] && empty($f['multiform_hide']) && empty($skip_required[$f['id']]) && ((method_exists($instance, $name) && !$instance->$name($post_fields, $f)) || (!method_exists($instance, $name) && empty($post_fields[$f['id']])))) {
                $this->addError(array('required' => _wd('multiform', "Fill in the required fields")), $f['id'], $index);
            }
            // Проверяем, существует ли переданное поле
            if (isset($post_fields[$f['id']]) || $f['type'] == 'checkbox') {
                $field = isset($post_fields[$f['id']]) ? $post_fields[$f['id']] : array();

                // Если поле только для чтения, устанавливаем значение по умолчанию, игнорируя пришедшие данные
                if ($f['disabled'] && isset($f['properties']['default']) && !is_array($field) && !$backend) {
                    $field = $f['properties']['default'];
                }

                // Проверка регулярных выражений
                $name_validate_mask = "validate_mask";
                if (!empty($field) && empty($f['multiform_hide'])) {
                    if (method_exists($instance, $name_validate_mask)) {
                        $method_result = $instance->$name_validate_mask($f, $field);
                        $field = !empty($method_result['field']) ? $method_result['field'] : $field;
                        if (!empty($method_result['error_message'])) {
                            $this->addError($method_result['error_message'], $f['id'], $index);
                        }
                    } elseif (!empty($f['validation']['mask'])) {
                        $regexp = !empty($f['validation']['regexp']) ? $f['validation']['regexp'] : "";
                        $error = isset($f['validation']['regexp_error']) ? $f['validation']['regexp_error'] : "";
                        if (!preg_match($regexp, $field)) {
                            $this->addError($error ? $error : sprintf(_wd('multiform', "Field %s has incorrect value"), multiformHelper::secureString($f['title'])), $f['id'], $index);
                        }
                    }
                }

                // Выполнение остальных проверок полей
                $name_validate_field = "validate_field";
                if (empty($f['multiform_hide'])) {
                    if (method_exists($instance, $name_validate_field)) {
                        $method_result2 = $instance->$name_validate_field($f, $field, $form_fields, $process_post_fields);
                        $field = !empty($method_result2['field']) ? $method_result2['field'] : $field;
                        if (!empty($method_result2['errors']['messages'])) {
                            $this->result['errors']['messages'] = array_merge($this->result['errors']['messages'], $method_result2['errors']['messages']);
                            $this->result['errors']['fields'][$f['id']] = $f['id'];
                        }
                    }
                }

                // Если передан файл и не используется javascript
                if ($f['type'] == 'file' && empty($f['multiform_hide'])) {
                    if ($file_processing->isValid($field, $f)) {
                        // Сохраняем файлы для последующей обработки
                        $file_processing->add($f['id'], $field, $index);
                    }
                    continue;
                }

                // Если выбрано не сохранять значения
                if (isset($f['properties']['save_data']) && !$f['properties']['save_data']) {
                    continue;
                }

                // Составляем массив для сохранения значений полей
                if (is_array($field)) {
                    if (!empty($field['value']) && is_array($field['value'])) {
                        foreach ($field['value'] as $key => $value) {
                            $values[] = array("field_id" => $f['id'], "ext" => $key, "value" => $value, "cloned_index" => $index);
                        }
                    } elseif (!empty($field['value'])) {
                        $values[] = array("field_id" => $f['id'], "ext" => '', "value" => $field['value'], "cloned_index" => $index);
                    } else {
                        foreach ($field as $key2 => $value2) {
                            $values[] = array("field_id" => $f['id'], "ext" => $key2, "value" => $value2, "cloned_index" => $index);
                        }
                    }
                } else {
                    $values[] = array("field_id" => $f['id'], "ext" => '', "value" => $field, "cloned_index" => $index);
                }
            }
            // Если используется javascript и имеются активные поля файлов, указываем, что необходимо выполнить загрузку файлов прежде,
            // чем закончить обработку
            if ($f['type'] == 'file' && empty($f['multiform_hide']) && waRequest::isXMLHttpRequest()) {
                $form['has_files'] = 1;
            }
        }
        if ($opened_sections) {
            $this->processSectionClonedFields($opened_sections, $cloned_section_fields, $form_fields, $form, $values, $file_processing, $index);
        }
    }

    private function processSectionClonedFields($sections, $cloned_section_fields, $form_fields, &$form, &$values, &$file_processing, $index = 0)
    {
        foreach ($sections as $section) {
            // Проверяем, имеются ли дублированные поля у секции
            if (!empty($section['properties']['multiply_fields']) && !empty($cloned_section_fields[$section['id']])) {
                $section_fields = $cloned_section_fields[$section['id']];
                // Если имеется ограничение по максимально допустимому кол-ву дублирований
                if (!empty($section['properties']['multiply_max']) && $section['properties']['multiply_max'] > 0) {
                    $section_fields = array_slice($section_fields, 0, intval($section['properties']['multiply_max']), true);
                }
                $index = ($index ? $index . '._.' : '') . $section['id'];
                foreach ($section_fields as $index2 => $fields) {
                    $inside_index = $index . '.' . $index2;
                    $this->recursiveFieldsValidation($form_fields, $fields, $form, $values, $file_processing, $section['id'], $inside_index);
                }
            }
        }
    }

    private function recursiveFieldsValidation($form_fields, $process_post_fields, &$form, &$values, &$file_processing, $section_id, $index)
    {
        $cloned_section = array();
        if (isset($process_post_fields['cloned_section'])) {
            $cloned_section = $process_post_fields['cloned_section'];
            unset($process_post_fields['cloned_section']);
        }

        $this->validateFields(array_intersect_key($form_fields, $form_fields[$section_id]['all_childs']), $process_post_fields, $form, $values, $file_processing, $cloned_section, $index);
    }

    /**
     * Preprocess post fields. Return array with field_id keys
     *
     * @param array $post_fields
     * @param array $form_fields
     * @return array
     */
    private function processPostFields($post_fields, $form_fields)
    {
        $temp = $result = $unique = array();
        if ($post_fields) {
            // Формируем массив из переданных значений, ключами которого являются unique_id
            foreach ($post_fields as $field_id => $field) {
                if (strpos($field_id, "_") !== false) {
                    // Получаем UNIQUE_ID полей
                    $chunks = explode("_", $field_id);
                    $f_id = (int) $chunks[1];
                    if (isset($form_fields[$f_id])) {
                        $temp[$form_fields[$f_id]['unique_id']] = $field;
                    }
                }
            }
        }
        // Определяем ID полей и заменяем ими unique_id
        foreach ($form_fields as $f) {
            // Если передан файл и не используется javascript
            if ($f['type'] == 'file' && !waRequest::isXMLHttpRequest()) {
                // Здесь нет проверки клонированных полей, потому что без JS их просто не будет.
                $field_files = waRequest::file('files_' . $f['id']);
                if ($field_files->uploaded()) {
                    $result[$f['id']] = $field_files;
                }
                continue;
            }
            $f['unique_id'] = (int) $f['unique_id'];
            // Проверяем, существует ли переданное поле
            if (isset($temp[$f['unique_id']])) {
                // Производим дополнительную обработку полей
                $method_name = $f['type'] . 'FieldProcess';
                if (method_exists($this, $method_name)) {
                    $result[$f['id']] = $this->$method_name($temp[$f['unique_id']], $f);
                } else {
                    $result[$f['id']] = $temp[$f['unique_id']];
                }
                // Сохраняем значение с ключом unique_id на случай, если будут использоваться формулы
                $unique[$f['unique_id']] = $result[$f['id']];
            }
        }

        return array("field_ids" => $result, "unique_ids" => $unique);
    }

    /**
     * Preprocess cloned section fields. Return array with field_id keys
     *
     * @param array $post_fields
     * @param array $form_fields
     */
    private function processClonedSectionPostFields(&$post_fields, $form_fields)
    {
        foreach ($post_fields as $field_id => &$sections) {
            foreach ($sections as &$section_fields) {
                $section_fields_inside = [];
                if (isset($section_fields['cloned_section'])) {
                    $section_fields_inside = $section_fields['cloned_section'];
                    unset($section_fields['cloned_section']);
                }
                $section_fields = $this->processPostFields($section_fields, $form_fields);
                if ($section_fields_inside) {
                    $section_fields['cloned_section'] = $this->processClonedSectionPostFields($section_fields_inside, $form_fields);
                    $section_fields['cloned_section'] = $section_fields_inside;
                }
            }
        }
    }

    /**
     * Process date field
     *
     * @param array $value
     * @param array $form_field
     * @return array
     */
    private function dateFieldProcess($value, $form_field)
    {
        $date = $formatted = "";

        // Если поле только для чтения, устанавливаем значение по умолчанию, игнорируя пришедшие данные
        if (!empty($form_field['disabled']) && isset($form_field['properties']['default_value']) && wa()->getEnv() == 'frontend') {
            $default_value = (strpos($form_field['properties']['default_value'], "-") !== false ? explode("-", $form_field['properties']['default_value']) : array());
        }

        // Если для указания даты используются несколько полей
        if ((!empty($form_field['display']['date_type']) && $form_field['display']['date_type'] == 'fields') || (empty($form_field['display']['date_type']) && is_array($value))) {
            $separator = !empty($form_field['display']['date_separator']) ? strip_tags($form_field['display']['date_separator']) : '/';
            $separator = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $separator);

            if (isset($default_value)) {
                $separator = '-';
                $value['year'] = (!empty($default_value[0]) ? $default_value[0] : 0);
                $value['month'] = (!empty($default_value[1]) ? $default_value[1] : 0);
                $value['day'] = (!empty($default_value[2]) ? $default_value[2] : 0);
            }

            if (isset($value['year'])) {
                $year = (int) $value['year'];
                // Если имеются ограничения для года, то не даем уйти за границы
                if (!empty($form_field['year_start']) && $year < $form_field['year_start']) {
                    $year = $form_field['year_start'];
                }
                if (!empty($form_field['year_end']) && $year > $form_field['year_end']) {
                    $year = $form_field['year_end'];
                }
                $formatted .= $year . '-';
                $date .= str_pad($year, 4, 0, STR_PAD_LEFT) . $separator;
            } else {
                $formatted .= '0-';
            }
            if (isset($value['month'])) {
                // Если передано более двух символов, обрезаем значение
                if (strlen($value['month'] > 2)) {
                    $value['month'] = substr(trim($value['month']), 0, 2);
                }
                $month = (int) $value['month'];
                // Если значение превышает 12 или меньше 1, устанавливаем в качестве месяца значение 1
                if ($month > 12 || $month < 1) {
                    $month = 1;
                    $value['month'] = '01';
                }
                $date .= str_pad($value['month'], 2, 0, STR_PAD_LEFT) . $separator;
                $formatted .= $month . '-';
            } else {
                $formatted .= '0-';
            }
            if (isset($value['day'])) {
                // Если передано более двух символов, обрезаем значение
                if (strlen($value['day'] > 2)) {
                    $value['day'] = substr(trim($value['day']), 0, 2);
                }
                $day = (int) $value['day'];
                // Определяем максимально допустимое значение для дней
                if (!empty($year) && !empty($month)) {
                    // Вычисляем количество дней
                    $days = multiformHelper::days_in_month($month, $year);
                } elseif (!empty($month)) {
                    $days = $month == 2 ? 28 : 31;
                } else {
                    $days = 31;
                    if ($day > $days) {
                        $day = $days;
                        $value['day'] = $days;
                    }
                    if ($day < 1) {
                        $day = 1;
                    }
                    $value['day'] = '01';
                }
                $date .= str_pad($value['day'], 2, 0, STR_PAD_LEFT);
                $formatted .= $day;
            } else {
                $formatted .= '0';
            }
        } // Если для указания даты используется одно поле
        else {
            if (isset($default_value)) {
                $value['value'] = $value['formatted'] = $form_field['properties']['default'];
            }
            $date = $value['value'];
            $formatted = isset($value['formatted']) ? $value['formatted'] : $date;
        }
        return array("value" => $date, "formatted" => $formatted);
    }

    /**
     * Process time field
     *
     * @param string $value
     * @param array $form_field
     * @return array
     */
    private function timeFieldProcess($value, $form_field)
    {
        $time = $formatted = "";

        // Если поле только для чтения, устанавливаем значение по умолчанию, игнорируя пришедшие данные
        if (!empty($form_field['disabled']) && isset($form_field['properties']['default']) && wa()->getEnv() == 'frontend') {
            $default_value = (strpos($form_field['properties']['default'], ":") !== false ? explode(":", $form_field['properties']['default']) : array());
        }

        // Если для указания даты используются несколько полей
        if ((!empty($form_field['display']['time_type']) && $form_field['display']['time_type'] == 'fields') || (empty($form_field['display']['time_type']) && is_array($value))) {
            $separator = !empty($form_field['display']['separator']) ? strip_tags($form_field['display']['separator']) : ':';
            $separator = preg_replace("/&#?[a-z0-9]{2,8};/i", "", $separator);

            if (isset($default_value)) {
                $separator = ':';
                $value['hour'] = (!empty($default_value[0]) ? $default_value[0] : 0);
                $value['hour'] = $value['hour'] > 24 ? 24 : $value['hour'];
                $value['minute'] = (!empty($default_value[1]) ? $default_value[1] : 0);
                $value['minute'] = $value['minute'] > 60 ? 60 : $value['minute'];
                $value['second'] = (!empty($default_value[2]) ? $default_value[2] : 0);
                $value['second'] = $value['second'] > 60 ? 60 : $value['second'];
            }

            if (isset($value['hour'])) {
                // Если передано более двух символов, обрезаем значение
                if (strlen($value['hour'] > 2)) {
                    $value['hour'] = substr(trim($value['hour']), 0, 2);
                }
                $hour = (int) $value['hour'];
                // Если значение превышает 24 или меньше 0, устанавливаем в качестве часов значение 0
                if ($hour > 24 || $hour < 0) {
                    $hour = 0;
                    $value['hour'] = '00';
                }
                if (isset($form_field['display']['time_format']) && $form_field['display']['time_format'] == 12 && $hour > 12) {
                    $value['time_format'] = 'pm';
                    $value['hour'] = $hour -= 12;
                }
                $time .= str_pad($value['hour'], 2, 0, STR_PAD_LEFT) . $separator;
                if (isset($value['time_format'])) {
                    $value['time_format'] = strtolower($value['time_format']);
                    if ($hour < 12 && $value['time_format'] == 'pm') {
                        $hour = $hour + 12;
                    } elseif ($hour == '12' && $value['time_format'] == 'am') {
                        $hour = '00';
                    }
                }
                $formatted .= (trim($value['hour']) == '' && $form_field['required'] ? '' : $hour) . ":";
            } else {
                $formatted .= ($form_field['required'] ? '' : 0) . ":";
            }
            if (isset($value['minute'])) {
                // Если передано более двух символов, обрезаем значение
                if (strlen($value['minute'] > 2)) {
                    $value['minute'] = substr(trim($value['minute']), 0, 2);
                }
                $minute = (int) $value['minute'];
                // Если значение превышает 60 или меньше 0, устанавливаем в качестве минут значение 0
                if ($minute > 60 || $minute < 0) {
                    $minute = 0;
                    $value['minute'] = '00';
                }
                $time .= str_pad($value['minute'], 2, 0, STR_PAD_LEFT) . $separator;
                $formatted .= (trim($value['minute']) == '' && $form_field['required'] ? '' : $minute) . ":";
            } else {
                $formatted .= ($form_field['required'] ? '' : 0) . ":";
            }
            if (isset($value['second'])) {
                // Если передано более двух символов, обрезаем значение
                if (strlen($value['second'] > 2)) {
                    $value['second'] = substr(trim($value['second']), 0, 2);
                }
                $second = (int) $value['second'];
                // Если значение превышает 60 или меньше 0, устанавливаем в качестве секунд значение 0
                if ($second > 60 || $second < 0) {
                    $second = 0;
                    $value['second'] = '00';
                }
                $time .= str_pad($value['second'], 2, 0, STR_PAD_LEFT);
                $formatted .= (trim($value['second']) == '' && $form_field['required'] ? '' : $second);
            } else {
                $formatted .= ($form_field['required'] ? '' : 0);
            }
            if (isset($value['time_format'])) {
                $time .= " " . strtoupper($value['time_format']);
            }
            $time = rtrim($time, $separator);
        } // Если для указания даты используется одно поле
        else {
            if (isset($default_value)) {
                $value = $form_field['properties']['default'];
            }
            $time = $formatted = $value;
        }
        return array("value" => $time, "formatted" => $formatted);
    }

    /**
     * Process radio field
     *
     * @param array $value
     * @param array $form_field
     * @return array
     */
    private function radioFieldProcess($value, $form_field)
    {
        $return = $formatted = "";

        $other_key = 0;

        if (isset($value['other']) && !empty($form_field['options']['allow_other']) && !empty($form_field['option_fields'][(int) $value['value']]) && $form_field['option_fields'][(int) $value['value']]['sort'] == 999999) {
            $other_key = (int) $value['value'];
        }

        // Если поле только для чтения, устанавливаем значение по умолчанию, игнорируя пришедшие данные
        if (!empty($form_field['disabled']) && wa()->getEnv() == 'frontend') {
            foreach ($form_field['option_fields'] as $key => $v) {
                if ($v['checked']) {
                    if ($v['sort'] == 999999) {
                        $return = $value['other'] ? trim($value['other']) : $value['other'];
                        $other_key = $key;
                    } else {
                        $return = !empty($form_field['options']['customize_keys']) ? $v['key'] : $v['value'];
                    }
                    $formatted = $key;
                    break;
                }
            }
        } else {
            $key = (int) $value['value'];
            if (isset($form_field['option_fields'][$key])) {
                if ($form_field['option_fields'][$key]['sort'] == 999999) {
                    $return = $value['other'] ? trim($value['other']) : $value['other'];
                } else {
                    $return = !empty($form_field['options']['customize_keys']) ? $form_field['option_fields'][$key]['key'] : $form_field['option_fields'][$key]['value'];
                }
                $formatted = $key;
            }
        }

        return array("value" => $return, "formatted" => $formatted, "other" => $other_key);
    }

    /**
     * Process select field
     *
     * @param string $value
     * @param array $form_field
     * @return array
     */
    private function selectFieldProcess($value, $form_field)
    {
        $return = $formatted = "";

        // Если поле только для чтения, устанавливаем значение по умолчанию, игнорируя пришедшие данные
        if (!empty($form_field['disabled']) && wa()->getEnv() == 'frontend') {
            foreach ($form_field['option_fields'] as $key => $v) {
                if ($v['checked']) {
                    $return = !empty($form_field['options']['customize_keys']) ? $v['key'] : $v['value'];
                    $formatted = $key;
                    break;
                }
            }
        } else {
            $value = (int) $value;
            if (isset($form_field['option_fields'][$value])) {
                $return = !empty($form_field['options']['customize_keys']) ? $form_field['option_fields'][$value]['key'] : $form_field['option_fields'][$value]['value'];
                $formatted = $value;
            }
        }
        return array("value" => $return, "formatted" => $formatted);
    }

    /**
     * Process checkbox field
     *
     * @param array $value
     * @param array $form_field
     * @return array
     */
    private function checkboxFieldProcess($value, $form_field)
    {
        $return = array();

        // Если поле только для чтения, устанавливаем значение по умолчанию, игнорируя пришедшие данные
        if (!empty($form_field['disabled']) && wa()->getEnv() == 'frontend') {
            if (!empty($form_field['option_fields'])) {
                foreach ($form_field['option_fields'] as $key => $v) {
                    if ($v['checked']) {
                        $return[$key] = !empty($form_field['options']['customize_keys']) ? $v['key'] : $v['value'];
                    }
                }
            }
        } else {
            foreach ($value as $key => $v) {
                if (!isset($form_field['option_fields'][$key])) {
                    continue;
                }
                $return[$key] = !empty($form_field['options']['customize_keys']) ? $form_field['option_fields'][$key]['key'] : $form_field['option_fields'][$key]['value'];
            }
        }

        return $return;
    }

    /**
     * Process table_grid field
     *
     * @param array $value
     * @param array $form_field
     * @return array
     */
    private function table_gridFieldProcess($value, $form_field)
    {
        $return = $formatted = array();
        if ($value) {
            // Проверяем, существуют ли строки и столбцы
            foreach ($value as $statement_id => $column) {
                if (!isset($form_field['option_fields']['statement'][$statement_id])) {
                    continue;
                }
                foreach ($form_field['option_fields']['column'] as $key => $c) {
                    if (!empty($form_field['options']['customize_column_keys'])) {
                        if ($column == $c['key']) {
                            $return[$statement_id] = $column;
                            $formatted[$statement_id] = $key;
                            break;
                        }
                    } else {
                        if ($column == $c['value']) {
                            $return[$statement_id] = $column;
                            $formatted[$statement_id] = $key;
                            break;
                        }
                    }
                }
            }
        }
        return array("value" => $return, "formatted" => $formatted);
    }

    /**
     * Process rating field
     *
     * @param int $value
     * @param array $form_field
     * @return int
     */
    private function ratingFieldProcess($value, $form_field)
    {
        // Если значение рейтинга больше 5
        if ($value && $value > 5) {
            $value = 5;
        } elseif (!$value) {
            $value = 0;
        }
        return $value;
    }

    /**
     * Insert or update data
     *
     * @param array $data
     * @param int $record_id
     * @return int
     *
     */
    private function save($data, $record_id)
    {
        $mrm = new multiformRecordModel();
        if (wa()->getEnv() == 'backend') {
            $data = array("update_datetime" => date("Y-m-d H:i:s"), "update_contact_id" => wa()->getUser()->getId());
            $mrm->updateById($record_id, $data);
            return $record_id;
        } else {
            return $mrm->insert($data);
        }
    }

    private function saveValues($values, $record_id)
    {
        $mrvm = new multiformRecordValuesModel();
        if (wa()->getEnv() == 'backend') {

            // Индексируем записи
            $records_with_keys = [];
            $records = $mrvm->getByField('record_id', $record_id, 'id');
            if ($records) {
                foreach ($records as $r) {
                    $key = $r['field_id'] . '-' . $r['ext'] . '-' . $r['cloned_index'] . $r['record_id'];
                    $records_with_keys[$key] = $r['id'];
                }
            }

            if ($files = waRequest::post('has_files', array())) {
                foreach ($files as $value) {
                    list($field_id, $index) = explode('|', $value);
                    $values[] = array("field_id" => $field_id, "ext" => 'file', "value" => "", "cloned_index" => $index, "record_id" => $record_id);
                }
            }

            $updated = [];
            foreach ($values as $k => $v) {
                $key = $v['field_id'] . '-' . $v['ext'] . '-' . $v['cloned_index'] . $v['record_id'];
                if (isset($records_with_keys[$key])) {
                    $mrvm->updateById($records_with_keys[$key], $v);
                    $updated[$records_with_keys[$key]] = $records_with_keys[$key];
                    unset($values[$k]);
                }
            }

            if ($delete = array_diff($records_with_keys, $updated)) {
                $mrvm->deleteById($delete);
            }
        }
        if ($values) {
            $mrvm->multipleInsert(array_values($values));
        }
    }

}
