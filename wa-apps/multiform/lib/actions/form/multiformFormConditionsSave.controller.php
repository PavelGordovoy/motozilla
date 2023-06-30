<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormConditionsSaveController extends waJsonController
{

    public function execute()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        if (!$id) {
            $this->errors['messages']['form_id'] = _w("Hey, where is form ID?");
            return;
        }

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if (!multiformHelper::hasFullFormAccess($form)) {
            throw new waRightsException('Access denied');
        }

        $conditions = waRequest::post("condition", array());
        $tab = waRequest::post("tab", "field");

        $condition_model = new multiformConditionModel();
        $params_model = new multiformConditionParamsModel();
        $params = $data = array();
        try {
            $c = 0;
            $url_validator = new waUrlValidator();
            $email_validator = new waEmailValidator();
            foreach ($conditions as $sort_id => $condition) {
                // Если необходимо обработать поле для отправки уведомления на email
                if ($condition['action'] == 'email' && is_array($condition['target'])) {
                    if ($condition['target']['email_list']) {
                        // Если указано несколько email адресов
                        if (strpos($condition['target']['email_list'], ",")) {
                            $emails = explode(",", $condition['target']['email_list']);
                            foreach ($emails as $email) {
                                $email = trim($email);
                                if (strpos($email, '{') !== 0 && strpos($email, '}') !== strlen($email) && !$email_validator->isValid($email)) {
                                    $this->errors['messages']['email'] = _w("Email is not valid");
                                    $this->errors['fields'][] = '.s-form-tab .field[data-pos="' . $sort_id . '"] .s-email-tab input';
                                }
                            }
                        } else {
                            $email = trim($condition['target']['email_list']);
                            if (strpos($email, '{') !== 0 && strpos($email, '}') !== strlen($email) && !$email_validator->isValid($email)) {
                                $this->errors['messages']['email'] = _w("Email is not valid");
                                $this->errors['fields'][] = '.s-form-tab .field[data-pos="' . $sort_id . '"] .s-email-tab input';
                            }
                        }
                    }
                    $condition['target'] = serialize($condition['target']);
                }
                
                // Проверяем ссылку редиректа
                if ($condition['action'] == 'redirect') {
                    $condition['target'] = trim($condition['target']);
                    if ($condition['target'] && strpos($condition['target'], '{') !== 0 && strpos($condition['target'], '}') !== strlen($condition['target']) && !$url_validator->isValid($condition['target'])) {
                        $this->errors['messages']['url'] = _w("Redirect URL is not valid");
                        $this->errors['fields'][] = '.s-form-tab .field[data-pos="' . $sort_id . '"] .s-redirect-tab input';
                    }
                }
                
                // Основные данные условия
                $data[$sort_id] = array(
                    'form_id' => $id,
                    'action' => $condition['action'],
                    'target' => $tab == 'field' ? (int) $condition['target'] : $condition['target'],
                    'andor' => (int) $condition['andor'],
                    'sort' => $c,
                    'tab' => $tab,
                );
                $c++;
            }
            if (!$this->errors) {
                // Очищаем условия, чтобы занести их занаво
                $condition_model->delete($id, $tab);

                foreach ($data as $s_id => $d) {
                    $condition_id = $condition_model->insert($d);
                    if ($condition_id) {
                        // Обрабатываем параметры условия
                        foreach ($conditions[$s_id]['params'] as $param) {
                            $params[] = array(
                                'condition_id' => (int) $condition_id,
                                'source' => (int) $param['source'],
                                'source_ext' => !empty($param['source_ext']) ? (int) $param['source_ext'] : 0,
                                'operator' => $param['operator'],
                                'value' => (mb_strlen($param['value'], "UTF-8") > 255 ? mb_substr($param['value'], 0, 255, "UTF-8") : $param['value']),
                            );
                        }
                    }
                }
                if ($params) {
                    $params_model->multipleInsert($params);
                }
            }
        } catch (Exception $e) {
            multiformHelper::log($e->getMessage());
            $this->errors['messages'][] = _w("Something wrong. Check log files.");
        }
    }

}
