<?php

return array(

    'status' => array(
        'value' => "1",
        'title' => "Активность плагина",
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array(
                "value" => "1",
                "title" => "Включен",
            ),
            array(
                "value" => "0",
                "title" => "Отключен",
            ),
        ),
    ),

    'is_cache' => array(
        'value' => "0",
        'title' => "Кеширование",
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array(
                "value" => "1",
                "title" => "Включено",
            ),
            array(
                "value" => "0",
                "title" => "Отключено",
            ),
        ),
    ),

    'is_2x' => array(
        'value' => "1",
        'title' => "Разрешить создание эскизов @2x (для экранов с высокой плотностью пикселей)",
        'description' => "При включении опции нужно пересоздать эскизы (если изображения ранее загружались)",
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array(
                "value" => "1",
                "title" => "Включено",
            ),
            array(
                "value" => "0",
                "title" => "Отключено",
            ),
        ),
    ),

    'query_string' => array(
        'value' => "0",
        'title' => "Добавлять временную метку к имени изображения",
        'description' => "Для избежания кеширования браузером разных изображений с одинаковым именем",
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array(
                "value" => "1",
                "title" => "Включено",
            ),
            array(
                "value" => "0",
                "title" => "Отключено",
            ),
        ),
    ),

);
