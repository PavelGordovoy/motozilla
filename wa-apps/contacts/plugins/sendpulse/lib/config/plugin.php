<?php
return array(
    'name' => _wp('SendPulse'),
    'description' => _wp('Import/export emails to/from SendPulse'),
    'vendor' => 'SendPulse',
    'version' => '1.0.1.3',
    'vendor' => 1075870,
    
    'img'=>'img/logo.png',
    'icons'=>array(
        16 => 'img/16.png',
        24 => 'img/24.png',
        48 => 'img/24.png',
    ),
    'handlers' => array(
    	'backend_sidebar'=>'backendMenu',
     	'backend_contact_info'=>'backendContactInfo',  
     	'backend_assets'=>'includeScripts' 	
    ),
    'custom_settings' => true,
    
);
//EOF
