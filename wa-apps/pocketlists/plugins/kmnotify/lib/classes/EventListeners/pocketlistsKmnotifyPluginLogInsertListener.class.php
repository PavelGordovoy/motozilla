<?php

/**
 * Class pocketlistsKmnotifyPluginLogInsertListener
 */
class pocketlistsKmnotifyPluginLogInsertListener
{
    /**
     * @param pocketlistsEvent $plEvent
     *
     * @return bool
     * @throws waException
     */
    public function handle(pocketlistsEvent $plEvent)
    {
        /** @var pocketlistsLog $log */
        $log = $plEvent->getObject();

        $plugin = pocketlistsKmnotifyPlugin::getInstance();
        /** @var pocketlistsKmnotifyPluginEvent[] $event */
        $events = pocketlistsKmnotifyPluginEventFactory::createFromLog($log);

        if (!$events) {
            return false;
        }

        $sourceLocale = wa()->getUser()->getLocale();
//        wa()->pushActivePlugin('kmnotify', 'pocketlists');
        foreach ($plugin->getProviders() as $provider) {
            foreach ($events as $event) {
                $affectedUser = $event->getAffectedUser();

                $eventEnabled = $plugin->getUserSettings($affectedUser)->isProviderEnabledForEvent(
                    $provider->getIdentifier(),
                    $event->getName()
                );

                if ($eventEnabled) {
                    /** @var pocketlistsNotification $notification */
                    $notification = pl2()->getEntityFactory(pocketlistsNotification::class)->createNew();

                    pocketlistsLocale::forceLoad($affectedUser->getLocale(), 'kmnotify');

                    $content = $provider->createContentForEventFromLog($event, $log);
                    if (!$content) {
                        continue;
                    }

                    $content->setToContactId($affectedUser->getId());

                    $notification
                        ->setType($provider->getIdentifier())
                        ->setContent($content)
                        ->setContactId($content->getToContactId())
                        ->setIdentifier($event->getName())
                        ->setHandler('kmnotify')
                        ->setDirection($provider->getDirection())
                        ->setCreatedAt(date('Y-m-d H:i:s'));

                    if ($notification instanceof pocketlistsNotification) {
                        pl2()->getEntityFactory(pocketlistsNotification::class)->insert($notification);
                    }
                }
            }
        }
        pocketlistsLocale::forceLoad($sourceLocale, 'kmnotify');
//        wa()->popActivePlugin();
    }
}
