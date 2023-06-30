<?php

class hubHidsetPlugin extends waPlugin
{

    public $hsets = array(
        'comments_per_page' => array(
            'type' => 'int',
            'desc' => 'Количество комментариев на одной странице/одной загрузке lazyloading на странице топика во фронтенде'
        ),
        'authors_per_page' => array(
            'type' => 'int',
            'desc' => 'Количество авторов на одной странице/одной загрузке lazyloading на странице авторов во фронтенде'
        ),
        'topics_per_page' => array(
            'type' => 'int',
            'desc' => 'Количество топиков на одной странице/одной загрузке lazyloading во фронтенде'
        ),
    );
}
