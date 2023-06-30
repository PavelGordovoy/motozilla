<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformNotification
{

    /**
     * Send notification
     *
     * @param string $message
     * @param array $params
     * @param string $content_type
     */
    public static function send($message, $params, $content_type='text/html')
    {
        $params = self::prepareParams($params);
        $email_validator = new waEmailValidator();
        if ($params['to']) {
            try {
                $mail_message = new waMailMessage($params['subject'], $message);
                $mail_message->setTo($params['to']);
                if ($params['reply_to']) {
                    $mail_message->addReplyTo($params['reply_to']);
                }
                $mail_message->setFrom($params['from'], $params['from_name']);
                if ($params['email_cc']) {
                    foreach ($params['email_cc'] as $cc) {
                        $cc = trim($cc);
                        if ($email_validator->isValid($cc)) {
                            $mail_message->addCc($cc);
                        }
                    }
                }
                if ($params['email_bcc']) {
                    foreach ($params['email_bcc'] as $bcc) {
                        $bcc = trim($bcc);
                        if ($email_validator->isValid($bcc)) {
                            $mail_message->addBcc($bcc);
                        }
                    }
                }

                foreach ($params['attachments'] as $attach) {
                    $mail_message->addAttachment($attach['path'], $attach['name']);
                }
                if ($content_type !== 'text/html') {
                    $mail_message->setContentType($content_type);
                }
                $mail_message->generateId();
                $mail_message->send();
            } catch (Exception $e) {
                multiformHelper::log($e->getMessage());
            }
        }
    }

    /**
     * Send sms notification
     * 
     * @param string $text
     * @param array $params
     */
    public static function sendSms($text, $params)
    {
        try {
            $sms = new waSMS();

            $text = strip_tags(str_ireplace(array("<li>"), array("[br]  - "), $text));
            $text = str_ireplace(array("[br]", "[space]"), array("\r\n", " "), $text);

            $sms->send($params['to'], $text, isset($params['sms_from']) ? $params['sms_from'] : null);
        } catch (Exception $e) {
            multiformHelper::log($e->getMessage());
        }
    }

    /**
     * Prepare params before sending
     * 
     * @param array $params
     * @return array
     */
    private static function prepareParams($params)
    {
        // Send to
        $to = !empty($params['confirmation_send_to']) ? array($params['confirmation_send_to']) : (!empty($params['send_to']) ? $params['send_to'] : "");
        // Reply to
        if (!empty($params['confirmation_reply_to'])) {
            $reply_to = $params['confirmation_reply_to'];
        }
        // From
        if (!empty($params['confirmation_sender'])) {
            $from = $params['confirmation_sender'];
        } else {
            $app_settings_model = new waAppSettingsModel();
            $system_email = $app_settings_model->get('webasyst', 'email');
            $from = $system_email ? $system_email : 'no-reply@' . wa('multiform')->getConfig()->getDomain();
        }
        // From name
        if (!empty($params['confirmation_sender_name'])) {
            $from_name = $params['confirmation_sender_name'];
        } else {
            $from_name = _wd('multiform', "Application Web-forms");
        }
        // Subject
        if (!empty($params['confirmation_subject'])) {
            $subject = $params['confirmation_subject'];
        } else {
            $subject = _wd('multiform', "You've got new message");
        }
        // Attachments
        if (!empty($params['attachments'])) {
            $attachments = $params['attachments'];
        }

        foreach (array('email_cc', 'email_bcc') as $field_name) {
            if (!empty($params[$field_name])) {
                if (!is_array($params[$field_name])) {
                    $params[$field_name] = explode(',', $params[$field_name]);
                }
            } else {
                $params[$field_name] = array();
            }

        }
        if (!empty($params['email_bcc'])) {
            if (!is_array($params['email_bcc'])) {
                $params['email_bcc'] = explode(',', $params['email_bcc']);
            }
        }

        $new_params = array(
            'to' => !empty($to) ? $to : '',
            'reply_to' => !empty($reply_to) ? $reply_to : '',
            'from' => $from,
            'from_name' => $from_name,
            'subject' => $subject,
            'attachments' => !empty($attachments) ? $attachments : array(),
            'email_cc' =>  $params['email_cc'],
            'email_bcc' => $params['email_bcc']
        );

        return $new_params;
    }

}
