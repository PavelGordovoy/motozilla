<?php
return array (
  'name' => 'Header Toggle',
  'img' => 'img/waheader.png',
  'version' => '1.1.1',
  'vendor' => '1028182',
  'handlers' => array(
    '*' => array(
      array(
        'event_app_id' => 'webasyst',
        'event' => 'backend_header',
        'method' => 'backendHeader',
      )
    )
  ),
);
