<?php
return array(
    'name' => "Изображения для блога",
    'version' => '1.11.1',
    'description' => "Привязка изображений любых размеров к публикациям блога",
    'img' => 'img/skimage.png',
    'custom_settings' => true,
    'handlers' => array(
        'backend_post_edit' => 'addInputsFile',
    ),
    'vendor' => 1039853,
);