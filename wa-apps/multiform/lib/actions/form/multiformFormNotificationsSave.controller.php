<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormNotificationsSaveController extends waJsonController
{

    public function execute()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        if (!$id) {
            $this->errors['messages'][] = _w("Hey, where is form ID?");
            return;
        }

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($id);
        if (!multiformHelper::hasFullFormAccess($form) || multiformHelper::notificationsAccess() < 3) {
            throw new waRightsException('Access denied');
        }

        $params_model = new multiformFormParamsModel();

        $params = array();

        try {
            $email_validator = new waEmailValidator();

            // Почта получателя уведомлений
            $emails = waRequest::post("email");
            $email_list = array();
            foreach (explode(",", $emails) as $email) {
                $email = trim($email);
                if ($email_validator->isValid($email) && count($email_list) < 10) {
                    $email_list[] = $email;
                }
            }
            $params['email'] = $email_list ? implode(",", $email_list) : "";

            $params['reply_to'] = (int) waRequest::post("reply_to", 0);
            $params['new_records'] = (int) waRequest::post("new_records", 0);
            $params['new_comments'] = (int) waRequest::post("new_comments", 0);
            $params['cancel_backend_notifications'] = (int) waRequest::post("cancel_backend_notifications", 0);

            // Email отправителя
            $email_sender = waRequest::post("email_sender");
            if (!$email_validator->isValid($email_sender)) {
                $this->errors['messages'][] = _w('Email') . " " . multiformHelper::secureString($email_sender) . " " . _w("is not valid");
                $this->errors['fields'][] = '.f-email-sender-field';
                return;
            }
            $params['email_sender'] = $email_sender;

            $params['email_sender_name_record'] = waRequest::post("email_sender_name_record");
            $params['email_sender_name_comment'] = waRequest::post("email_sender_name_comment");
            $params['email_subject_record'] = waRequest::post("email_subject_record");
            $params['email_subject_comment'] = waRequest::post("email_subject_comment");

            // Emails CC
            $email_cc = waRequest::post("email_cc");
            $email_list = array();
            foreach (explode(",", $email_cc) as $cc) {
                $cc = trim($cc);
                if ($email_validator->isValid($cc) && count($email_list) < 10) {
                    $email_list[] = $cc;
                }
            }
            $params['email_cc'] = $email_list ? implode(",", $email_list) : "";

            // Emails BCC
            $email_bcc = waRequest::post("email_bcc");
            $email_list = array();
            foreach (explode(",", $email_bcc) as $bcc) {
                $bcc = trim($bcc);
                if ($email_validator->isValid($bcc) && count($email_list) < 10) {
                    $email_list[] = $bcc;
                }
            }
            $params['email_bcc'] = $email_list ? implode(",", $email_list) : "";

            $params['email_attachments'] = (int) waRequest::post("email_attachments", 0);

            $params['plain_text'] = (int) waRequest::post("plain_text", 0);

            $params['email_notification'] = waRequest::post("email_notification");

            $params['sms_phone'] = waRequest::post("sms_phone");

            $sms = waRequest::post('sms', array());
            $path = $this->getConfig()->getPath('config', 'sms');
            $save = array();
            foreach ($sms as $s) {
                $from = $s['from'];
                $adapter = $s['adapter'];
                unset($s['from']);
                unset($s['adapter']);
                $empty = true;
                foreach ($s as $v) {
                    if ($v) {
                        $empty = false;
                        break;
                    }
                }
                if (!$empty) {
                    if (!$from) {
                        $from = '*';
                    }
                    foreach (explode("\n", $from) as $from) {
                        $from = trim($from);
                        $save[$from] = $s;
                        $save[$from]['adapter'] = $adapter;
                    }
                }
            }
            waUtils::varExportToFile($save, $path);

            $params['new_records_sms'] = (int) waRequest::post("new_records_sms", 0);
            $params['new_comments_sms'] = (int) waRequest::post("new_comments_sms", 0);

            $params['sms_from'] = waRequest::post("sms_from");
            $params['sms_notification'] = waRequest::post("sms_notification");

            // Сохраняем параметры
            $params_model->save($id, $params, 'notifications');
        } catch (Exception $e) {
            multiformHelper::log($e->getMessage());
            $this->errors['messages'][] = _w("Something wrong. Check log files.");
        }
    }

}
