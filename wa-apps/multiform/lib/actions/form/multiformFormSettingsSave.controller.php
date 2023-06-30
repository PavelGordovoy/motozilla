<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormSettingsSaveController extends waJsonController
{

    public function execute()
    {
        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        if (!$id) {
            $this->errors['messages'][] = _w("Hey, where is form ID?");
            return;
        }

        $form_model = new multiformFormModel();
        $params_model = new multiformFormParamsModel();

        $form = $form_model->getFormSettings($id);
        if (!multiformHelper::hasFullFormAccess($form)) {
            throw new waRightsException('Access denied');
        }

        $data = waRequest::post("basic", array());
        $params = waRequest::post("param", array());

        try {
            // Статус формы
            $data['status'] = !empty($data['status']) ? 1 : 0;
            // ID формы
            if (!empty($data['url'])) {
                $data['url'] = preg_replace("/[^0-9a-z\-_]/", "", $data['url']);
                if (strlen($data['url']) > 20) {
                    $data['url'] = substr($data['url'], 0, 20);
                }
                $data['url'] = $form_model->generateUrl($id, $data['url']);
            }
            // Проверяем имя формы на количество символов
            if (mb_strlen($data['name'], "UTF-8") > 255) {
                $data['name'] = mb_substr($data['name'], 0, 255, "UTF-8");
            }
            if (mb_strlen($data['title'], "UTF-8") > 255) {
                $data['title'] = mb_substr($data['title'], 0, 255, "UTF-8");
            }

            // Расписание
            if (waRequest::post('schedule_activity')) {
                $schedule = waRequest::post('schedule');
                $start = $schedule['start'];
                $end = $schedule['end'];
                $start_timestamp = strtotime((int) $start['year'] . '-' . (int) $start['month'] . '-' . (int) $start['day'] . " " . (int) $start['hour'] . ":" . (int) $start['minute']);
                $end_timestamp = strtotime((int) $end['year'] . '-' . (int) $end['month'] . '-' . (int) $end['day'] . " " . (int) $end['hour'] . ":" . (int) $end['minute']);

                $data['start'] = date("Y-m-d H:i:s", $start_timestamp);
                // Если конечный срок публикации меньше начального, то удаляем конечный срок
                if ($end_timestamp <= $start_timestamp) {
                    $data['end'] = null;
                } else {
                    $data['end'] = date("Y-m-d H:i:s", $end_timestamp);
                }
            } else {
                $data['start'] = $data['end'] = null;
            }

            // Подтверждающее письмо
            if (!empty($params['confirmation_email']) && multiformHelper::notificationsAccess() >= 3) {
                $validator = new waEmailValidator();
                // Поле отправителя в подтверждающем письме
                if (!empty($params['confirmation_sender'])) {
                    if (!$validator->isValid($params['confirmation_sender'])) {
                        $this->errors['messages'][] = _w('Field "Sender" contains wrong email');
                        $this->errors['fields'][] = 'f-sender-field';
                        return;
                    }
                }
                if (!empty($params['confirmation_reply_to'])) {
                    if (!$validator->isValid($params['confirmation_reply_to'])) {
                        $this->errors['messages'][] = _w('Field "Reply to" contains wrong email');
                        $this->errors['fields'][] = 'f-reply-to-field';
                        return;
                    }
                }
            }
            if (multiformHelper::notificationsAccess() < 3) {
                foreach ([
                             'confirmation_email', 'confirmation_send_to', 'confirmation_sender', 'confirmation_sender_name',
                             'confirmation_reply_to', 'confirmation_subject', 'confirmation_message'
                         ] as $field) {
                    $params[$field] = ifset($form, $field, '');
                }
            }

            // Ссылка Redirect
            if ($params['confirmation'] == 'redirect' && !empty($params['confirmation_redirect'])) {
                $validator_url = '#\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))#iS';
                if (!preg_match($validator_url, $params['confirmation_redirect'])) {
                    $this->errors['messages'][] = _w('Field "Redirect" contains wrong URL');
                    $this->errors['fields'][] = 'f-confirmation-redirect';
                    return;
                }
            }

            // Выводить тег <form>
            $params['use_tag_form'] = !empty($params['use_tag_form']) ? 1 : 0;

            // Обновляем основные данные формы
            $form_model->updateById($id, $data);

            // Ограничение по доменам и витринам
            if (waRequest::post('domain_limitations')) {
                $domain_routes = array();
                $domains = waRequest::post('domain');
                $routes = waRequest::post('route');
                foreach ($domains as $k => $domain) {
                    if ($domain && isset($routes[$k]) && $routes[$k]) {
                        $domain_routes[$domain][] = $routes[$k];
                    }
                }
                if ($domain_routes) {
                    $params['domain_limitations'] = serialize($domain_routes);
                }
            }

            // Ограничение по отправкам формы
            if ($params['form_submissions'] == 'limit' && !$params['form_submit_limit']) {
                $params['form_submissions'] = '';
                unset($params['form_submit_limit'], $params['form_submit_limit_freq']);
            }

            // Ограничение по отправкам формы для пользователей
            if ($params['form_submissions_auth'] == 'limit' && !$params['form_submit_limit_auth']) {
                $params['form_submissions_auth'] = '';
                unset($params['form_submit_limit_auth'], $params['form_submit_limit_freq_auth']);
            }

            // Сохраняем параметры
            $params_model->save($id, $params);
        } catch (Exception $e) {
            multiformHelper::log($e->getMessage());
            $this->errors['messages'][] = _w("Something wrong. Check log files.");
        }
    }

}
