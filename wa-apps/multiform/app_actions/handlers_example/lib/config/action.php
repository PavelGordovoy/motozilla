<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

return array(
    'name' => _w('Example. Handlers'),
    'description' => _w('Example of all handlers'),
    'version' => '1.0',
    'settings' => true,
    'handlers' => array(
        'js_before_submit' => 'jsBeforeSubmit',
        'js_validate' => 'jsValidate',
        'js_after_submit' => 'jsAfterSubmit',
        'php_validate' => 'phpValidate',
        'php_after_submit' => 'phpAfterSubmit'
    )
);
