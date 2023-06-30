<?php

class mylangConfig extends waAppConfig
{
    /**
     * wa('mylang')->getConfig()->getProviders();
     * @param bool $short
     * @return array
     * @throws waException
     */
    public function getProviders($short = false)
    {
        $plugins = [];
        foreach (wa('mylang')->getConfig()->getPlugins() as $id => $plugin) {
            if (isset($plugin['translation_api'])) {
                $plugins[$id] = $plugin;
            }
        }
        if ($short) {
            $plugins = array_column($plugins, 'name', 'id');
        }
        return $plugins;
    }

    /**
     * @param array $options
     * @return mylangPlugin|waPlugin
     * @throws waDbException
     * @throws waException
     */
    public function getProvider($options = [])
    {
        $plugin_id = ifset($options, 'provider', false);
        if (!$plugin_id) {
            $plugin_id = wa('mylang')->getSetting('main_provider', false, 'mylang');
        }
        return wa('mylang')->getPlugin($plugin_id)->getTranslate($options);
    }

    //TODO
    public function explainLogs($logs)
    {
        $logs = parent::explainLogs($logs);
        $product_ids = array();
        foreach ($logs as $l_id => $l) {
            if (in_array($l['action'], array('product_add', 'product_edit')) && $l['params']) {
                $product_ids[] = $l['params'];
            }
        }
        if ($product_ids) {
            $product_model = new shopProductModel();
            $products = $product_model->getById($product_ids);
        }
        $app_url = wa()->getConfig()->getBackendUrl(true) . $l['app_id'] . '/';
        foreach ($logs as $l_id => $l) {
            if (in_array($l['action'], array('product_add', 'product_edit'))) {
                if (isset($products[$l['params']])) {
                    $p = $products[$l['params']];
                    $url = $app_url . '?action=products#/product/' . $l['params'] . '/';
                    $logs[$l_id]['params_html'] = '<div class="activity-target"><a href="' . $url . '">' . htmlspecialchars(
                            $p['name']
                        ) . '</a></div>';
                    if ($l['action'] == 'product_add' && !empty($p['image_id'])) {
                        $_is_2x_enabled = wa('shop')->getConfig()->getOption('enable_2x');
                        $_image_size = '96x96';
                        if ($_is_2x_enabled) {
                            $_image_size = '96x96@2x';
                        }
                        $img = shopImage::getUrl(
                            array(
                                'id' => $p['image_id'],
                                'product_id' => $p['id'],
                                'filename' => $p['image_filename'],
                                'ext' => $p['ext']
                            ),
                            $_image_size
                        );
                        $logs[$l_id]['params_html'] .= '<div class="activity-photo-wrapper">
                            <div class="activity-photo-list"><div class="photo-item"><a href="' . $url . '">
                            <img src="' . $img . '"></a></div></div></div>';
                    }
                }
            } elseif (substr($l['action'], 0, 6) == 'order_') {
                $url = $app_url . '#/order/' . $l['params'] . '/';
                $logs[$l_id]['params_html'] = '<div class="activity-target"><a href="' . $url . '">' .
                    shopHelper::encodeOrderId($l['params']) . '</a></div>';
            } elseif (in_array($l['action'], array('page_add', 'page_edit', 'page_move'))) {
                if (!empty($l['params_html'])) {
                    $logs[$l_id]['params_html'] = str_replace(
                        '#/pages/',
                        '?action=storefronts#/pages/',
                        $l['params_html']
                    );
                }
            }
        }
        return $logs;
    }
}