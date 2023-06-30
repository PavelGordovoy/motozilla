<?php
return array(
	'name' => _wp('Type of contact'),
	'description' => _wp('Changes the contact type, person, or company'),
	'img'=>'img/icon.png',
	'vendor' => 972278,
	'version' => '1.0',
	'handlers' => array(
		'backend_contact_info' => 'backendContactInfo',
		'*' => [
			[
				'event_app_id' => 'team',
				'event' => 'backend_profile',
				'class' => 'contactsIscontactPlugin',
				'method' => 'backendContactInfo',
			]
		],
	),
);
