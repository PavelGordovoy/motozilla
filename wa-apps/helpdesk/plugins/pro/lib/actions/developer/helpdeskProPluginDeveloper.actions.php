<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginDeveloperActions extends waJsonActions
{

    /**
     * Save settings
     */
    public function saveSettingsAction()
    {
        // Только админ имеет доступ к данной функции
        if (!wa()->getUser()->isAdmin()) {
            $this->errors = 1;
            return;
        }

        $developer_key = waRequest::post('developer_key');
        $developer_id = waRequest::post('developer_id');
        $old_settings = helpdeskProPluginHelper::getSettings();

        wao(new helpdeskProPluginSettingsModel())->saveSettings(array_merge($old_settings, array('developer_key' => $developer_key, 'developer_id' => $developer_id)));

        if (count(explode(PHP_EOL, $developer_id)) === 1) {
            // Если ID разработчика был изменен, удаляем список продуктов
            if ((int) $old_settings['developer_id'] !== $developer_id) {
                $path = wa()->getDataPath('plugins/pro/products.php', false, 'helpdesk', true);
                waUtils::varExportToFile(array(), $path);
            }
        }
        $this->response = 1;
    }

    /**
     * Parse products
     */
    public function loadProductsAction()
    {
        // Только админ имеет доступ к данной функции
        if (!wa()->getUser()->isAdmin()) {
            $this->errors = 1;
            return;
        }

        $developer_ids = helpdeskProPluginHelper::getSettings('developer_id', 0);
        if ($developer_ids) {
            $result = '';
            foreach (explode(PHP_EOL, $developer_ids) as $developer_id) {
                $waUrl = "https://www.webasyst.ru/store/developer/" . $developer_id . '/';
                $net = new waNet();
                try {
                    $result .= $net->query($waUrl, null, waNet::METHOD_GET);
                } catch (waException $e) {
                    $this->errors[] = _wp('Cannot load products for' . ' ' . $developer_id);
                }
            }
            $this->response = $result;
        } else {
            $this->errors = _wp('Developer ID is empty');
        }
    }

    /**
     * Save products to file
     */
    public function saveProductsAction()
    {
        // Только админ имеет доступ к данной функции
        if (!wa()->getUser()->isAdmin()) {
            $this->errors = 1;
            return;
        }

        $data = array();
        $products = waRequest::post('products', array());
        if ($products && !empty($products['name']) && !empty($products['slug'])) {
            foreach ($products['name'] as $k => $v) {
                if (!empty($products['slug'][$k])) {
                    $data[$products['slug'][$k]] = $v;
                }
            }
        }
        $path = wa()->getDataPath('plugins/pro/products.php', false, 'helpdesk', true);
        waUtils::varExportToFile($data, $path);
    }

    /**
     * Check domain license
     */
    public function licenseCheckAction()
    {
        $domain = waRequest::post('domain');
        $product = waRequest::post('product', '');
        $order = waRequest::post('order', '');
        $check_type = waRequest::post('type', 'domain');

        if ($check_type == 'domain' && !$domain) {
            $this->errors = _wp('Specify domain');
            return;
        }
        if ($check_type == 'order' && !$order) {
            $this->errors = _wp('Specify order number');
            return;
        }

        $view = wa()->getView();

        $products = wao(new helpdeskProPluginHelper())->getProducts();
        $view->assign('products', $products);
        // Проверка домена/номера заказа
        $data = $check_type == 'domain' ? $this->getDomainLicense($domain, $product) : $this->getOrderInfo($order);
        $template = wa()->getAppPath('plugins/pro/templates/actions/developer/' . ($check_type == 'domain' ? 'DeveloperLicenseDomainCheck.html' : 'DeveloperLicenseOrderCheck.html'), 'helpdesk');
        $view->assign('all_data', $data);
        $developer_ids = helpdeskProPluginHelper::getSettings('developer_id', 0);
        $view->assign('developer_ids', explode(PHP_EOL, $developer_ids));
        $this->response = $view->fetch($template);
    }

    /**
     * Get developer data
     *
     * @param string $api_key
     * @param array $params
     * @return array
     */
    private function getData($api_key, $params = array())
    {
        $waUrl = "https://www.webasyst.ru/my/api/developer/";

        $action = $params['action'];
        $custom_headers = array('X-API-Key' => $api_key);
        $net = new waNet(array('format' => waNet::FORMAT_JSON), $custom_headers);
        try {
            $response = $net->query($waUrl . $action, null, waNet::METHOD_GET);
        } catch (waException $e) {
            $response = json_decode($e->getMessage(), true);
        }
        return $response;
    }

    /**
     * Get HTML page of developer products
     *
     * @param int $developer_id
     * @return string
     */
    private function getProductsHTMLPage($developer_id)
    {
        // Только админ имеет доступ к данной функции
        if (!wa()->getUser()->isAdmin()) {
            $this->errors = 1;
            return;
        }

        $waUrl = "https://www.webasyst.ru/store/vendor/" . $developer_id;
        $net = new waNet();
        try {
            $response = $net->query($waUrl, null, waNet::METHOD_GET);
        } catch (waException $e) {
            $response = '';
        }
        return $response;
    }

    /**
     * Check if domain is located in the Cloud
     *
     * @param string $domain
     * @return int
     */
    private function isCloud($domain)
    {
        $domain = str_replace(array('http://', 'https://'), '', $domain);
        $net = new waNet(array('format' => waNet::FORMAT_RAW));
        $is_cloud = 0;
        try {
            $response = $net->query('http://' . $domain . '/wa-apps/hosting/css/hosting.css');
            $headers = $net->getResponseHeader('http_code');
            if ($headers === 200) {
                $is_cloud = 1;
            }
        } catch (waException $e) {
            $response = json_decode($e->getMessage(), true);
        }
        return $is_cloud;
    }

    /**
     * Check product license for domain
     *
     * @param string $domain
     * @param string|null $product
     * @param bool $cloud_check
     * @param int|null $return_on_result
     * @return array
     */
    private function getDomainLicense($domain, $product = null, $cloud_check = true, $return_on_result = null)
    {
        $products = wao(new helpdeskProPluginHelper())->getProducts();
        $domain = trim($domain);
        if (substr($domain, -1) == '/') {
            $domain = substr($domain, 0, -1);
        }
        $params = array(
            'action' => 'check/?domain=' . $domain,
        );
        if ($product && isset($products[$product])) {
            $params['action'] .= '&product=' . $product;
        }

        $api_keys = helpdeskProPluginHelper::getSettings('developer_key', 0);
        if (!$api_keys) {
            $this->errors = _wp('API key not found');
            return array();
        }

        $data = [];
        $api_keys = explode(PHP_EOL, $api_keys);

        // Если необходимо вернуть результат запроса конкретного API ключа
        if ($return_on_result !== null) {
            $api_keys = [$api_keys[$return_on_result]];
        }

        foreach ($api_keys as $k => $api_key) {
            $data['developer'][$k] = $this->getData($api_key, $params);

            // Если проверка не прошла, и у домена был указан протокол, то попробуем опустить его
            if (empty($data['developer'][$k]['data']) && strpos($domain, 'http') !== false) {
                $domain = str_replace(array('http://', 'https://'), '', $domain);
                $data['developer'][$k] = $this->getDomainLicense($domain, $product, true, $k);
            }
            if ($return_on_result !== null) {
                return $data['developer'][$k];
            }
        }

        if ($cloud_check) {
            // Проверяем домен в Облаке или нет
            $data['is_cloud'] = $this->isCloud($domain);
        }

        return $data;
    }

    /**
     * Get order information
     *
     * @param int $order_id
     * @return array
     */
    private function getOrderInfo($order_id)
    {
        $params = array(
            'action' => 'order/?id=' . $order_id,
        );

        $api_keys = helpdeskProPluginHelper::getSettings('developer_key', 0);
        if (!$api_keys) {
            $this->errors = _wp('API key not found');
            return array();
        }

        $data = [];
        $api_keys = explode(PHP_EOL, $api_keys);

        foreach ($api_keys as $k => $api_key) {
            $data[$k] = $this->getData($api_key, $params);
        }
        return $data;
    }

}
