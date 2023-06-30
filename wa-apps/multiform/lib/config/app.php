<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */
return array(
    'name' => /*_w*/("Web-forms"),
    'description' => /*_w*/("Form builder. Create online surveys, form calculators, contact forms, logic forms."),
    'icon' => array(
        16 => 'img/multiform16.png',
        24 => 'img/multiform24.png',    
        48 => 'img/multiform.png',
        96 => 'img/multiform96.png',
    ),
    'routing_params' => array(
        'private' => true,
    ),
    'csrf' => true,
    'frontend' => true,
    'plugins' => true,
    'rights'      => true,
    'sms_plugins' => true,
    'version' => '4.4.0',
    'vendor' => '969712',
    /*
    'actions' => array(
        'record_color' => true,
        'available_fields_example' => true,
        'form_fields_example' => true,
        'custom_settings_example' => true,
        'conditions' => true,
        'multiple' => true,
    )
    */
);