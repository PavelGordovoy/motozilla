<?php

/**
 * Class pocketlistsKmnotifyPluginUserSettings
 */
class pocketlistsKmnotifyPluginUserSettings
{
    const APP_ID = 'pocketlists.kmnotify';
    /**
     * @var pocketlistsContact
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $onEvents;

    /**
     * @var waContactSettingsModel
     */
    protected $model;

    /**
     * pocketlistsKmnotifyPluginUserSettings constructor.
     *
     * @param pocketlistsContact $user
     */
    public function __construct(pocketlistsContact $user)
    {
        $this->model = new waContactSettingsModel();
        $this->user = $user;

        $allSettings = $this->model->get($this->user->getContact()->getId(), self::APP_ID);
        $this->settings = $allSettings ?: [];
        $this->onEvents = json_decode(ifset($this->settings, 'events', '{}'), true);
    }

    /**
     * @return pocketlistsContact
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     *
     * @return pocketlistsKmnotifyPluginUserSettings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return array
     */
    public function getOnEvents()
    {
        return $this->onEvents;
    }

    /**
     * @param array $onEvents
     *
     * @return pocketlistsKmnotifyPluginUserSettings
     */
    public function setOnEvents($onEvents)
    {
        $this->onEvents = $onEvents;

        return $this;
    }

    /**
     *
     */
    public function saveSettings()
    {
        $this->model->set(
            $this->user->getContact()->getId(),
            self::APP_ID,
            [
                'events' => json_encode($this->onEvents, JSON_UNESCAPED_UNICODE),
            ]
        );
    }

    /**
     * @param $providerId
     * @param $eventId
     *
     * @return bool
     */
    public function isProviderEnabledForEvent($providerId, $eventId)
    {
        return !empty($this->onEvents[$eventId][$providerId]);
    }
}
