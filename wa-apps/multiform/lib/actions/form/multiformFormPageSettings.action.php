<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageSettingsAction extends waViewAction
{

    public function execute()
    {
        // ID формы
        $id = waRequest::get("id", 0, waRequest::TYPE_INT);

        // Настройки формы
        $form_model = new multiformFormModel();
        $form_settings = $form_model->getFormSettings($id);

        if (!multiformHelper::hasFullFormAccess($form_settings)) {
            throw new waRightsException('Access denied');
        }

        // Поля типа email
        $field_model = new multiformFieldModel();
        $email_fields = $field_model->getByField(array("form_id" => $id, "type" => 'email', "hidden" => 0), true);

        // Домены и их правила маршрутизации
        $domains = wa()->getRouting()->getDomains();
        $routes = array();
        foreach ($domains as $domain) {
            $routes[$domain] = multiformHelper::getRoutes($domain);
        }

        // Проверяем, указаны ли в приложении Магазин данные Google ReCaptcha
        if (wa()->appExists('shop') && !isset($form_settings['recaptcha_sitekey']) && !isset($form_settings['recaptcha_secretkey'])) {
            $factories = wa('shop')->getConfig()->getOption('factories');
            if ($factories) {
                if (!empty($factories['captcha'])) {
                    if (is_array($factories['captcha'])) {
                        $captcha_options = $factories['captcha'][1];
                        $form_settings['recaptcha_sitekey'] = ifset($captcha_options['sitekey']);
                        $form_settings['recaptcha_secretkey'] = ifset($captcha_options['secret']);
                    }
                }
            }
        }

        $this->view->assign('form', $form_settings);
        $this->view->assign('lang', substr(wa()->getLocale(), 0, 2));
        $this->view->assign("dom", wa('multiform')->getConfig()->getDomain());
        $this->view->assign('email_fields', $email_fields);
        $this->view->assign('routes', $routes);
    }

}
