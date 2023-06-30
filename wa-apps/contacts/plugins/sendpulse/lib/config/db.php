<?php

return array(
    'contacts_sendpulse_category_data' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        
        'sendpulse_id' => array('int',11, 'null' => 0),
        'sendpulse_creationdate'	=> array('varchar', 255, 'null' => null),
		'sendpulse_status'	=> array('int', 1, 'null' => 0),
		'sendpulse_status_explain'	=> array('varchar', 255, 'null' => null),

        'ss_id' => array('int',11, 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    
    'contacts_sendpulse_contact_data' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int',11, 'null' => 0),
        'email_id'=> array('varchar', 255, 'null' => null),
        'field'=> array('varchar', 255, 'null' => null),
        'field_value'=> array('varchar', 255, 'null' => null),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
        
    ),
    
);
