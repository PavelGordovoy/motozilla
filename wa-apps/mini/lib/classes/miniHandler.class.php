<?php

class miniHandler extends waEventHandler
{

    public function execute(&$params = null, $array_keys = null)
    {
        if (waRequest::param('mini_init') || !$this->checkEnabled()) {
            return;
        }
        if (mini::settings('html') > 0) {
            try {
                wa()->getView()->smarty->loadFilter('output', 'trimwhitespace');
            } catch (SmartyException $e) {
            }
        }
        wa()->getView()->smarty->registerFilter('output', [$this, 'output']);
        waRequest::setParam('mini_init', true);
    }

    public function checkEnabled()
    {
        $settings = mini::settings();
        if ($settings['enabled'] == '3'
            || ($settings['enabled'] == '2' && (null === waRequest::cookie('landing')))
            || ($settings['enabled'] == '1' && mini::checkAccess())) {
            if (wa()->getEnv() === 'frontend'
                && !waSystemConfig::isDebug()
                && !waRequest::get('nomini')
                && !stripos(waRequest::server('HTTP_USER_AGENT'), 'wamini')
                && waRequest::getMethod() === 'get'
                && !waRequest::isXMLHttpRequest()) {
                return true;
            }
        }
        return false;
    }

    public function output($source)
    {
        if (false === strpos($source, '</body>')) {
            return $source;
        }

        $compile = new miniHtml($source);
        return $compile->output();
    }
}