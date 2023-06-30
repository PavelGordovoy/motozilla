<?php

return array(
    array(
        'event'        => '/view_*/',
        'class'        => 'mylangShopViewHandler',
        'method'       => array('execute'),
        'event_app_id' => 'shop'
    ),
    array(
        'event'        => 'view_pages',
        'class'        => 'mylangEventHandler',
        'method'       => array('pages'),
        'event_app_id' => '*'
    ),
);
