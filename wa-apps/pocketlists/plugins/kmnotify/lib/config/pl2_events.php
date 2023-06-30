<?php

return [
//    pocketlistsEventStorage::ITEM_SAVE => ['pocketlistsHelper', 'saveEntity'],
    pocketlistsEventStorage::CREATE_NOTIFICATION_CONTENT => [
        'pocketlistsKmnotifyPluginHookHandlerCreateNotificationContent',
        'handle',
    ],

    pocketlistsEventStorage::LOG_INSERT => ['pocketlistsKmnotifyPluginLogInsertListener', 'handle'],
];
