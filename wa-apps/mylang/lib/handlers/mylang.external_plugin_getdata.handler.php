<?php

class mylangMylangExternal_plugin_getdataHandler extends waEventHandler
{

    /**
     * @param array{app_id:string, type:string, ids:int|array_int, format:bool}: $params
     * @return array
     * @throws waException
     */
    public function execute(&$params)
    {
        static $translate;
        if (!$translate) {
            $translate = new mylangTranslate();
        }

        $env = wa()->getEnv();
        if ($env === 'frontend') {
            if (!mylangHelper::checkSite()) {
                return [];
            }
            $params['locale'] = mylangLocale::currentLocale();
        }

        $data = $translate->translate(
            ifset($params, 'ids', null),
            ifset($params, 'locale', null),
            $params['type'],
            ifset($params, 'appId', null)
        );
        if ($params['format'] && !empty($data)) {
            $result = [];
            foreach ($data as $entry) {
                if (array_key_exists('locale', $params)) {
                    $result[$entry['type_id']][$entry['subtype']] = $entry['text'];
                } else {
                    $result[$entry['type_id']][$entry['subtype']][$entry['locale']] = $entry['text'];
                }
            }
            $data = $result;
        }
        return $data;
    }
}
