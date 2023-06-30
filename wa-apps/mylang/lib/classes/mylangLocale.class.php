<?php

class mylangLocale
{
    private $locales;
    private $path;

    public function __construct()
    {
        $this->locales = waLocale::getAll();
        $this->path = wa()->getConfig()->getPath('config', 'locale');
    }

    /**
     * @return array|mixed|null
     */
    public static function getActive()
    {
        $locales = (array)mylangHelper::getSettings('locales');
        $main = mylangHelper::getSettings('main_language');
        if (!empty($main)) {
            unset($locales[$main]);
        }
        $locales = array_intersect_key(self::getAll(true), $locales);
        return $locales;
    }

    public static function getAll($extra = false)
    {
        $locales = $extra ? waLocale::getAll('name') : waLocale::getAll();
        $main = mylangHelper::getSettings('main_language');
        if (!empty($main)) {
            unset($locales[$main]);
        }
        return $locales;
    }

    /**
     * @return mixed|string|null
     * @throws waException
     */
    public static function currentLocale()
    {
        if (waConfig::get('mylang_locale')) {
            return waConfig::get('mylang_locale');
        }
        //fast
        $locale = wa()->getStorage()->get('locale');
        if (!$locale) {
            $locale = waRequest::param('locale', false); //локаль витрины
        }
        if (!$locale) {
            $locale = waRequest::cookie('mylang'); //Если стоит Авто - выбранная пользователем
        }
        if (!$locale) {
            //$locale = waRequest::getLocale(); //Если пользователь не выбирал определяем по браузеру
            $locale = wa()->getLocale();
        }
        waConfig::add(['mylang_locale' => $locale]);
        return $locale;
    }

    /**
     * @param $locale
     * @return bool
     * @throws Exception
     */
    public static function fixDatepicker($locale)
    {
        if (!preg_match('/^([a-zA-Z_]+)$/', $locale, $matches) || in_array($locale, ['en_US', 'ru_RU'], true)) {
            return false;
        }
        $source = wa()->getAppPath('js/vendors/datepicker', 'mylang');
        $target = waConfig::get('wa_path_content').'/js/jquery-ui/i18n';
        $file = 'jquery.ui.datepicker-'.$locale.'.js';
        if (file_exists($source.'/'.$file)) {
            waFiles::copy($source.'/'.$file, $target.'/'.$file);
            waLog::log($locale.'- Datepicker fixed.', 'mylang.log');
        }
        return false;
    }

    /**
     * @param string $locale
     * @param array $localedata
     * @return array|null
     * @throws waException
     */
    public function add($locale, $localedata)
    {
        if (in_array($locale, $this->locales, false)) {
            return $this->locales;
        }
        $files = waFiles::listdir(wa()->getConfig()->getPath('system').'/locale/data/');
        foreach ($files as $file) {
            if (preg_match('/^([a-zA-Z_]+)\.php$/', $file, $matches)) {
                $internal[] = $matches[1];
            }
        }
        if (isset($internal) && !in_array($locale, $internal, false) && !$this->create($locale, $localedata)) {
            return $this->locales;
        }
        $this->locales[] = $locale;
        waUtils::varExportToFile($this->locales, $this->path);
        return $this->locales;
    }

    public function delete($locale, $force = false)
    {
        if ($key = array_search($locale, $this->locales)) {
            unset($this->locales[$key]);
        }
        waUtils::varExportToFile($this->locales, $this->path);
        if ($force) {
            $path = wa()->getConfig()->getPath('system').'/locale/data/'.$locale.'.php';
            //can't touch system
            if (!in_array($locale, ['ru_RU', 'en_US'])) {
                waFiles::delete($path);
            }
        }
        return $this->locales;
    }

    /**
     * @param $locale
     * @param array $localedata
     * @return bool
     * @throws waException
     */
    private function create($locale, $localedata = [])
    {
        if (!preg_match('/^([a-zA-Z_]+)$/', $locale, $matches)) {
            return false;
        }
        $data = [
            'name' => $locale,
            'region' => $locale,
            'english_name' => $locale,
            'english_region' => $locale,
            'decimal_point' => '.',
            'frac_digits' => '2',
            'thousands_sep' => ',',
        ];

        $langs = wa('mylang')->getAppPath('lib/config/data/isolangs.php');
        if (file_exists($langs)) {
            /** @define "$langs" "$PROJECT_DIR/fw/wa-apps/mylang/lib/config/data/isolangs.php" */
            $langs = include $langs;
            $code = substr($locale, 0, 2);
            self::fixRedactor($code);
            self::fixDatepicker($locale);
            if (isset($langs[$code])) {
                $data['name'] = $langs[$code]['name'];
                $data['english_name'] = $langs[$code]['name'];
            }
            $code = substr($locale, 3, 2);
            $model = new waCountryModel();
            $country = $model->getByField('iso2letter', $code);
            if (!empty($country)) {
                $data['iso3'] = $country['iso3letter'];
                $data['english_region'] = $country['name'];
            }
        }
        $localedata += $data;

        $path = wa()->getConfig()->getPath('system').'/locale/data/'.$locale.'.php';
        return waUtils::varExportToFile($localedata, $path);
    }

    /**
     * @param bool $namesonly
     * @return array|bool|mixed
     * @throws waException
     */
    public static function getFullList($namesonly = false)
    {
        if (waConfig::get('is_template')) {
            return false;
        }
        
        $langs_file = wa('mylang')->getAppPath('lib/config/data/plurals.php');
        $langs = [];
        if (file_exists($langs_file)) {
            $langs = include $langs_file;
            uasort($langs, wa_lambda('$a, $b', 'if ($a["name"] == $b["name"]) return 0; return ($a["name"] < $b["name"]) ? -1 : 1;'));
            $array_map = [];
            foreach ($langs as $key => $element) {
                $array_map[$key] = $element['name'];
            }
            $langs = $namesonly ? $array_map : $langs; //Только имена.
        }
        return $langs;
    }

    //setLocale gives false when we set unknown to server locale

    /**
     * @param $locale
     * @return array|bool
     */
    public static function checkServerLocale($locale)
    {
        if (waConfig::get('is_template')) {
            return false;
        }
        
        if (!is_array($locale)) {
            $locale = (array) $locale;
        }
        foreach ($locale as $key => $loc) {
            if (setlocale(LC_ALL, $loc)!==false) {
                continue;
            }
            if (setlocale(LC_ALL, $loc.'.utf8')!==false) {
                continue;
            }
            if (setlocale(LC_ALL, $loc.'.UTF8')!==false) {
                continue;
            }
            unset($locale[$key]);
        }
        return $locale;
    }

    /**
     * @param array $list
     * @throws waException
     */
    public static function fixIso3($list = [])
    {
        if (waConfig::get('is_template')) {
            return;
        }
        
        if (!is_array($list)) {
            $list = [$list];
        }
        $model = new waCountryModel();
        foreach ($list as $locale) {
            $path = wa()->getConfig()->getPath('system').'/locale/data/'.$locale.'.php';
            if (file_exists($path)) {
                $localedata = include $path;
                $code = substr($locale, 3, 2);
                $country = $model->getByField('iso2letter', $code);
                if (!empty($country)) {
                    $localedata['iso3'] = $country['iso3letter'];
                    waUtils::varExportToFile($localedata, $path, true);
                    waLog::log($locale. '-fixed.', 'mylang.log');
                }
            }
        }
    }

    /**
     * @param null $id
     * @return bool
     * @throws Exception
     */
    public static function fixRedactor($id = null)
    {
        if (empty($id) || waConfig::get('is_template')) {
            return false;
        }
        $id = strtolower($id).'.js';
        $orig_path = wa()->getAppPath('js/vendors/redactor/'.$id, 'mylang');
        if (file_exists($orig_path)) {
            $target_path = waConfig::get('wa_path_root');
            $target_path .= '/wa-content/js/redactor/2/'.$id;
            if (!file_exists($target_path)) {
                waFiles::copy($orig_path, $target_path);
                waLog::log($id. '-fixed.', 'mylang.log');
                return true;
            }
        }
        return false;
    }
}
