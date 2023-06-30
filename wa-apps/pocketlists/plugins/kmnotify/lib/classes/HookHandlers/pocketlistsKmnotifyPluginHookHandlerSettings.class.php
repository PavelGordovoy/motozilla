<?php

/**
 * Class pocketlistsKmnotifyPluginHookHandlerSettings
 */
class pocketlistsKmnotifyPluginHookHandlerSettings implements pocketlistsHookHandlerInterface
{
    /**
     * @var pocketlistsKmnotifyPlugin
     */
    protected $plugin;

    /**
     * pocketlistsKmnotifyPluginHookHandlerSettings constructor.
     */
    public function __construct()
    {
        $this->plugin = pocketlistsKmnotifyPlugin::getInstance();
    }

    /**
     * @param pocketlistsEvent|null $event
     *
     * @return mixed
     */
    public function handle($event = null)
    {
        $sidebarLi = '';
        if (wa()->getUser()->getRights('pocketlists', 'plugin.kmnotify.settings')) {
            $sidebarLi = $this->getView()->fetch($this->getViewTemplate('backend_settings.sidebar_li'));
        }

        return [
            'sidebar_li' => $sidebarLi,
        ];
    }

    /**
     * @return waView
     */
    protected function getView()
    {
        return $this->plugin->getView();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getViewTemplate($name)
    {
        return sprintf('%s/templates/hooks/%s.html', $this->plugin->getPath(), $name);
    }
}
