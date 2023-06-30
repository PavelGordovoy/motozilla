<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

return array(
    'field1' => array(
        'control_type' => 'GetFormFieldsControl',
        'title' => _w('All fields'),
    ),
    'field2' => array(
        'control_type' => 'GetFormFieldsControl',
        'title' => _w('Only email and phone fields'),
        'filter' => array('email', 'phone')
    ),
    'field3' => array(
        'control_type' => 'GetFormFieldsControl',
        'title' => _w('Exclude section and HTML'),
        'exclude' => array('section', 'html')
    ),
);
