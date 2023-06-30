<?php

/**
 * Interface pocketlistsKmnotifyProviderInterface
 */
interface pocketlistsKmnotifyPluginProviderInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return bool
     */
    public function getDirection();

    /**
     * @param pocketlistsContact $user
     *
     * @return bool
     */
    public function isEnabledForUser(pocketlistsContact $user);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param pocketlistsKmnotifyPluginEvent $event
     * @param pocketlistsLog                 $log
     *
     * @return pocketlistsNotificationContentInterface|bool
     */
    public function createContentForEventFromLog(pocketlistsKmnotifyPluginEvent $event, pocketlistsLog $log);

    /**
     * @param string $rawData
     *
     * @return pocketlistsNotificationContentInterface
     */
    public function createContent($rawData);

    /**
     * @return string
     */
    public function getSettingsHtml();
}
