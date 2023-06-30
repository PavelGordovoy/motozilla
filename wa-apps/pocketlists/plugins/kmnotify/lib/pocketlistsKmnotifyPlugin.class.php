<?php

/**
 * Class pocketlistsKmnotifyPlugin
 */
class pocketlistsKmnotifyPlugin extends waPlugin
{
    /**
     * @var waView
     */
    protected $view;

    /**
     * @var pocketlistsKmnotifyPlugin
     */
    protected static $instance;

    /**
     * @var pocketlistsKmnotifyPluginUserSettings[]
     */
    protected $userSettings = [];

    /**
     * @var pocketlistsKmnotifyPluginProviderInterface[]
     */
    private $providers = [];

    /**
     * pocketlistsKmnotifyPlugin constructor.
     *
     * @param $info
     */
    public function __construct($info)
    {
        parent::__construct($info);

        $path = $this->path.'/lib/config/providers.php';
        if (file_exists($path)) {
            $providers = include($path);
            if (!is_array($providers)) {
                $providers = [];
            }
        } else {
            $providers = [];
        }

        foreach ($providers as $provider => $className) {
            if (class_exists($className)) {
                $class = new $className();
                if ($class instanceof pocketlistsKmnotifyPluginProviderInterface) {
                    $this->providers[$provider] = $class;
                }
            }
        }
    }

    /**
     * @return pocketlistsKmnotifyPlugin
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = wa(pocketlistsHelper::APP_ID)->getPlugin('kmnotify');
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return waView
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->view = wa()->getView();
        }

        return $this->view;
    }

    /**
     * @param pocketlistsContact|null $user
     *
     * @return pocketlistsKmnotifyPluginUserSettings
     */
    public function getUserSettings(pocketlistsContact $user = null)
    {
        if ($user === null) {
            $user = pl2()->getUser();
        }

        if (!isset($this->userSettings[$user->getId()])) {
            $this->userSettings[$user->getId()] = new pocketlistsKmnotifyPluginUserSettings($user);
        }

        return $this->userSettings[$user->getId()];
    }

    /**
     * @param bool $enabled
     *
     * @return pocketlistsKmnotifyPluginProviderInterface[]
     */
    public function getProviders($enabled = true)
    {
        if ($enabled) {
            $ok = [];
            foreach ($this->providers as $provider) {
                if ($provider->isEnabledForUser(pl2()->getUser())) {
                    $ok[] = $provider;
                }
            }

            return $ok;
        }

        return $this->providers;
    }

    /**
     * @param $type
     *
     * @return pocketlistsKmnotifyPluginProviderInterface
     * @throws pocketlistsNotImplementedException
     */
    public function getProvider($type)
    {
        if (!empty($this->providers[$type])) {
            return $this->providers[$type];
        }

        throw new pocketlistsNotImplementedException('Such provider %s not exists', $type);
    }

    /**
     * @param $rights waRightConfig
     */
    public function backendRightsConfig($rights)
    {
        $rights->addItem('plugin.kmnotify.settings', _wp('Allow user to change his own notification settings'));
    }
}
