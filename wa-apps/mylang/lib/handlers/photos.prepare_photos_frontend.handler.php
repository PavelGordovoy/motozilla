<?php

class mylangPhotosPrepare_photos_frontendHandler extends waEventHandler
{
    public function execute(&$params)
    {
        if (!mylangHelper::checkSite()) {
            return;
        }
        $ids = array_column($params, 'id');
        $translate = new mylangTranslate();
        $strings = $translate->translate($ids, mylangLocale::currentLocale(), 'photo', 'photos');
        $photos = [];
        foreach ($strings as $string) {
            $photos[$string['type_id']][$string['subtype']] = $string['text'];
        }
        foreach ($params as &$photo) {
            if (isset($photos[$photo['id']])) {
                $photo = array_replace_recursive($photo, $photos[$photo['id']]);
            }
        }
        return true;
        //TODO tags
    }
}
