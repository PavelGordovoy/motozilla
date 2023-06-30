<?php

class mylangPhotosPrepare_photo_frontendHandler extends waEventHandler
{
    public function execute(&$params) //null
    {
        if (!mylangHelper::checkSite()) {
            return;
        }
        $id = $params['id'];
        $translate = new mylangTranslate();
        $strings = $translate->translate($id, mylangLocale::currentLocale(), 'photo', 'photos');
        $photos = [];
        foreach ($strings as $string) {
            $photos[$string['type_id']][$string['subtype']] = $string['text'];
        }
        if (isset($photos[$id])) {
            $params = array_replace_recursive($params, $photos[$id]);
        }
        return $params;
    }
}
