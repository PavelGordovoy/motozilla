<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 24.10.2018
 * Time: 22:46
 */
class mylangSitejsActions extends waJsonActions
{
    public function execute($action)
    {
        $action = waRequest::post('action');
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    private function fixauth(){
        $config = $this->getConfig()->getAuth();
        if (!empty($config)) {
            foreach ($config as &$site) {
                if(array_key_exists('fields', $site)) {
                    foreach ($site['fields'] as &$field) {
                        if(isset($field['caption'])) {
                            unset($field['caption']);
                        }
                    }
                }
                if(isset($site['params']['button_caption'])) {
                    unset($site['params']['button_caption']);
                }
            }
        }
        if (!$this->getConfig()->setAuth($config)) {
            $this->errors = sprintf(_w('File could not be saved due to the insufficient file write permissions for the "%s" folder.'), 'wa-config/');
        }
    }
}