<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformBackendLayout extends waLayout
{

    public function execute()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Expires: " . date("r"));

        // Подключаем JS файлы
        $module = waRequest::get("module", "backend");
        if (file_exists(wa()->getAppPath('js/' . $module . '/' . $module . '.js'))) {
            $this->getResponse()->addJs('js/' . $module . '/' . $module . '.js', 'multiform');
        }

        $domain = wa('multiform')->getConfig()->getDomain();
        // Проверяем существует ли поселение
        $route_url = wa()->getRouteUrl("multiform/frontend", array("domain" => $domain), true);

        // JS Локализция
        $js_locale_path = wa()->getAppPath('locale/' . wa()->getLocale() . '/LC_MESSAGES/multiform_js_backend.json', 'multiform');
        $js_locale_strings = "{}";
        if (file_exists($js_locale_path)) {
            $js_locale_strings = file_get_contents($js_locale_path);
        }

        $this->assign('route_url', $route_url);
        $this->assign('domain', $domain);
        $this->assign('domain_hash', self::domainHash($domain));
        $this->assign('module', $module);
        $this->assign('js_locale_strings', $js_locale_strings);
        $this->assign('lang', substr(wa()->getLocale(), 0, 2));
    }

    private static function domainHash($d)
    {
        $a = 1;
        $c = 0;
        if ($d) {
            $a = 0;
            for ($h = strlen($d) - 1; $h >= 0; $h--) {
                $o = ord($d[$h]);
                $a = (($a << 6) & 268435455) + $o + ($o << 14);
                $c = $a & 266338304;
                $a = ( $c != 0 ) ? $a ^ ($c >> 21) : $a;
            }
        }
        return $a;
    }

}
