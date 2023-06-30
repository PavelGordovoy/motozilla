<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformSettingsSaveController extends waJsonController
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin('multiform')) {
            throw new waRightsException('Access denied');
        }

        $settings = waRequest::post('settings', array());

        // Сохраняем данные
        if ($settings) {
            foreach ($settings as $key => $value) {
                (new waAppSettingsModel())->set('multiform', $key, $value);
            }
        }
    }

}
