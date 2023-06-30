<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormPageNotificationsAction extends waViewAction
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

        $sms_adapters = $this->getSMSAdapters();
        $this->view->assign('sms_adapters', $sms_adapters);

        $this->view->assign('id', $id);
        $this->view->assign('form', $form_settings);
        $this->view->assign('sms_from', $this->getSmsFrom());
        $this->view->assign('email_fields', $email_fields);
        $this->view->assign('lang', substr(wa()->getLocale(), 0, 2));
    }

    protected function getSmsFrom()
    {
        $sms_config = wa()->getConfig()->getConfigFile('sms');
        $sms_from = array();
        foreach ($sms_config as $from => $options) {
            $sms_from[$from] = $from . ' (' . $options['adapter'] . ')';
        }
        return $sms_from;
    }

    protected function getSMSAdapters()
    {
        $path = $this->getConfig()->getPath('plugins') . '/sms/';
        if (!file_exists($path)) {
            return array();
        }
        $dh = opendir($path);
        $adapters = array();
        while (($f = readdir($dh)) !== false) {
            if ($f === '.' || $f === '..' || !is_dir($path . $f)) {
                continue;
            } elseif (file_exists($path . $f . '/lib/' . $f . 'SMS.class.php')) {
                require_once($path . $f . '/lib/' . $f . 'SMS.class.php');
                $class_name = $f . 'SMS';
                $adapters[$f] = new $class_name(array());
            }
        }
        closedir($dh);
        $result = array();

        $config = wa()->getConfig()->getConfigFile('sms');

        $used = array();
        foreach ($config as $c_from => $c) {
            if (isset($adapters[$c['adapter']])) {
                $used[$c['adapter']] = 1;
                if (!isset($result[$c['adapter']])) {
                    $temp = $this->getSMSAdapaterInfo($adapters[$c['adapter']]);
                    $temp['config'] = $c;
                    $temp['config']['from'] = array($c_from);
                    $result[$c['adapter']] = $temp;
                } else {
                    $result[$c['adapter']]['config']['from'][] = $c_from;
                }
            }
        }
        $result = array_values($result);

        foreach ($adapters as $a) {
            /**
             * @var waSMSAdapter $a
             */
            if (!empty($used[$a->getId()])) {
                continue;
            }
            $result[] = $this->getSMSAdapaterInfo($a);
        }
        return $result;
    }

    protected function getSMSAdapaterInfo(waSMSAdapter $a)
    {
        $temp = $a->getInfo();
        $temp['id'] = $a->getId();
        $temp['controls'] = $a->getControls();
        return $temp;
    }

}
