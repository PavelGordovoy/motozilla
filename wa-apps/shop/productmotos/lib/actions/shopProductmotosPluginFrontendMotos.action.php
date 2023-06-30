<?php

class shopProductmotosPluginFrontendMotosAction extends shopFrontendAction
{
    public function execute()
    {
        if ($t = wa()->getSetting('template_motos', '', array('shop', 'productmotos'))) {
            $template = 'string:'.$t;
        } else {
            $template = 'file:'.wa()->getAppPath('plugins/productmotos/templates/', 'shop').'frontendMotos.html';
        }

        $motos = shopProductmotosPlugin::getMotos();
        $this->view->assign('motos', $motos);

        $plugin = wa('shop')->getPlugin('productmotos');

        $title = $plugin->getSettings('motos_name');
        if (!$title) {
            $title = _w('Motos');
        }

        $this->setThemeTemplate('page.html');

        $this->view->assign('page', array(
            'id' => 'motos',
            'title' => $title,
            'name' => $title,
            'content' => $this->view->fetch($template)
        ));

        $this->getResponse()->setTitle($title);

        if ($tmp = $plugin->getSettings('motos_meta_description')) {
            $this->getResponse()->setMeta('description', $tmp);
        }
        if ($tmp = $plugin->getSettings('motos_meta_keywords')) {
            $this->getResponse()->setMeta('keywords', $tmp);
        }

        waSystem::popActivePlugin();
    }
}