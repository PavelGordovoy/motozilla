<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformProcessingAction
{

    protected $form_id;
    protected $record_id;
    protected $result;
    protected $record;
    protected $form;

    public function __construct()
    {
        $this->result = array("errors" => array("messages" => array(), "fields" => []), "status" => 'ok', "data" => array());
        // ID формы
        $this->form_id = waRequest::post("form_id", 0, waRequest::TYPE_INT);
        // UID формы
        $this->form_uid = waRequest::post("form_uid");
    }

    public function postExecute()
    {
        // Устанавливаем ключ, чтобы JS скрипт понял, что загрузка файлов закончена
        $this->result['postExecute'] = 1;

        // Пропустить шаги проверок, если форма обрабатывается из бекэнда.
        $backend = wa()->getEnv() == 'backend' ? 1 : 0;

        if (!$this->record_id) {
            $this->addError(_wd('multiform', "Cannot save files"));
            multiformHelper::log("Lost record_id");
            $this->generateResponse();
        }

        $mrm = new multiformRecordModel();

        $record = $this->getRecord();
        $form = $this->getForm();

        $this->form_id = $form['id'];

        // Условия формы
        $cache_cond = empty($this->result['data']['has_files']) ? new waRuntimeCache('multiform_form_conditions_' . $this->record_id) : new waSerializeCache('multiform_form_conditions_' . $this->record_id);
        if ($cache_cond->isCached()) {
            $active_conditions = $cache_cond->get();
        }

        // Параметры для уведомлений
        $template_params = array('record' => $record, 'form' => $form);

        $params = array();

        // Email отправителя
        if (!empty($form['email_sender'])) {
            $params['confirmation_sender'] = $form['email_sender'];
        }

        // Имя отправителя
        if (!empty($form['email_sender_name_record'])) {
            $params['confirmation_sender_name'] = multiformTemplate::process($form['email_sender_name_record'], $template_params);
        }

        // Тема уведомления
        $params['confirmation_subject'] = !empty($form['email_subject_record']) ? multiformTemplate::process($form['email_subject_record'], $template_params) : multiformTemplate::process(_wd('multiform', "You've got new notification: {Request:ID}"), $template_params);
        // Действие после успешной обработки формы
        if (!$backend) {
            if (!empty($active_conditions['redirect'])) {
                $redirect = multiformTemplate::process($active_conditions['redirect'], $template_params);
                // Проверяем корректность ссылки для перенаправления
                $validator_url = '#\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))#iS';
                if (preg_match($validator_url, $redirect)) {
                    $this->result['data']['redirect'] = $redirect;
                }
            } elseif (isset($active_conditions['message'])) {
                $this->result['data']['message'] = multiformTemplate::process($active_conditions['message'], $template_params);
            } elseif (empty($form['confirmation']) || (!empty($form['confirmation']) && $form['confirmation'] == 'message')) {
                // Текст после успешной отправки
                $this->result['data']['message'] = isset($form['callback_text']) ? multiformTemplate::process($form['callback_text'], $template_params) : multiformTemplate::process(_wd('multiform', "Your message successfully sent! Your request number is {Request:ID}"), $template_params);
            } elseif ($form['confirmation'] == 'redirect' && !empty($form['confirmation_redirect'])) {
                $redirect = multiformTemplate::process($form['confirmation_redirect'], $template_params);
                // Проверяем корректность ссылки для перенаправления
                $validator_url = '#\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))#iS';
                if (preg_match($validator_url, $redirect)) {
                    $this->result['data']['redirect'] = $redirect;
                }
            }
            // Отправка подтверждающего письма
            if (!empty($active_conditions['email'])) {
                $params_cond_email = $params;
                foreach ($active_conditions['email'] as $ace) {
                    $ace_trimed = $this->trim_email($ace['email_list'], $template_params);
                    if ($ace_trimed && is_array($ace_trimed)) {
                        $params_cond_email["send_to"] = array_shift($ace_trimed);
                        $params_cond_email['email_bcc'] = $ace_trimed ? $ace_trimed : array();
                        multiformNotification::send(multiformTemplate::process($ace['email_message'], $template_params), $params_cond_email);
                    }
                }
            }
            if (!empty($form['confirmation_email']) && !empty($form['confirmation_send_to']) && !empty($form['fields'][$form['confirmation_send_to']]) && !empty($record['fields'][$form['fields'][$form['confirmation_send_to']]['id']])) {
                $params_conf = $form;
                $params_conf['confirmation_send_to'] = $record['fields'][$form['fields'][$form['confirmation_send_to']]['id']];
                if (!empty($form['confirmation_message'])) {
                    $confirmation_message = multiformTemplate::process($form['confirmation_message'], $template_params);
                } else {
                    // Сообщение
                    $view = wa()->getView();
                    $custom_params = $mrm->getCustomParams($form['id']);

                    $view->assign('record', $record);
                    $view->assign('form', $form);
                    $view->assign('is_mail', 1);
                    $view->assign('hide_infoblocks', 1);
                    $view->assign("domain", wa('multiform')->getConfig()->getDomain());
                    $view->assign('custom_params', $custom_params);

                    $form_clone = $form;
                    $form_clone['plain_text'] = $form_clone['email_notification'] = 0;
                    $confirmation_message = (new multiformTemplate())->getEmailMessage($view, $form_clone, $template_params, $params['confirmation_subject']);

                    $view->clearAllAssign();
                }
                multiformNotification::send($confirmation_message, $params_conf);
            }

            // JS Callback
            if (!empty($form['js_callback'])) {
                $this->result['data']['js_callback'] = "<script>" . $form['js_callback'] . "</script>";
            }
        }

        // Очищаем кеш
        $cache_cond->delete();

        // Ставим отметку, что запись обработана
        $record_status = $mrm->updateById($this->record_id, array("status" => 1));

        if ($record_status) {
            $record['status'] = 1;
        }

        /**
         * @event multiform_frontend_save
         * @return void
         */
        $event_params = array("form" => $form, "record" => $record);
        wa()->event('multiform_frontend_save', $event_params);

        $cancel_notifications = ($backend && (!empty($form['cancel_backend_notifications']) || !isset($form['cancel_backend_notifications']))) ? 1 : 0;
        // Email Уведомления
        if (!empty($form['new_records']) && !$cancel_notifications) {
            $email_validator = new waEmailValidator();
            // Email получателей
            $email_list = array();
            if (!empty($form['email'])) {
                foreach (explode(",", $form['email']) as $email) {
                    $email = trim($email);
                    if ($email_validator->isValid($email)) {
                        $email_list[] = $email;
                    }
                }
            }

            if ($email_list) {

                unset($params['confirmation_send_to']);
                $params["send_to"] = $email_list;

                // Reply to
                if (!empty($form['reply_to']) && !empty($form['fields'][$form['reply_to']]) && !empty($record['fields'][$form['fields'][$form['reply_to']]['id']])) {
                    $params['confirmation_reply_to'] = $record['fields'][$form['fields'][$form['reply_to']]['id']];
                }

                // Добавляем вложения к письму
                if (!empty($form['email_attachments'])) {
                    $params['attachments'] = $mrm->getRecordAttachments($record, $form);;
                }

                $collection = new waContactsCollection('/id/' . $record['create_contact_id'] . '/', array('check_rights' => true));
                $contacts = $collection->getContacts('id,name');

                // Сообщение
                $view = wa()->getView();
                $view->assign('record', $record);
                $view->assign('form', $form);
                $view->assign('is_mail', 1);
                $view->assign("contacts", $contacts);
                $view->assign("domain", wa('multiform')->getConfig()->getDomain());
                if (!isset($custom_params)) {
                    $custom_params = $mrm->getCustomParams($form['id']);
                    $view->assign('custom_params', $custom_params);
                }

                $email_message = (new multiformTemplate())->getEmailMessage($view, $form, $template_params, $params['confirmation_subject']);

                $view->clearAllAssign();

                multiformNotification::send(multiformTemplate::process($email_message, $template_params), $params, !empty($form['plain_text']) ? 'text/plain' : 'text/html');
            }
        }

        // SMS уведомления
        if (!empty($form['new_records_sms']) && !empty($form['sms_phone']) && !$cancel_notifications) {
            $text = !empty($form['sms_notification']) ? multiformTemplate::process($form['sms_notification'], $template_params) : (!isset($form['sms_notification']) ? multiformTemplate::process(_wd('multiform', "You've got new notification: {Request:ID}"), $template_params) : "");
            if ($text) {
                $sms_params = array(
                    "to" => $form['sms_phone'],
                    "from" => isset($form['sms_from']) ? $form['sms_from'] : null
                );
                multiformNotification::sendSms($text, $sms_params);
            }
        }

        $this->generateResponse();
    }

    /**
     * If we have XMLHttpRequest, then generate json data, else redirect to referer page
     *
     * @param int $form_id
     * @param array[mixed] $result
     */
    protected function generateResponse()
    {
        $this->result['status'] = $this->hasErrors() ? 'fail' : 'ok';
        // Если был сделан ajax-запрос, то возвращаем json-ответ
        if (waRequest::isXMLHttpRequest()) {
            header("Content-type: application/json; charset=UTF-8");
            header("Cache-Control: must-revalidate");
            header("Pragma: no-cache");
            header("Expires: -1");
            exit(json_encode($this->result));
        } else {
            if ($this->form_id) {
                $session_name = 'multiform-session-' . $this->form_id;
                $form_session = wa()->getStorage()->get($session_name);
                if (!empty($form_session)) {
                    wa()->getStorage()->remove($session_name);
                }
                // Записываем в сессию результат запроса
                wa()->getStorage()->write($session_name, $this->result);
            }

            $url = !empty($this->result['data']['redirect']) ? $this->result['data']['redirect'] : waRequest::server('HTTP_REFERER');
            $this->redirect(array("url" => $url));
        }
    }

    /**
     * Add error message
     *
     * @param string|array $message
     * @param int $field_id
     * @param string $index
     */
    protected function addError($message, $field_id = 0, $index = '')
    {
        if (is_array($message)) {
            $this->result['errors']['messages'][key($message)] = current($message);
        } else {
            $this->result['errors']['messages'][] = $message;
        }
        if ($field_id) {
            $this->result['errors']['fields'] += [
                $field_id . '_' . $index => [
                    'id' => $field_id,
                    'index' => $index
                ]
            ];
        }
    }

    /**
     * Check, if we have any errors
     *
     * @return bool
     */
    protected function hasErrors()
    {
        return !empty($this->result['errors']['messages']);
    }

    /**
     * Redirect to url
     *
     * @param array $params
     * @param int $code - Server response code to return with the redirect
     */
    private function redirect($params = array(), $code = null)
    {
        if ((!is_array($params) && $params)) {
            $params = array(
                'url' => $params
            );
        }
        if (isset($params['url']) && $params['url']) {
            wa()->getResponse()->redirect($params['url'], $code);
        }
        if ($params) {
            $url = waSystem::getInstance()->getUrl();
            $i = 0;
            foreach ($params as $k => $v) {
                $url .= ($i++ ? '&' : '?') . $k . '=' . urlencode($v);
            }
        } else {
            $url = waSystem::getInstance()->getConfig()->getCurrentUrl();
        }
        wa()->getResponse()->redirect($url, $code);
    }

    /**
     * Get form information
     *
     * @return array
     */
    private function getForm()
    {
        if (!$this->form) {
            $mf = new multiformFormModel();
            $this->form = $mf->getFullForm($this->record['form_id'], true);
        }
        return $this->form;
    }

    /**
     * Get information about record
     *
     * @return array
     */
    private function getRecord()
    {
        if (!$this->record) {
            $mrm = new multiformRecordModel();
            $this->record = $mrm->getRecord($this->record_id);
        }
        return $this->record;
    }

    private function trim_email($email_list, $template_params)
    {
        $emails = array();
        foreach (explode(',', $email_list) as $em) {
            $emails[] = trim(multiformTemplate::process($em, $template_params));
        }
        return $emails;
    }

}
