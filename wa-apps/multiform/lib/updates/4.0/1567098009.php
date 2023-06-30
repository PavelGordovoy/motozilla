<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

// Меняем стили у слайдера значений
$mts = new multiformThemeSettingsModel();
$theme_settings = $mts->getByField([
    'property' => 'additional',
    'field' => 'rangeslider',
    'ext' => 'color',
], true);

if ($theme_settings) {
    $values = [];
    foreach ($theme_settings as $setting) {
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'line:color',
            'value' => $setting['value']
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'circle:color',
            'value' => $setting['value']
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'inactive_line:color',
            'value' => '#e1e4e9'
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'value:color',
            'value' => '#fff'
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'value_block:color',
            'value' => '#ed5565'
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'inactive_value:color',
            'value' => '#999'
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'inactive_value_block:color',
            'value' => '#e1e4e9'
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'grid:color',
            'value' => '#e1e4e9'
        ];
        $values[] = [
            'theme_id' => $setting['theme_id'],
            'property' => $setting['property'],
            'field' => $setting['field'],
            'ext' => 'grid_value:color',
            'value' => '#999'
        ];
        $mts->deleteByField($setting);
    }
    $mts->multipleInsert($values);
}
