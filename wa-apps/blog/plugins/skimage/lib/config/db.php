<?php
return array(
    'blog_skimage_groups' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 32, 'null' => 0),
        'title' => array('varchar', 255, 'null' => 0),
        'width' => array('int', 11),
        'height' => array('int', 11),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'blog_skimage_data' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'post_id' => array('int', 11, 'null' => 0),
        'group_name' => array('varchar', 32, 'null' => 0),
        'name' => array('text'),
        'type' => array('varchar', 64, 'null' => 0),
        'size' => array('int', 11),
        'query' => array('varchar', 64, 'default' => ''),
        ':keys' => array(
            'PRIMARY' => 'id',
            'post_group' => array('post_id', 'group_name', 'unique' => 1),
        ),
    ),
);