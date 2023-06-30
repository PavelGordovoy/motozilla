<?php

return array(
    'name' => 'Mobile phones',
    'icon' => 'ss pt mobile',
    'features' => array(
        'brand' => array(
            'name' => 'Brand',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'selectable' => true,
            'values' => array(
                'Apple',
                'BlackBerry',
                'Casio',
                'Dell',
                'HTC',
                'Huawei',
                'Kyocera',
                'LG',
                'Motorola',
                'Nokia',
                'Palm',
                'Samsung',
                'Sanyo',
                'Sharp',
                'Sony Ericsson',
            ),
        ),
        'platform' => array(
            'name' => 'Software platform',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'selectable' => true,
            'values' => array(
                'Android',
                'bada',
                'BlackBerry OS',
                'iOS',
                'Linux',
                'MeeGo',
                'Nokia Series 40',
                'Nokia Series 60',
                'Symbian',
                'Symbian UIQ',
                'webOS',
                'Windows Mobile',
                'Windows Phone',
            ),
        ),
        'form_factor' => array(
            'name' => 'Form factor',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'selectable' => true,
            'values' => array(
                'bar',
                'flip',
                'slider',
                'swivel',
                'watch',
                'mixed',
            ),
        ),
        'sim_card_count' => array(
            'name' => 'SIM card count',
            'type' => shopFeatureModel::TYPE_DOUBLE,
            'selectable' => true,
            'values' => array(1, 2, 3, 4),
        ),
        'screen_size' => array(
            'name' => 'Screen size',
            'type' => shopFeatureModel::TYPE_DIMENSION.'.length',
            'selectable' => true,
        ),
        'phone_memory' => array(
            'name' => 'Phone memory',
            'type' => shopFeatureModel::TYPE_DIMENSION.'.memory',
        ),
        'material' => array(
            'name' => 'Material',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'selectable' => true,
            'multiple' => true,
            'values' => array(
                'aluminium',
                'plastic',
                'glass',
                'steel',
                'polycarbonate',
            ),
        ),
        'touch_screen' => array(
            'name' => 'Touch screen',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'selectable' => true,
            'values' => array(
                'capacitive',
                'resistive',
            ),
        ),
        'camera_megapixels' => array(
            'name' => 'Camera megapixels',
            'type' => shopFeatureModel::TYPE_DOUBLE,
        ),
        'camera_resolution' => array(
            'name' => 'Camera resolution',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'selectable' => true,
            'values' => array(
                '352 x 288',
                '600 x 800',
                '640 x 480',
                '1280 x 960',
                '1280 x 1024',
                '1304 x 968',
                '1600 x 1200',
                '1728 x 1296',
                '2048 x 1536',
                '2560 x 1920',
                '2584 x 1938',
                '2592 x 1936',
                '2592 x 1944',
                '3264 x 2448',
                '4256 x 2832',
            ),
        ),
        'weight' => array(
            'name' => 'Weight',
            'type' => shopFeatureModel::TYPE_DIMENSION.'.weight',
            'builtin' => 1
        ),
        'gtin' => array(
            'name' => 'GTIN',
            'type' => shopFeatureModel::TYPE_VARCHAR,
            'status' => shopFeatureModel::STATUS_PRIVATE,
            'available_for_sku' => 1,
            'builtin' => 1
        ),
    ),
);
