<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 11.04.2020
 * Time: 17:34
 */

class mylangTutorialCheck extends waJsonActions
{
    private $result;

    public function checkAction(): void
    {
        $this->checkRedactor();

        $this->response = $this->result;
    }

    private function checkRedactor()
    {
        $locales = mylangLocale::getAll();
        $target_path = waConfig::get('wa_path_root').'/wa-content/js/redactor/2/';
        $ru_file = $target_path.'ru.js';
        foreach ($locales as $l) {
            $wa_lang = substr($l, 0, 2);
            $js_file = $target_path.$wa_lang.'.js';
            if (!file_exists($js_file)) {
                if (in_array($wa_lang, ['uk', 'by'])) {
                    waFiles::copy($ru_file, $js_file);
                    $this->result['redactor'][] = $wa_lang.' '._w('fixed');
                } else {

                }
            }
        }
    }
}