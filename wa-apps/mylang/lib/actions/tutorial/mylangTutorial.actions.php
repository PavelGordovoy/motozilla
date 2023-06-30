<?php

/**
 * Tutorial tab in shop backend.
 */
class mylangTutorialActions extends waViewActions
{
    // Right sidebar and inner layout for Tutorial section
    protected function defaultAction(): void
    {
        $this->setLayout(new mylangBackendLayout());
        $this->getResponse()->setTitle(_w('Welcome to MyLang'));

        $checked = $this->getSettings();
        $checked['structure'] = $this->checkStructure($checked);

        $this->assignVariables();
        $this->view->assign(array(
            'checked'           => $checked,
        ));
    }

    /**
     * @return array
     */
    private function getSettings(): array
    {
        $settings = mylangHelper::getSettings();
        return [
            'mainLocale'    => ifset($settings, 'main_language', false),
            'sites'         => ifset($settings, 'domains', false),
            'locales'       => ifset($settings, 'locales', false),
        ];
    }

    protected function selectorAction(): void
    {

        $this->setLayout(new mylangBackendLayout());
        $this->getResponse()->setTitle(_w('Tutorial').':'._w('Selector'));
        $this->view->assign(array(
            'backend_tutorial'  => self::backendTutorialEvent(),
            'actions'           => self::getActions(self::backendTutorialEvent()),
            'lang'              => substr(wa()->getLocale(), 0, 2),
            'checked'           => $this->getSettings(),
        ));
        $this->assignVariables();
    }

    protected function pluginsAction()
    {
        $this->setLayout(new mylangBackendLayout());
        $this->getResponse()->setTitle(_w('Tutorial').':'._w('Selector'));
        $this->view->assign(array(
                                'backend_tutorial'  => self::backendTutorialEvent(),
                                'actions'           => self::getActions(self::backendTutorialEvent()),
                                'lang'              => substr(wa()->getLocale(), 0, 2),
                                'checked'           => $this->getSettings(),
                            ));
        $this->assignVariables();
    }

    protected function paymentAction()
    {
        $this->assignVariables();
    }

    protected function shippingAction()
    {
        $this->assignVariables();
    }

    protected function profitAction()
    {
        $this->assignVariables();
    }


    protected function doneAction()
    {
        $app_settings_model = new waAppSettingsModel();
        $app_settings_model->del('shop', 'show_tutorial');
        $app_settings_model->get('shop', 'setup_demo_time');
        exit;
    }

    protected function assignVariables()
    {
        $this->view->assign('actions', self::getActions(true));
        $this->view->assign('active', waRequest::get('action', null, waRequest::TYPE_STRING));

        $locales = waLocale::getAll('name');
        $this->view->assign(
            [
                'locales' => $locales,
                'backend_tutorial' => self::backendTutorialEvent(),
                'actions' => self::getActions(self::backendTutorialEvent()),
                'lang' => substr(wa()->getLocale(), 0, 2),
            ]
        );

    }

    protected function getTemplate()
    {
        $template = parent::getTemplate();
        $ext = $this->view->getPostfix();
        $locale_template = str_replace($ext, '.'.wa()->getLocale().$ext, $template);
        if (is_readable(wa()->getAppPath($locale_template, 'shop'))) {
            return $locale_template;
        }
        return $template;
    }

    protected function customAction()
    {
        $page_id = waRequest::request('page', '', 'string');
        $params = array(
            'page' => $page_id,
        );
        $blocks = wa()->event('backend_tutorial_page', $params);
        if (!$blocks) {
            throw new waException('Not found', 404);
        }

        $html = array();
        foreach ($blocks as $app_id => $b) {
            if ($b && !is_array($b) && !is_object($b)) {
                $html[] = '<div class="block-'.$app_id.'">'.$b."</div>\n";
            }
        }

        $this->view->assign(array(
            'html'    => implode('', $html),
            'actions' => self::getActions(true),
        ));
    }

    public static function getActions($backend_tutorial = false): array
    {
        $backend_url = wa('mylang', 1)->getAppUrl();
        $tutorial_url = '?module=tutorial';

        $actions = array(
            'welcome'  => array(
                'href'     => $backend_url.$tutorial_url,
                'name'     => _w('Basic settings'),
                'complete' => true,
            ),
            'selector' => array(
                'href'     => $backend_url.$tutorial_url.'&action=selector',
                'name'     => _w('Selector'),
                'complete' => false,
            ),
            'plugins' => array(
                'href'     => $backend_url.$tutorial_url.'&action=plugins',
                'name'     => _w('External plugins'),
                'complete' => false,
            ),
        );

        $app_settings_model = new waAppSettingsModel();
        $app_settings_model->clearCache('mylang');
        $welcome = $app_settings_model->get('mylang', 'welcome');
        $wa_log_model = new waLogModel();

        foreach ($actions as $id => &$action) {
            if ($id === 'welcome') {
                if ($welcome) {
                    //"welcome" requirements were not met
                    break;
                }
                $action['complete'] = true;
            }

            if ($id === 'products') {
                $action['complete'] = $shop_product_model->countAll() > 0;
            }

            if ($id === 'design') {
                $app_themes = wa()->getThemes('mylang'); // all shop themes
                $storefronts = shopHelper::getStorefronts(true); // all shop storefronts

                $asm = new waAppSettingsModel();
                $setup_demo_time = $asm->get('mylang', 'setup_demo_time', null);

                if (empty($app_themes) || empty($storefronts)) {
                    continue;
                }

                foreach ($storefronts as $storefront) {
                    $storefront_theme = ifempty($storefront['route']['theme'], 'default');
                    if (!array_key_exists($storefront_theme, $app_themes)) {
                        continue;
                    }

                    $storefront_theme = $app_themes[$storefront_theme];

                    // If there is any design theme that has changed after the demo database was imported, then everything is fine
                    if ($setup_demo_time) {
                        $custom_theme_xml_path = $storefront_theme->path_custom .'/theme.xml';
                        if (!file_exists($custom_theme_xml_path) || (file_exists($custom_theme_xml_path) && filemtime($custom_theme_xml_path) > $setup_demo_time)) {
                            $action['complete'] = true;
                        }
                        continue;
                    }

                    // If any design theme other than default is used or the theme has been changed, then everything is fine
                    if ($storefront_theme->id !== 'default' || $storefront_theme->path_custom) {
                        $action['complete'] = true;
                        continue;
                    }
                }
            }

            if ($id === 'payment') {
                $action['complete'] = $wa_log_model->countByField(array(
                    'app_id' => 'shop',
                    'action' => array('payment_plugin_add', 'payment_plugin_edit')
                )) > 0;
            }

            if ($id === 'shipping') {
                $action['complete'] = $wa_log_model->countByField(array(
                    'app_id' => 'shop',
                    'action' => array('shipping_plugin_add', 'shipping_plugin_edit')
                )) > 0;
            }
        }
        unset($action);

        if ($backend_tutorial) {
            if (is_array($backend_tutorial)) {
                $tutorial_event = $backend_tutorial;
            } else {
                $tutorial_event = self::backendTutorialEvent();
            }

            if ($tutorial_event && is_array($tutorial_event)) {
                foreach ($tutorial_event as $plugin_id => $event_result) {
                    if (empty($event_result['sidebar_li']) || !is_array($event_result['sidebar_li'])) {
                        continue;
                    }
                    $acts = $event_result['sidebar_li'];
                    if (empty($acts[0])) {
                        $acts = array($acts);
                    }
                    foreach ($acts as $i => $a) {
                        if (empty($a['href'])) {
                            $a['href'] = 'javascript:void(0)';
                        } else {
                            $a['href'] = $backend_url.$tutorial_url.$a['href'];
                        }
                        if (empty($a['name'])) {
                            $a['name'] = $plugin_id;
                        }

                        $a['complete'] = !empty($a['complete']) && !$welcome; //If welcome complete

                        $actions[ifset($a['action'], $plugin_id.'.'.$i)] = $a;
                    }
                }
            }
        }

        return $actions;
    }

    public static function backendTutorialEvent()
    {
        static $result = null;
        if ($result === null) {
            /*
             * @event backend_tutorial
             * @return array[string][string]string $return[%plugin_id%]['sidebar_li'] html output
             * @return array[string][string]string $return[%plugin_id%]['sidebar_block'] html output
             */
            $result = wa('mylang')->event('backend_tutorial');
            if (!$result) {
                $result = array();
            }
        }
        return $result;
    }

    public static function getTutorialProgress(): array
    {
        $total = 0;
        $complete = 0;

        $actions = self::getActions(true);
        if ($actions && is_array($actions)) {
            $total = count($actions);
            foreach ($actions as $a) {
                if (!empty($a['complete'])) {
                    $complete++;
                }
            }
        }

        return array(
            'total'    => $total,
            'complete' => $complete
        );
    }

    /**
     * @param array $checked
     * @return array
     * @throws waException
     */
    private function checkStructure(array $checked): array
    {
        $status = true;
        $sites = $checked['sites'];
        if(empty($sites)) {
            return [];
        }
        $routing = wa()->getRouting();

        foreach ($sites as &$site) {
            $site = ['url' => $site];
            $site['routes'] = $routing->getRoutes($site['url']);
            if (empty($site['routes'])) {
                $site['status'] = false;
                $site['message'] = _w('Empty site');
                $status = false;
                continue;
            }
            $site['locales'] = $checked['locales'];
            foreach ($site['routes'] as $route) {
                if (array_key_exists('locale', $route) && in_array($route['locale'], $site['locales'], true)) {
                    unset($site['locales'][$route['locale']]);
                }
            }
            if(empty($site['locales'])) {
                $site['status'] = true;
                $site['message'] = _w('Site has rules for all locales');
                continue;
            }
            $site['status'] = false;
            $site['message'] = _w('Site has no rules for locales: ').implode(', ', $site['locales']);
            $status = false;
        }
        return ['status' => $status, 'sites' => $sites];
    }
}

