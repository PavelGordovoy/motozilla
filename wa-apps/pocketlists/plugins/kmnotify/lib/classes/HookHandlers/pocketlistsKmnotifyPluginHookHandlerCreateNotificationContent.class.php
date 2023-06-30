<?php

/**
 * Class pocketlistsKmnotifyPluginHookHandlerCreateNotificationContent
 */
class pocketlistsKmnotifyPluginHookHandlerCreateNotificationContent implements pocketlistsHookHandlerInterface
{
    /**
     * @var pocketlistsKmnotifyPlugin
     */
    protected $plugin;

    /**
     * pocketlistsKmnotifyPluginHookHandlerCreateNotificationContent constructor.
     */
    public function __construct()
    {
        $this->plugin = pocketlistsKmnotifyPlugin::getInstance();
    }

    /**
     * @param pocketlistsEvent $event
     *
     * @return mixed
     * @throws pocketlistsAssertException
     * @throws pocketlistsNotImplementedException
     */
    public function handle($event = null)
    {
        /** @var pocketlistsNotification $notification */
        $notification = $event->getObject();
        pocketlistsAssert::instance($notification, pocketlistsNotification::class);

        $content = pocketlistsKmnotifyPluginProviderContentFactory::generateFromNotification($notification);
        $event->setResponse($content);
    }
}
