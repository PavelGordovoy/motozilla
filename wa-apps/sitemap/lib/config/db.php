<?php
return array(
    'sitemap_application_structure' => array(
        'app_id' => array('varchar', 64, 'null' => 0),
        'domain_id' => array('int', 11, 'unsigned' => 1, 'null' => 0),
        'route_url' => array('varchar', 200, 'null' => 0),
        'element_id' => array('varchar', 32, 'null' => 0),
        'plugin_id' => array('varchar', 32, 'null' => 0, 'default' => ''),
        'change_frequency' => array('enum', "'always','hourly','daily','weekly','monthly','yearly','never'"),
        'priority' => array('float'),
        'is_shown' => array('enum', "'Y','N'", 'null' => 0, 'default' => 'Y'),
        'sort' => array('tinyint', 3, 'unsigned' => 1, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('app_id', 'domain_id', 'route_url', 'element_id', 'sort', 'plugin_id'),
            'app_id_domain_id_route_url_element_id_plugin_id' => array('app_id', 'domain_id', 'route_url', 'element_id', 'plugin_id', 'unique' => 1),
        ),
    ),
    'sitemap_settings' => array(
        'app_id' => array('varchar', 64, 'null' => 0),
        'domain_id' => array('int', 10, 'unsigned' => 1, 'null' => 0),
        'route_url' => array('varchar', 200, 'null' => 0),
        'name' => array('varchar', 32, 'null' => 0),
        'value' => array('text', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('app_id', 'domain_id', 'route_url', 'name'),
        ),
    ),
);
