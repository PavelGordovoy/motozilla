<?php

return array(
    "ENABLED" => array(
		'title'			=> _wp('Включить плагин'),
		'description'	=> _wp('Если плагин перестал быть полезен, его можно выключить.'),
		'control_type'	=> waHtmlControl::CHECKBOX,
		'value'			=> 1
    ),

    "POSITION_CLASS" => array(
        'title'         => _wp('Позиция переключателя'),
        'description'	=> _wp('Позволяет выбрать позицию переключателя режима'),
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            array(
                'title' => 'По центру',
                'value' => '',
            ),
            array(
                'title' => 'Слева',
                'value' => 'waheader-control-left',
            ),
            array(
                'title' => 'Справа',
                'value' => 'waheader-control-right',
            ),
        ),
        'value'         => 'click',

    ),

    "TYPE_EVENT" => array(
        'title'         => _wp('Тип события'),
        'description'	=> _wp('Позволяет выбрать событие при котором будет отображаться/скрываться шапка'),
        'control_type'  => waHtmlControl::SELECT,
        'options'       => array(
            array(
                'title' => 'Клик',
                'value' => 'click',
            ),
            array(
                'title' => 'Наведение',
                'value' => 'mouseover',
            ),
        ),
        'value'         => 'click',

    ),
);