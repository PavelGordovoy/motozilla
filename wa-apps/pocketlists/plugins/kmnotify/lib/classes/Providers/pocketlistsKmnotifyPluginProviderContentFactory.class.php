<?php

/**
 * Class pocketlistsKmnotifyPluginProviderContentFactory
 */
class pocketlistsKmnotifyPluginProviderContentFactory
{
    /**
     * @param pocketlistsNotification $notification
     *
     * @return pocketlistsNotificationContentInterface
     * @throws pocketlistsNotImplementedException
     */
    public static function generateFromNotification(pocketlistsNotification $notification)
    {
        $provider = pocketlistsKmnotifyPlugin::getInstance()->getProvider($notification->getType());

        if ($provider instanceof pocketlistsKmnotifyPluginProviderInterface && $provider->isEnabledForUser(pl2()->getUser())) {
            return $provider->createContent($notification->getData());
        }

        throw new pocketlistsNotImplementedException(
            sprintf('Notification content for type %s not implemented for now', $notification->getType())
        );
    }
}
