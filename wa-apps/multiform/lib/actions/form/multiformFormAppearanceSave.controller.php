<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormAppearanceSaveController extends waJsonController
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin() && !wa()->getUser()->getRights('multiform', 'appearance')) {
            throw new waRightsException('Access denied');
        }

        $id = waRequest::post("id", 0, waRequest::TYPE_INT);

        if (!$id) {
            multiformHelper::log(_w("Hey, where is form ID?"));
            $this->errors = 1;
            return;
        }
        
        $theme_name = waRequest::post("name", _w("My theme"));
        $theme_id = waRequest::post("theme_id");
        $important = waRequest::post("important", 0, waRequest::TYPE_INT);
        $theme_settings = waRequest::post("theme_settings", array());

        if ($theme_id) {
            $mt = new multiformThemeModel();
            if ($theme_id == 'new') {
                $theme_name = $theme_name ? $theme_name : _w("My theme");
                $theme_id = $mt->insert(array("name" => $theme_name, "important" => $important, "create_datetime" => date("Y-m-d H:i:s"), "create_contact_id" => wa()->getUser()->getId()));
            } else {
                $theme = $mt->getTheme($theme_id);
                if (wa()->getUser()->getRights("multiform", "appearance") == 1 && $theme['create_contact_id'] !== wa()->getUser()->getId()) {
                    throw new waRightsException('Access denied');
                }
                
                $mt->updateById($theme_id, array("important" => $important, "update_datetime" => date("Y-m-d H:i:s"), "update_contact_id" => wa()->getUser()->getId()));
            }
            $settings = array();
            foreach ($theme_settings as $property => $fields) {
                foreach ($fields as $field => $value) {
                    if (is_array($value)) {
                        foreach ($value as $ext => $v) {
                            if (is_array($v)) {
                                foreach ($v as $ext_k => $v2) {
                                    $settings[] = array("theme_id" => $theme_id, "property" => $property, "field" => $field, "ext" => $ext . ":" . $ext_k, "value" => $v2);
                                }
                            } else {
                                $settings[] = array("theme_id" => $theme_id, "property" => $property, "field" => $field, "ext" => $ext, "value" => $v);
                            }
                        }
                    } else {
                        $settings[] = array("theme_id" => $theme_id, "property" => $property, "field" => $field, "ext" => "", "value" => $value);
                    }
                }
            }


            $mts = new multiformThemeSettingsModel();

            // Удаляем старые настройки
            $mts->deleteByField("theme_id", $theme_id);

            $mts->multipleInsert($settings);

            $this->response = array("name" => multiformHelper::secureString($theme_name), "theme_id" => $theme_id);
        }
    }

}
