<?php

class mylangShopjsActions extends waJsonActions
{

    public function execute($action)
    {
        $checkout = wa()->getConfig()->getConfigPath('checkout.php', true, 'shop');
        $action = waRequest::post('action');
        if ($action && file_exists($checkout) && method_exists($this, $action)) {
            $data = include $checkout;
            $result = $this->$action($data);
            waUtils::varExportToFile($result, $checkout);
        }
    }

    private function checkoutFixsteps($data)
    {
        foreach ($data as &$step) {
            if (isset($step['name'])) {
                unset($step['name']);
                if (empty($step)) {
                    $step = true;
                }
            }
        }
        return $data;
    }

    private function checkoutFixfields($data)
    {
        if (isset($data['contactinfo']['fields'])) {
            foreach ($data['contactinfo']['fields'] as &$field) {
                if (isset($field['localized_names'])) {
                    unset($field['localized_names']);
                    //addresses
                    if (isset($field['fields'])) {
                        foreach ($field['fields'] as &$subfield) {
                            if (isset($subfield['localized_names'])) {
                                unset($subfield['localized_names']);
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }
}
