<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFrontController extends waFrontController
{

    public function execute($plugin = null, $module = null, $action = null, $default = false)
    {
        $this->registerAutoload();
        if ($module == 'frontend') {
            try {
                parent::execute(null, 'frontend', $action);
            } catch (Exception $e) {
                multiformHelper::log($e->getMessage());
                parent::execute(null, 'frontend', 'error');
            }
        } else {
            parent::execute($plugin, $module, $action, $default);
        }
    }

    private function registerAutoload()
    {
        waAutoload::getInstance()->add('Igaponov\Hash', 'wa-apps/multiform/lib/classes/String.php');
    }

}
