<?php
return [
    'mylang' => [
        'id' => ['int', 11, 'null' => 0, 'autoincrement' => 1],
        'locale' => ['varchar', 10],
        'type' => ['varchar', 40],
        'subtype' => ['varchar', 128],
        'type_id' => ['varchar', 64],
        'text' => ['text'],
        'app_id' => ['varchar', 64],
        ':keys' => [
            'PRIMARY' => 'id',
            'locale' => ['locale', 'type', 'subtype', 'type_id', 'app_id', 'unique' => 1],
        ],
    ],
];
