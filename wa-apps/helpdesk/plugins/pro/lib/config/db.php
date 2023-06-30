<?php
return array(
    'helpdesk_pro_attachments' => array(
        'request_id' => array('int', 11, 'null' => 0),
        'log_id' => array('int', 11, 'null' => 0),
        'attach_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255),
        'size' => array('int', 11),
        'not_found' => array('tinyint', 1, 'default' => '0'),
        'file' => array('varchar', 255),
        'attach_datetime' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => array('request_id', 'log_id', 'attach_id'),
        ),
    ),
    'helpdesk_pro_constructor' => array(
        'contact_id' => array('int', 11, 'null' => 0),
        'field_id' => array('varchar', 64, 'null' => 0),
        'sort' => array('int', 11, 'default' => '-1'),
        'params' => array('text'),
        'enable' => array('tinyint', 1, 'default' => '0'),
        'type' => array('varchar', 50, 'null' => 0, 'default' => ''),
        ':keys' => array(
            'PRIMARY' => array('contact_id', 'field_id'),
        ),
    ),
    'helpdesk_pro_favourites' => array(
        'contact_id' => array('int', 11, 'null' => 0),
        'message_id' => array('varchar', 20, 'null' => 0),
        'request_id' => array('int', 11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('contact_id', 'message_id', 'request_id'),
        ),
    ),
    'helpdesk_pro_settings' => array(
        'contact_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 50, 'null' => 0),
        'value' => array('text'),
        ':keys' => array(
            'PRIMARY' => array('contact_id', 'name'),
        ),
    ),
    'helpdesk_pro_spam' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'email' => array('varchar', 255, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
            'email' => array('email', 'unique' => 1),
        ),
    ),
);
