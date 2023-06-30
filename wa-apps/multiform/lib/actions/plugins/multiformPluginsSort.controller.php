<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformPluginsSortController extends waJsonController
{

    public function execute()
    {
        if (!$this->getUser()->isAdmin($this->getApp())) {
            throw new waRightsException(_w("Permission denied"));
        }
        try {
            $this->getConfig()->setPluginSort(waRequest::post('slug'), waRequest::post('pos', 0, 'int'));
            $this->response = 'ok';
        } catch (waException $e) {
            $this->setError($e->getMessage());
        }
        $this->getResponse()->addHeader('Content-type', 'application/json');
    }

}
