<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

return array(
    'input' => array(
        'control_type' => waHtmlControl::INPUT,
        'title' => _w('Input field'),
        'value' => '',
    ),
    'file' => array(
        'control_type' => waHtmlControl::FILE,
        'title' => _w('File field'),
        'value' => '',
        'required' => 1,
        'file_max_count' => 10,
        'file_max_size' => 2000000,
        'file_extension' => array('jpg', 'jpeg', 'jpe', 'png'),
        'private' => 0
    ),
    'file2' => array(
        'control_type' => waHtmlControl::FILE,
        'title' => _w('File field 2'),
        'value' => '',
        'file_max_count' => 10,
        'file_max_size' => 2000000,
        'file_extension' => '*',
        'private' => 0
    ),
    'select' => array(
        'title' => _w('Select field'),
        'value' => 'two',
        'required' => 1,
        'control_type' => waHtmlControl::SELECT,
        'options' => array(
            array('title' => _w('One column'), 'value' => 'one'),
            array('title' => _w('Two columns'), 'value' => 'two'),
            array('title' => _w('Three columns'), 'value' => 'three'),
            array('title' => _w('One by one'), 'value' => 'line'),
        ),
    ),
    'checkbox' => array(
        'label' => _w('Checkbox field without title'),
        'required' => 1,
        'value' => 0,
        'description' => _w('The options will be shuffled each time'),
        'control_type' => waHtmlControl::CHECKBOX,
    ),
    'groupbox' => array(
        'title' => _w('Groupbox'),
        'control_type' => waHtmlControl::GROUPBOX,
        'value' => array('2' => '2', '3' => '3'),
        'options' => array(
            '1' => _w('first'),
            '2' => _w('second'),
            '3' => _w('third'),
        ),
    ),
    'radio' => array(
        'title' => _w('Radiogroup'),
        'control_type' => waHtmlControl::RADIOGROUP,
        'value' => '',
        'options' => array(
            1 => _w('first'),
            2 => _w('second'),
            3 => _w('third'),
        ),
    ),
);
