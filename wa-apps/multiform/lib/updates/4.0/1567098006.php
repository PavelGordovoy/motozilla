<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

// Устанавливаем корректный размер для полей, у которых заголовок скрыт или назодится сверху
$ids = (new multiformFieldModel())->getByField([
    'title_pos' => ['top', 'hide']
], 'id');

if ($ids) {
    $mfp = new multiformFieldParamsModel();
    foreach (array_keys($ids) as $id) {
        $mfp->insert([
            'field_id' => $id,
            'param' => 'sizes',
            'ext' => 'field_width',
            'value' => 'colm12'
        ], 2);
    }
}
