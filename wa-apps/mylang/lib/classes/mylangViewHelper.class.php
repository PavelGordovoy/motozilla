<?php

class mylangViewHelper extends waAppViewHelper
{

    public static function selector6()
    {
        $path = wa('mylang')->getAppPath('/templates/actions/settings/selector6.html');
        $view = wa()->getView();
        return $view->fetch($path);
    }

    public static function selector2()
    {
        //DEV
        $locale = mylangLocale::currentLocale();
        $locales = mylangHelper::getSettings('locales');
        $html = "<select id='mylang-select'><option value='auto'>Auto</option>";
        foreach ($locales as $l) {
            $html .= "<option value='" . $l . "'>" . $l . "</option>";
        }
        $html .= <<<HTML
        </select>
        <script>
        document.getElementById("mylang-select").value = "{$locale}";
        if(document.getElementById("mylang-select").value.length==0) document.getElementById("mylang-select").value = "auto";
        document.getElementById("mylang-select").onchange = function(event) {
            if(event.target.value=='auto') document.cookie = "mylang=; expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
            else document.cookie = "mylang="+this.value+";path=/";
            location.reload();
        }
        </script>
HTML;
        return $html;
    }

    public static function selector3()
    {
        //DEV
        $locale = mylangLocale::currentLocale();
        $locales = mylangHelper::getSettings('locales');
        $rm = new mylangRules();
        $domain_id = wa()->getRouting()->getDomain();
        $rules = $rm->get($domain_id);
        $root = wa()->getRootUrl(true);
        $cm = new waCountryModel();
        $countries = $cm->all(null, null, $locale);
        $path = wa('mylang')->getAppPath('/templates/actions/settings/selector3.html');
        $view = wa()->getView();
        $view->assign(compact('countries', 'locales', 'locale', 'rules', 'root'));
        return $view->fetch($path);
    }

    /**
     * @param int $flags
     * @return string
     * @throws SmartyException
     * @throws waException
     */
    public static function selector($flags): string
    {
        /* 0 - flags
        *  1 - text view
        *  2 - select
        *  3 - select with flags
        *  4 - text view full size dialog
        *
        */
        $locales = mylangHelper::getSettings('locales');
        $root = wa()->getRootUrl(true);
        $static = wa()->getView()->getVars('wa_static_url');
        $locInfo = waLocale::getAll(true);
        $selector_html = '';
        $fixIso3 = [];

        if ($flags === 0) { //most basic
            foreach ($locales as $locale) {
                if (!isset($locInfo[$locale]['iso3'])) {
                    $fixIso3[] = $locale;
                }
                $flag = '<img class="mylang-flag" alt="'.ifset($locInfo[$locale]['iso3'], '-').'" src="'.$static.'wa-apps/mylang/img/country/'.ifset($locInfo[$locale]['iso3'], '-').'.png" width="32px" height="32px">';
                $flag = '<a href="?locale='.$locale.'" title="'.$locInfo[$locale]['name'].'" rel="alternate">'.$flag.'</a>';
                $selector_html .= $flag;
            }
            if ($fixIso3) {
                mylangLocale::fixIso3($fixIso3);
            }
            return $selector_html;
        }

        if ($flags === 4) {
            $elements = [];
            foreach ($locales as $locale) {
                $short_code = explode('_', $locale);
                $short_code = ifset($short_code, 1, $short_code);
                $elements[] = '<a href="?locale='.$locale.'" title="'.$locInfo[$locale]['name'].'" rel="alternate">'.strtoupper($short_code).'</a>';
            }
            $selector_html = implode(' | ', $elements);
            return $selector_html;
        }


        //$selector_type = mylangHelper::getSettings('selector_type', 1);
        $locale = mylangLocale::currentLocale();
        $path = wa('mylang')->getAppPath('/templates/actions/settings/selector.html');
        $view = wa()->getView();
        $view->assign(compact('locales', 'locale', 'root', 'static'));
        return $view->fetch($path);
    }

    public static function selectorFlags()
    {
        return self::selector(0);
    }


    /**
     * @param array $categories
     * @param null $locale
     * @return array
     * @throws waException
     */
    public static function categories($categories = [], $locale=null)
    {
        if (!mylangHelper::checkSite()) {
            return $categories;
        }
        //TODO медленная функция
        if (empty($categories)) {
            return $categories;
        }

        $ids = [];
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($categories));
        foreach ($it as $k => $v) {
            if ($k ==='id') {
                $ids[] = $v;
            }
        }

        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }

        $cache = new mylangCache();
        $cache_key = $locale.'_'.md5(implode('_', (array)$ids));
        $translated = $cache->get($cache_key, 'mylang_view_categories');
        if ($translated === null) {
            $translated = $model->getValues('category', $ids, $locale);
            $cache->set($cache_key, $translated, 3600, 'mylang_view_categories');
        }

        mylangHelper::iterate_recursive($categories, $ids, $translated);
        return $categories;
    }

    public static function translates($param, $ids = [], $locale)
    {
        $ids = array_keys($ids);
        $translate = new mylangTranslate();
        if($param == "categories"){
            $translated_fields = $translate->category($ids, $locale);
        }
        if($param == "products"){
            $translated_fields = $translate->productsIds($ids, $locale);
        }

        return $translated_fields;
    }

    /**
     * @param array $tags
     * @param null $locale
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function tags($tags = [], $locale = null)
    {
        if (!mylangHelper::checkSite()) {
            return $tags;
        }
        //TODO make with class
        if (empty($tags)) {
            return $tags;
        }
        $ids = [];
        foreach ($tags as $tag) {
            $ids[] = $tag['id'];
        }
        $model = new mylangModel();
        if (!$locale) {
            $locale = mylangLocale::currentLocale();
        }

        $cache = new mylangCache();
        $cache_key = $locale.'_'.md5(implode('_', (array)$ids));
        $translated = $cache->get($cache_key, 'mylang_view_tags');
        if ($translated === null) {
            $translated = $model->getValues('tag', $ids, $locale);
            $cache->set($cache_key, $translated, 3600, 'mylang_view_tags');
        }

        foreach ($tags as &$tag) {
            if (!empty($translated[$tag['id']])) {
                $tag['name'] = $translated[$tag['id']]['text'];
            }
        }
        return $tags;
    }

    /**
     * @param array $stocks
     * @return array
     * @throws waException
     */
    public static function stocks($stocks = [])
    {
        if (!mylangHelper::checkSite()) {
            return $stocks;
        }
        $translate = new mylangTranslate();
        return $translate::stocks($stocks);
    }

    /**
     * @param array $features
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public static function features($features = [])
    {
        if (!mylangHelper::checkSite()) {
            return $features;
        }
        $translate = new mylangTranslate();
        return $translate::features($features);
    }

    /**
     * @param $products
     * @return mixed
     * @throws waException
     */
    public static function products($products)
    {
        if (!mylangHelper::checkSite()) {
            return $products;
        }
        $translate = new mylangTranslate();
        return $translate->products($products);
    }

    /**
     * @param $pages
     * @return array|void
     * @depecated since 2.9
     * TODO Remove in 3.0
     */
    public static function filterPages(&$pages)
    {
        return mylangEventHandler::pages($pages);
    }

    /**
     * @param $promocards
     * @return array
     * @throws waException
     */
    public static function promos($promocards)
    {
        if (empty($promocards) || (!is_array($promocards))) {
            return $promocards;
        }
        $translate = new mylangTranslate();
        $loc_info = $translate->promos(array_keys($promocards), mylangLocale::currentLocale());
        foreach ($loc_info as $loc) {
            $promocards[$loc['type_id']][$loc['subtype']] = $loc['text'];
        }
        return $promocards;
    }

    //Not used. Save to theme.xml.
    public static function theme_settings()
    {
        $view = wa()->getView();
        $settings = $view->getVars('theme_settings');
        $model = new mylangModel();
        $newsettings = $model->getTheme(waRequest::param('theme'), mylangLocale::currentLocale());
        if ($newsettings) {
            $settings = $newsettings+$settings;
            $view->assign('theme_settings', $settings);
        }
        return $settings;
    }

    /**
     * TODO Deprecate 3.0.0
     * @return array|mixed|null
     * @throws waException
     */
    public static function customStrings()
    {
        $allMessages = waConfig::get('mylang_custom_messages');
        if ($allMessages) {
            return [];
        }
        $theme = waRequest::param('theme');
        $app = waRequest::param('app');

        $theme = new waTheme($theme, $app, null, true);
        $theme_locales = $theme->getLocales();
        if ($parent_theme = $theme->parent_theme) {
            $theme_locales += $parent_theme->getLocales();
        }
        $locale = mylangLocale::currentLocale();
        $locale_path = waConfig::get('wa_path_root').'/wa-data/protected/mylang/mylang_custom/locale';
        $domain = 'mylang_custom';
        $adapter = new mylangLocalePHPAdapter();
        $adapter->load($locale, $locale_path, $domain);
        $messages = $adapter->getMessages();
        $allMessages = $theme_locales+$messages;
        waConfig::set('mylang_custom_messages', $allMessages);
        return $allMessages;
    }

    public static function localeFilter()
    {
        waLocale::setStrings(self::customStrings());
    }

    public static function providers()
    {
        return wa('mylang')->getConfig()->getProviders(true);
    }
}
