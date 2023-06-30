<?php

class miniBackendActions extends waActions
{
    private $settings;

    const CRITICAL_API_URL = 'http://wamini.me/minimize';
    const GOOGLE_API_KEY = 'AIzaSyCD09UEi7UbUN9KEadbbMocwAQIJIsjbJM';

    public function defaultAction()
    {
        $params = [
            'id' => 'mini',
            'namespace' => 'mini',
            'title_wrapper' => '%s',
            'description_wrapper' => '<br><span class="hint">%s</span>',
            'control_wrapper' => '<div class="name">%s</div><div class="value">%s %s</div>',
        ];
        $settings_config = include wa()->getAppPath('lib/config/settings.php', 'mini');
        foreach ($settings_config as $name => $row) {
            $row = array_merge($row, $params);
            $row['value'] = mini::settings($name);
            if (!empty($row['control_type'])) {
                $vars['settings_controls'][$name] = waHtmlControl::getControl($row['control_type'], $name, $row);
            }
        }
        $this->display($vars, wa()->getAppPath('templates/Settings.html', 'mini'));
    }

    public function saveAction()
    {
        $model = new waAppSettingsModel();
        $namespace = 'mini';
        $settings = (array)$this->getRequest()->post($namespace);
        try {
            $settings_config = include wa()->getAppPath('lib/config/settings.php', 'mini');
            foreach ($settings_config as $name => $row) {
                if (!isset($settings[$name])) {
                    if ((ifset($row['control_type']) == waHtmlControl::CHECKBOX) && !empty($row['value'])) {
                        $settings[$name] = false;
                    } elseif ((ifset($row['control_type']) == waHtmlControl::GROUPBOX) && !empty($row['value'])) {
                        $settings[$name] = [];
                    } elseif (!empty($row['control_type']) || isset($row['value'])) {
                        $this->settings[$name] = isset($row['value']) ? $row['value'] : null;
                        $model->del($namespace, $name);
                    }
                }
            }
            foreach ($settings as $name => $value) {
                $this->settings[$name] = $value;
                // save to db
                $model->set($namespace, $name, is_array($value) ? json_encode($value) : $value);
            }
            $response['message'] = _w('Saved');
            $this->displayJson($response);
            $this->moveApp('mini');
            mini::salt(true);
        } catch (Exception $e) {
            $this->displayJson([], $e->getMessage());
        }
    }

    public function clearAction()
    {
        $path = wa()->getDataPath('', true, 'mini');
        waFiles::delete($path);
    }

    private function moveApp($app) {
        $apps = wa()->getConfig()->getConfigFile('apps');
        if (isset($apps[$app])) {
            unset($apps[$app]);
            $apps += [$app => true];
            waUtils::varExportToFile($apps, waConfig::get('wa_path_config').'/apps.php');
        }
    }

    public function criticalAction()
    {
        $url = waRequest::get('url');
        $strategy = waRequest::isMobile() ? 'mobile' : 'desktop';
        $net = new waNet([
            'format' => waNet::FORMAT_JSON,
            'timeout' => 60,
        ]);

        try {
            $r = $net->query(self::CRITICAL_API_URL, array(
                'url' => $url,
                'strategy' => $strategy,
            ), waNet::METHOD_POST);
        } catch (waException $e) {
            $this->displayJson($e->getMessage());
            return;
        }

        $css = $r['result']['finalCss'];
        $css = mini::postProcess($css, false, 'css');

        $theme = waRequest::get('theme');
        $action = waRequest::get('type') ?: 'main';
        $id = "mini.$theme.$strategy.$action.css";

        $path = wa()->getDataPath("critical/$id", true, 'mini');
        if (file_put_contents($path, $css)) {
            $result = "Критический CSS типа $action типа $strategy для темы $theme успешно обновлен. ";
            waLog::log("Критический CSS типа $action типа $strategy для темы $theme успешно обновлен.", 'mini.log');
        } else {
            $result = 'Не удалось создать критический CSS. ';
        }

        $this->displayJson($result);
    }

    public function imagesAction()
    {
        $url = waRequest::get('url');
        $strategy = waRequest::isMobile() ? 'mobile' : 'desktop';
        $key = self::GOOGLE_API_KEY;
        $google_url = "https://www.googleapis.com/pagespeedonline/v3beta1/optimizeContents?key=$key&url=$url&strategy=$strategy";
        $net = new waNet(['timeout' => 60]);
        $response = @$net->query($google_url);
        if ($response) {
            $n = $this->parseArchive($response);
            $this->displayJson('Успешно оптимизировано изображений: ' . $n);
        } else {
            $this->displayJson('Произошла ошибка при загрузке, попробуйте еще раз позже');
        }
    }

    private function parseArchive($content)
    {
        $tmp_path = wa()->getTempPath('imgs', 'mini');
        waFiles::delete($tmp_path);
        waFiles::create($tmp_path);

        $filename = tempnam($tmp_path, 'goog');
        file_put_contents($filename, $content);

        $zip = new ZipArchive;
        if ($zip->open($filename) === TRUE) {
            $zip->extractTo($tmp_path);
            $zip->close();
        }
        if (!file_exists($tmp_path . '/MANIFEST')) {
            $this->displayJson('Не удалось открыть архив от google');
            waLog::log('Не удалось открыть архив от google', 'mini.log');
            return false;
        }
        $images = $this->parseManifest($tmp_path . '/MANIFEST');
        $n = 0;
        foreach ($images as $img) {
            if (file_exists($img['local']) && @getimagesize($img['local'])) {
                copy($tmp_path . '/' . $img['arch'], $img['local']);
                waLog::log('Изображение ' . $img['local'] . ' успешно оптимизировано', 'mini.log');
                $n++;
            }
        }
        return $n;
    }

    private function parseManifest($filename)
    {
        $man = file_get_contents($filename);
        $re = '/(.*): (http.*)/';
        preg_match_all($re, $man, $matches);
        $result = [];
        foreach ($matches[1] as $k => $v) {
            if (false !== strpos($v, 'image/')) {
                $result[$k]['arch'] = $v;
                $result[$k]['local'] = waRequest::server('DOCUMENT_ROOT') . parse_url($matches[2][$k], PHP_URL_PATH);
            }
        }
        return $result;
    }
}
