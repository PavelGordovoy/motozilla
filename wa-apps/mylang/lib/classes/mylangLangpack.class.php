<?php

use Gettext\Translations;

class mylangLangpack
{
    private $plugin;
    private $language;
    private $plugin_id;

    /**
     * mylangLangpack constructor.
     * @param null $plugin_id
     * @throws waException
     */
    public function __construct($plugin_id = null)
    {
        if ($plugin_id) {
            $this->plugin_id = $plugin_id;
            $this->plugin = wa('mylang')->getPlugin($plugin_id);
            $this->language = $this->plugin->getLanguage();
        }
    }


    /**
     * Get all langpack slug files
     * @return array|mixed
     * @throws waException
     */
    public function getConfig()
    {
        $plugin_config = wa('mylang')->getConfig()->getPluginPath($this->plugin->getId()). '/lib/config/config.php';
        if (!file_exists($plugin_config)) {
            return [];
        }
        return include $plugin_config;
    }


    /**
     * Format data and remove all except id/name/version
     * @param array $slug
     * @return array
     */
    public static function formatSlug($slug = [])
    {
        foreach ($slug['apps'] as &$app) {
            if (!empty($app['plugins'])) {
                foreach ($app['plugins'] as &$plugin) {
                    $plugin = [
                        'locale_path' => $plugin['locale_path'],
                        'name' => $plugin['name'],
                        'version' => $plugin['version']
                    ];
                }
            }
            if (!empty($app['widgets'])) {
                foreach ($app['widgets'] as &$widget) {
                    $widget = [
                        'locale_path' => $widget['locale_path'],
                        'name' => $widget['name'],
                        'version' => $widget['version']
                    ];
                }
            }
            $app = [
                'locale_path' => $app['locale_path'],
                'name' => $app['name'],
                'plugins' => ifset($app, 'plugins', []),
                'version' => $app['version'],
                'widgets' => ifset($app, 'widgets', []),
            ];
        }

        foreach ($slug['payment'] as &$payment) {
            $payment = [
                'locale_path' => $payment['locale_path'],
                'name' => $payment['name'],
                'version' => $payment['version']
            ];
        }
        foreach ($slug['shipping'] as &$shipping) {
            $shipping = [
                'locale_path' => $shipping['locale_path'],
                'name' => $shipping['name'],
                'version' => $shipping['version']
            ];
        }
        return $slug;
    }

    public function compareVersions()
    {
        $installed = mylangHelper::getSupported();
        $installed = self::formatSlug($installed);
        $plugin = $this->getConfig();
        $diff['apps'] = array_intersect_key($plugin['apps'], $installed['apps']);
        foreach ($diff['apps'] as $key=>&$app) {
            $app['installed_version'] = $installed['apps'][$key]['version'];
            foreach (['plugins', 'widgets'] as $slug) {
                if (isset($app[$slug])) {
                    $app[$slug] = array_intersect_key($installed['apps'][$key][$slug], ifset($plugin, 'apps', $key, $slug, []));
                    $app[$slug] = array_filter($app[$slug]);
                    foreach ($app[$slug] as $kitem=>&$item) {
                        $item['installed_version'] = $installed['apps'][$key][$slug][$kitem]['version'];
                        $item['locale_path'] = $installed['apps'][$key][$slug][$kitem]['locale_path'];
                        $item['status'] = version_compare($item['version'], $item['installed_version'], '>=');
                        $item['name'] = $installed['apps'][$key][$slug][$kitem]['name'];
                    }
                    unset($item);
                }
            }
            $app = array_filter($app);
            $app['status'] = version_compare($app['version'], $app['installed_version'], '>=');
            $app['name'] = $installed['apps'][$key]['name'];
            $app['locale_path'] = $installed['apps'][$key]['locale_path'];
        }
        unset($app);
        //TODO: shipping/payment/widgets
        ksort($diff['apps']);
        return $diff;
    }

    /**
     * Generate langpack for selected locale
     * @param string $locale
     * @return array
     * @throws waException
     */
    public function generate($locale = '')
    {
        $installed = mylangHelper::getSupported();
        $installed = self::formatSlug($installed);
        $path = waConfig::get('wa_path_cache').'/mylang/';
        waFiles::delete($path); //Clean temp path
        $config = [];
        $stat = ['apps'=>0, 'plugins'=>0, 'widgets'=>0];
        foreach ($installed['apps'] as $app_id=>$app) {
            $locale_file = $app['locale_path'].$locale.'/LC_MESSAGES/'.$app_id.'.po';
            if (file_exists($locale_file)) {
                $config['apps'][$app_id]['version'] = $app['version'];
                waFiles::copy($locale_file, $path.'/data/'.$app_id.'.po');
                ++$stat['apps'];
            }
            if (!empty($app['plugins'])) {
                foreach ($app['plugins'] as $key=>$plugin) {
                    $locale_file = $plugin['locale_path'].$locale.'/LC_MESSAGES/'.$app_id.'_'.$key.'.po';
                    if (file_exists($locale_file)) {
                        $config['apps'][$app_id]['plugins'][$key]['version'] = $plugin['version'];
                        waFiles::copy($locale_file, $path.'/data/plugins/'.$app_id.'_'.$key.'.po');
                        ++$stat['plugins'];
                    }
                }
            }
        }
        if (isset($config['apps'])) {
            ksort($config['apps']);
        }
        waUtils::varExportToFile($config, $path.'config.php');
        return ['file'=>mylangHelper::compress($path, $locale), 'stat'=>$stat];
    }


    /**
     * @param int $mode
     * @return array
     * @throws Exception
     */
    public function install($mode = 0): array
    {
        /*
        * INSTALL_MODE
        * 0 - all
        * 1 - missing
        * 2 - selected
        *  */
        $files = $this->prepareFiles();
        $this->copyFiles($files, $mode);
        $this->makeMO($files);
        return $files;
    }

    /**
     * @return array
     */
    public function prepareFiles()
    {
        $path = waConfig::get('wa_path_apps').'/mylang/plugins/'.$this->plugin_id.'/lib/config/data/';
        $toinstall = $this->compareVersions();
        $files = [];
        if (!empty($toinstall['apps'])) {
            foreach ($toinstall['apps'] as $app_id=>$app) {
                $files[] = [
                    'source' => $path.$app_id.'.po',
                    'target' => $app['locale_path'].$this->language.'/LC_MESSAGES/'.$app_id.'.po'
                ];
                foreach (['plugins', 'widgets'] as $slug) {
                    if (isset($app[$slug])) {
                        foreach ($app[$slug] as $plugin_id => $plugin) {
                            $files[] = [
                                'source' => $path.$slug.'/'.$app_id.'_'.$plugin_id.'.po',
                                'target' => $plugin['locale_path'].$this->language.'/LC_MESSAGES/'.$app_id.'_'.$plugin_id.'.po'
                            ];
                        }
                    }
                }
            }
        }
        return $files;
    }

    /**
     * @param $files
     * @param $mode
     * @return mixed
     * @throws Exception
     */
    private function copyFiles(&$files, $mode = 0)
    {
        foreach ($files as $key=>&$file) {
            if ($mode === 1 && file_exists($file['target'])) {
                unset($files[$key]); //remove from toMo list;
                continue;
            }
            try{
                waFiles::copy($file['source'], $file['target']);
            } catch (Exception $e) {
                waLog::dump($e->getMessage(), 'mylang/langpack.log');
            }
        }
        return $files;
    }

    /**
     * @param array $files
     */
    private function makeMO($files = null): void
    {
        if (empty($files)) {
            return;
        }
        if (!is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $file) {
            $file = ifset($file, 'target', $file);
            try {
                $translations = mylangGettext::readPO($file);
                mylangGettext::writeMO($translations, $file);
            } catch (Exception $e) {
                waLog::dump($e->getMessage(), 'mylang/langpackMO.log');
            }
        }
    }

    public static function getInstalled(): array
    {
        $plugins = wa('mylang')->getConfig()->getPlugins();
        $result = [];
        foreach ($plugins as $plugin_id => $plugin) {
            if (isset($plugin['language'])) {
                $result[$plugin_id] = $plugin;
            }
        }
        return $result;
    }
}
