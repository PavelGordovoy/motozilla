<?php
return array(
    'name'     => /*_wp*/('Notifications'),
    'img'      => 'img/kmnotify.gif',
    'version'  => '1.1.0',
    'vendor'   => '992205',
    'handlers' =>
        array(
            '*' => array(
                array(
                    'event_app_id' => 'pocketlists',
                    'event'        => 'backend_settings',
                    'class'        => 'pocketlistsKmnotifyPluginHookHandlerSettings',
                    'method'       => 'handle',
                ),
            ),

            'rights.config' => 'backendRightsConfig',
        ),
);
