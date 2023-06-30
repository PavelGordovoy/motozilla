<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformTemplate
{

    /**
     * Converts message with template and smarty vars to normal text
     *
     * @param string $text
     * @param array $params
     * @return string
     */
    public static function process($text, $params)
    {
        if ($text) {
            // Получаем шаблонные переменные
            preg_match_all("/{Field:(?P<field>\w+)}|{Record:(?P<record>\w+)}|{Form:(?P<form>\w+)}|{Contact:(?P<contact>\w+)}|{Request:(?P<request>\w+)}/", $text, $matches);

            $search = $replacement = array();

            $matched_fields = array_filter($matches['field']);
            $matched_records = array_filter($matches['record']);
            $matched_forms = array_filter($matches['form']);
            $matched_contact = array_filter($matches['contact']);
            $matched_request = array_filter($matches['request']);

            $form = self::get_form($params);

            static $custom_params = array();
            static $custom_transliterate_params = array();
            if (!isset($custom_params[$form['id']])) {
                $mr = new multiformRecordModel();
                // Дополнительные параметры, переданные в форму
                $custom_params[$form['id']] = $mr->getCustomParams($form['id']);
                $custom_transliterate_params[$form['id']] = self::transliterateKeys($custom_params[$form['id']]);
            }

            // Названия полей формы
            if ($matched_fields) {
                foreach ($matched_fields as $f_id) {
                    if (!empty($form['fields'][$f_id]) && $form['fields'][$f_id]['status']) {
                        $search[] = "/{Field:" . $f_id . "}/";
                        $replacement[] = multiformHelper::secureString($form['fields'][$f_id]['title']);
                    } elseif (!empty($custom_transliterate_params[$form['id']][$f_id])) {
                        $search[] = "/{Field:" . $f_id . "}/";
                        $replacement[] = multiformHelper::secureString($custom_params[$form['id']][$custom_transliterate_params[$form['id']][$f_id]]);
                    }
                }
            }

            $private_field_access = multiformHelper::privateFieldAccess($form);

            // Заменяем записи
            if ($matched_records && isset($params['record']['fields'])) {
                foreach ($matched_records as $r) {
                    if ($r == 'DATE' && !empty($params['record']['create_datetime'])) {
                        $search[] = "/{Record:{$r}}/";
                        $replacement[] = $params['record']['create_datetime'];
                    } elseif ($r == 'ALL' && !empty($params['record']) && !empty($form['fields'])) {
                        $search[] = "/{Record:{$r}}/";
                        // Сообщение
                        $view = wa()->getView();
                        $view->assign('record', $params['record']);
                        $view->assign('form', $form);
                        $view->assign('is_mail', 1);
                        $view->assign('hide_infoblocks', 1);
                        $view->assign("domain", wa('multiform')->getConfig()->getDomain());
                        $view->assign('custom_params', $custom_params[$form['id']]);
                        $replacement[] = $view->fetch(wa('multiform')->getAppPath('templates/actions/form/include.record.template.html'));
                        $view->clearAllAssign();
                    } elseif ((int) $r && !empty($form['fields'][$r]['status']) && isset($params['record']['fields'][$form['fields'][$r]['id']])) {
                        $search[] = "/{Record:{$r}}/";
                        if (is_array($params['record']['fields'][$form['fields'][$r]['id']])) {
                            // Для файлов формируем ссылку на скачивание
                            $html = "<ul style='padding-left: 15px; margin: 0;'>";
                            if (!empty($form['fields'][$r]) && $form['fields'][$r]['type'] == 'file') {
                                foreach ($params['record']['fields'][$form['fields'][$r]['id']] as $file) {
                                    $html .= "<li><a title=\"" . _wd('multiform', "Download") . "\" href=\"" . wa()->getRouteUrl('multiform/frontend/downloadFile', array("hash" => $file['hash'], "domain" => wa('multiform')->getConfig()->getDomain()), true) . "\">" . multiformHelper::secureString($file['filename']) . "</a></li>";
                                }
                            } else {
                                foreach ($params['record']['fields'][$form['fields'][$r]['id']] as $v) {
                                    $html .= "<li>" . multiformHelper::secureString($v) . "</li>";
                                }
                            }
                            $html .= "</ul>";
                            $replacement[] = $html;
                        } else {
                            $replacement[] = multiformHelper::secureString($params['record']['fields'][$form['fields'][$r]['id']]);
                        }
                        // Если нет доступа к скрытым полям, очищаем вывод
                        if ($form['fields'][$r]['private'] && !$private_field_access) {
                            $replacement[] = '';
                        }
                    } /* Дополнительные поля */
                    elseif (!intval($r) && !empty($params['record']['fields'][0][$custom_transliterate_params[$form['id']][$r]])) {
                        $search[] = "/{Record:{$r}}/";
                        $replacement[] = multiformHelper::secureString($params['record']['fields'][0][$custom_transliterate_params[$form['id']][$r]]);
                    }
                }
            }

            // Заменяем поля формы
            if ($matched_forms && $form) {
                foreach ($matched_forms as $mf) {
                    if ($mf == 'ID') {
                        $replacement[] = !empty($params['form']['form_id']) ? $params['form']['form_id'] : '';
                    } elseif ($mf == 'NAME') {
                        $replacement[] = $form['name'];
                    } elseif ($mf == 'DESCRIPTION') {
                        $replacement[] = $form['description'];
                    } elseif ($mf == 'URL') {
                        $replacement[] = wa()->getRouteUrl('multiform/frontend', array('url' => $form['hash'], 'domain' => wa('multiform')->getConfig()->getDomain()), true);
                    } elseif ($mf == 'LINK') {
                        $replacement[] = "<a href=\"" . wa()->getRouteUrl('multiform/frontend', array('url' => $form['hash'], 'domain' => wa('multiform')->getConfig()->getDomain()), true) . "\" title=\"" . multiformHelper::secureString($form['name']) . "\">" . multiformHelper::secureString($form['name']) . "</a>";
                    } else {
                        continue;
                    }
                    $search[] = "/{Form:{$mf}}/";
                }
            }

            // Заменяем поля контакта
            if ($matched_contact && !empty($params['record']['create_contact_id'])) {
                $contact = new waContact($params['record']['create_contact_id']);
                foreach ($matched_contact as $mc) {
                    $val = $contact->get($mc);
                    if (is_array($val)) {
                        $val = first($val);
                    }
                    $search[] = "/{Contact:{$mc}}/";
                    $replacement[] = multiformHelper::secureString($val);
                }
            }

            // Заменяем ID обращения
            if ($matched_request && !empty($params['record']['id']) && $form) {
                $request = reset($matched_request);
                $search[] = "/{Request:{$request}}/";
                $replacement[] = multiformHelper::formatRecordId($params['record']['id'], $form);
            }

            // Заменяем шаблонные переменные
            if ($search && $replacement) {
                $text = preg_replace($search, $replacement, $text);
            }

            // Заменяем неиспользованные шаблонные переменные, чтобы не возникало ошибок SMARTY
            $text = preg_replace(array("/{Field:\w+}|{Record:\w+}|{Form:\w+}|{Contact:\w+}|{Request:(?P<request>\w+)}/"), '', $text);

            $view = wa()->getView();
            if (isset($params['record'])) {
                $view->assign('record', $params['record']);
            }
            if ($form) {
                $view->assign('form', $form);
            }
            try {
                $text = $view->fetch('string:' . $text);
            } catch (Exception $ex) {

            }
        }
        return $text;
    }

    /**
     * Transliterate string
     *
     * @param string $str
     * @param string $default
     * @return string
     * @throws waException
     */
    public static function transliterate($str, $default = '')
    {
        $str = preg_replace('/\s+/u', '-', $str);
        if ($str) {
            foreach (waLocale::getAll() as $lang) {
                $str = waLocale::transliterate($str, $lang);
            }
        }
        $str = str_replace('-', '_', $str);
        $str = preg_replace('/[^a-zA-Z0-9_-]+/', '', $str);
        if (!$str) {
            $str = $default;
        }

        return $str ? strtolower($str) : '';
    }

    private static function transliterateKeys($params)
    {
        $new_params = [];
        if ($params) {
            foreach ($params as $k => $v) {
                $new_params[self::transliterate($k)] = $k;
            }
        }
        return $new_params;
    }

    /**
     * Get form information
     *
     * @param array $params
     * @return array
     */
    private static function get_form($params)
    {
        if (isset($params['form'])) {
            return $params['form'];
        } else {
            $form_id = !empty($params['form_id']) ? $params['form_id'] : (!empty($params['record']) ? $params['record']['form_id'] : 0);
            $mform = new multiformFormModel();
            // Ключами полей будет id
            return $mform->getFullForm($form_id, true);
        }
    }

    /**
     * Get email message, which is ready to send
     *
     * @param waSmarty3View $view
     * @param array $form
     * @param array $template_params
     * @param string $title
     * @return mixed|string
     */
    public function getEmailMessage($view, $form, $template_params, $title = '')
    {
        if (empty($form['plain_text'])) {
            $content = $this->getHtmlEmailMessage($view, $form, $template_params, $title);
        } else {
            $content = $this->getPlainEmailMessage($view, $form, $template_params);
        }
        return $content;
    }

    /**
     * Helper for getEmailMessage. Get text/html message
     *
     * @param waSmarty3View $view
     * @param array $form
     * @param array $template_params
     * @param string $title
     * @return mixed
     */
    private function getHtmlEmailMessage($view, $form, $template_params, $title)
    {
        $view->assign('title', $title);
        if (!empty($form['email_notification'])) {
            $content = multiformTemplate::process($form['email_notification'], $template_params);
            $view->assign('content', $content);
        }
        return $view->fetch(wa('multiform')->getAppPath('templates/include.email_message.html'));
    }

    /**
     * Helper for getEmailMessage. Get text/plain message
     *
     * @param waSmarty3View $view
     * @param array $form
     * @param array $template_params
     * @return mixed
     */
    private function getPlainEmailMessage($view, $form, $template_params)
    {
        if (!empty($form['email_notification'])) {
            $content = multiformTemplate::process($form['email_notification'], $template_params);
            $content = strip_tags(str_ireplace(array("<li>"), array("[br]  - "), $content));
        } else {
            $content = $view->fetch(wa('multiform')->getAppPath('templates/actions/form/include.record.template.plain.html'));
        }
        $content = str_ireplace(array("[br]", "[space]"), array("\r\n", " "), $content);

        return $content;
    }

}
