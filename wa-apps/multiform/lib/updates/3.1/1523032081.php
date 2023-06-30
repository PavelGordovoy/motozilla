<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

/* Изменяем значение полей слайдера */
$model = new multiformFieldParamsModel();
try {
    $sql = "SELECT * FROM {$model->getTableName()} WHERE `param` = 'validation' AND `ext` = 'slider'";
    foreach ($model->query($sql) as $r) {
        $model->updateByField(array("field_id" => $r['field_id'], "param" => $r['param'], "ext" => $r['ext']), array('param' => 'display'));
    }
} catch (waDbException $e) {
    
}

/* Добавляем значение ширины поля для элементов, у которых нет заголовка или он расположен сверху */
try {
    $mf = new multiformFieldModel();
    $sql2 = "SELECT DISTINCT(mf.id) as id "
            . "FROM {$mf->getTableName()} mf "
            . "LEFT JOIN {$model->getTableName()} mfp ON (mfp.field_id = mf.id AND mfp.ext = 'field_width')"
            . "WHERE (title_pos = 'hide' OR title_pos = 'top') AND mfp.ext IS NULL";
    foreach ($mf->query($sql2) as $r2) {
        $model->insert(array('field_id' => $r2['id'], 'param' => 'display', 'ext' => 'field_width', 'value' => 'colm12'), 2);
    }
} catch (Exception $ex) {
    
}

/* Изменяем значение полей ширины поля */
try {
    $sql3 = "SELECT * FROM {$model->getTableName()} WHERE `param` = 'display' AND `ext` = 'width'";
    foreach ($model->query($sql3) as $r3) {
        $model->updateByField(array("field_id" => $r3['field_id'], "param" => $r3['param'], "ext" => $r3['ext']), array('param' => 'sizes', 'value' => ($r3['value'] == 'size-small' ? 'colm3' : ($r3['value'] == 'size-large' ? 'colm12' : 'colm6'))));
    }
} catch (waDbException $e) {
    
}