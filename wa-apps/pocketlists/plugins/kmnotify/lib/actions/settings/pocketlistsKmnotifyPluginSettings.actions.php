<?php

/**
 * Class pocketlistsKmnotifyPluginSettingsActions
 */
class pocketlistsKmnotifyPluginSettingsActions extends pocketlistsViewActions
{
    /**
     * @throws waException
     */
    public function commonAction()
    {
        if (!wa()->getUser()->getRights('pocketlists', 'plugin.kmnotify.settings')) {
            throw new pocketlistsForbiddenException();
        }

        $plugin = pocketlistsKmnotifyPlugin::getInstance();
        $dto = new pocketlistsKmnotifyPluginSettingsDto();
        $dto->userSettings = $plugin->getUserSettings();

        $dto->events = pocketlistsKmnotifyPluginEventFactory::getEvents();
        foreach ($plugin->getProviders(false) as $provider) {
            $dto->providers[$provider->getIdentifier()] = $provider;
        }

        if (waRequest::method() === 'post') {
            $onEvents = waRequest::post('events', [], waRequest::TYPE_ARRAY);

            $dto->userSettings->setOnEvents($onEvents)->saveSettings();
        }

        $this->view->assign(['settings' => $dto]);
    }
}
