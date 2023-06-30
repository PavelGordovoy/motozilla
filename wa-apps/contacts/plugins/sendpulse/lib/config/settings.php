<?php

return array(
    'sendpulse_api_id' => array(
        'value'			=> '',
        'title'			=> _wp('ID'),
        'control_type'     => waHtmlControl::INPUT,
        'description'	=> _wp('ID for REST API'),
    ),    
    'sendpulse_api_secret' => array(
        'value'			=> '',
        'title'			=> _wp('Secret key'),
        'control_type'     => waHtmlControl::INPUT,
        'description'	=> _wp('Secret Key for REST API'),
    ),   

    'cron_categories' => array(
        'value'            => array(),
        'title'            => _wp('Address books for sync'),
        'options_callback' => array('contactsSendpulsePlugin', 'settingsTemplates'),
        'control_type'     => waHtmlControl::GROUPBOX,
        'options_wrapper'  => array(
            'control_separator' => '</div><div class="value">',
        ),
    ),

    'cron_help' => array(
		'title'            => _wp("CRON"),
		'control_type' => waHtmlControl::CUSTOM.' '.'contactsSendpulsePlugin::settingCustomControl',
        //'description'=>_wp("To automatically export customers, you need to configure the CRON, to do this, add the job"),
    ),


    'fields_for_export_rand' => array(
        'value'            => array(),
        'title'            => _wp('Contact fields for export'),
        'options_callback' => array('contactsSendpulsePlugin', 'allContactSettingsFields'),
        'control_type'     => waHtmlControl::GROUPBOX,
        'options_wrapper'  => array(
            'control_separator' => '</div><div class="value">',
        ),
        'value'=>array('name', 'email')
    ),
    
    
);
