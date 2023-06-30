<?php

class mylangPhotosFrontend_collectionHandler extends waEventHandler
{
    public function execute(&$params) //null
    {
        if (!mylangHelper::checkSite()) {
            return;
        }
        $view = wa()->getView();
        $album = $view->getVars('album');
        $id = ifset($albumm, 'id', false);
        if ($id) {
            $translate = new mylangTranslate();
            $strings = $translate->translate($id, mylangLocale::currentLocale(), 'album', 'photos');
            foreach ($strings as $string) {
                $album[$string['subtype']] = $string['text'];
            }
            $view->assign('album', $album);
        }
        waLocale::setStrings(mylangViewHelper::customStrings());
    }
}
