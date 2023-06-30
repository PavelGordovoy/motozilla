<?php

class mini
{
    public static function net($url, $mobile = false, $cache = false)
    {
        if ($cache) {
            $path = wa()->getDataPath('cache', true, 'mini') . '/' . hash('adler32', $url);
            if (file_exists($path)) {
                return file_get_contents($path);
            }
        }

        static $net;
        if (!$net) {
            $options = [
                'verify' => false,
                'timeout' => 40,
            ];
            $net = new waNet($options);
        }
        if ($mobile) {
            $net->userAgent(miniHtml::MOBILE_UA);
        } else {
            $net->userAgent(miniHtml::DESKTOP_UA);
        }
        $result = $net->query($url);

        if ($cache && $result) {
            file_put_contents($path, $result);
        }
        return $result;
    }

    public static function addPreload($link, $html = false)
    {
        $extension = pathinfo(parse_url($link, PHP_URL_PATH), PATHINFO_EXTENSION);
        switch ($extension) {
            case 'css':
                $as = $as_html = 'style';
                break;
            case 'js':
                $as = $as_html = 'script';
                break;
            case 'ttf':
            case 'woff':
            case 'woff2':
                $as = 'font; crossorigin=anonymous';
                $as_html = 'font\' crossorigin=\'anonymous';
                break;
            default:
                return $html;
        }
        if (self::settings('preload') == '2') {
            $html = str_replace('</title>', "</title> <link rel='preload' href='$link' as='$as_html'>", $html);
        }
        if (self::settings('preload') == '1') {
            header("Link: <$link>; rel=preload; as=$as", false);
        }
        return $html;
    }

    public static function settings($name = null)
    {
        static $settings;

        if ($settings === null) {
            $model = new waAppSettingsModel();
            $settings = $model->get('mini');
            foreach ($settings as $key => $value) {
                #decode non string values
                if (!is_numeric($value)) {
                    $json = json_decode($value, true);
                    if (is_array($json)) {
                        $settings[$key] = $json;
                    }
                }
            }
            #merge user settings from database with raw default settings
            $settings_config = include wa()->getAppPath('lib/config/settings.php', 'mini');
            if ($settings_config) {
                foreach ($settings_config as $key => $row) {
                    if (!isset($settings[$key])) {
                        $settings[$key] = is_array($row) ? (isset($row['value']) ? $row['value'] : null) : $row;
                    }
                }
            }
        }
        if ($name === null) {
            return $settings;
        } else {
            return isset($settings[$name]) ? $settings[$name] : null;
        }
    }

    public static function postProcess($code, $file, $type)
    {
        $r = $code;
        if ($type === 'css') {
            if (self::settings('font_swap')) {
                $r = str_replace('@font-face{font-family', '@font-face{font-display:swap;font-family', $code);
            }
            if (self::settings('font_cache')) {
                $r = preg_replace_callback('/url\((.*?)\)/',
                    function ($matches) {
                        $extension = pathinfo(parse_url($matches[1], PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (in_array($extension, ['ttf', 'eot', 'woff', 'woff2']) && filter_var($matches[1], FILTER_VALIDATE_URL) !== false) {

                            $path = wa()->getDataPath('font', true, 'mini');
                            $url = wa()->getDataUrl('font', true, 'mini');
                            $hash = hash('adler32', $matches[1] . mini::salt());
                            $file = $path . '/' . $hash . ".$extension";
                            if (!file_exists($file)) {
                                $temp = self::net($matches[1]);
                                file_put_contents($file, $temp);
                                waLog::log('Saved and compressed: "' . $matches[1] . '" to ' . basename($file), 'mini.log');
                            }
                            return "url($url/$hash.$extension)";
                        }
                        return "url($matches[1])";
                    }, $r);
            }
            if ($code !== $r) {
                if ($file) {
                    file_put_contents($file, $r);
                    waLog::log('Fonts saved to: ' . basename($file), 'mini.log');
                } else {
                    return $r;
                }
            } else {
                return $code;
            }
        }
        return $r;
    }

    public static function getWarnings()
    {
        $result = '';
        if (waSystemConfig::isDebug()) {
            $result .= '<b> В Настройках включен режим отладки, оптимизация отключена</b>  <br><br>';
        }
        if (!extension_loaded('zip')) {
            $result .= 'Расширение PHP ZIP не подключено, оптимизация изображений работать не будет<br><br>';
        }
        if (!@ini_get('allow_url_fopen')) {
            $result .= 'Директива allow_url_fopen на хостинге выключена, могут быть проблемы при работе приложения<br><br>';
        }
        return $result;
    }

    public static function checkAccess()
    {
        return wa()->getUser()->getRights('mini', 'backend');
    }

    public static function salt($new = false)
    {
        if ($new) {
            $salt = uniqid();
            $model = new waAppSettingsModel();
            $model->set('mini', 'salt', $salt);
            return $salt;
        }
        return self::settings('salt');
    }

    public static function getStats()
    {
        $path = wa()->getDataPath(null, true, 'mini');
        $files = waFiles::listdir($path, true);
        return 'Всего файлов: ' . count($files);
    }
}