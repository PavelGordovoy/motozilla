<?php

return array(
    'name' => /*_wp*/('Helpdesk PRO'),
    'icon' => 'img/igaponov.png',
    'description' => /*_wp*/('Helpdesk with advanced opportunities'),
    'version' => '1.3.1',
    'vendor' => '969712',
    'handlers' => array(
        'view_workflow_action' => 'viewWorkflowAction',
        'backend_layout' => 'backendLayout',
        'view_action' => 'viewAction',
        'sidebar' => 'sidebar',
        'requests_delete' => 'requestsDelete',
        'request_created' => 'requestCreated',
        'frontend_error' => 'frontendError',
        'json_controller' => 'jsonController'
    ),
);
