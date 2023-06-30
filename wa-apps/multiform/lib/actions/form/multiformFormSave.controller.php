<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormSaveController extends waJsonController
{

    public function execute()
    {
        $form_id = waRequest::get('form_id', 0, waRequest::TYPE_INT);
        if ($form_id) {

            $mform = new multiformFormModel();
            $form = $mform->getFormSettings($form_id);
            if (!multiformHelper::hasFullFormAccess($form)) {
                throw new waRightsException('Access denied');
            }

            $mf = new multiformFieldModel();

            /* Сохранение полей формы */
            $data = array();

            if (!waRequest::post('properties', array())) {
                $this->errors['messages'][] = _w('Field parameters are not existing');
                return;
            }

            $field_id = waRequest::post('field_id', 0, waRequest::TYPE_INT);

            // Информация о текущем поле
            $field = $mf->getDataById($field_id);
            // Если поле принадлежит форме, продолжаем обработку
            if ($field && $field['form_id'] == $form_id) {

                // Проверяем доступность поля
                if (!$instance = multiformFormBuilder::getFieldInstance($field['type'])) {
                    $this->errors['messages'][] = sprintf(_w('Field type %s is undefined'), $field['type']);
                    return;
                }

                $field_settings = $instance->getFieldOptions($field['type']);
                try {
                    foreach (multiformFormBuilder::getSettingsTabs() as $tab_id) {
                        $post_data[$tab_id] = waRequest::post($tab_id, array());
                        if (isset($field_settings[$tab_id])) {
                            foreach ($field_settings[$tab_id] as $f_id => $f) {
                                $this->saveSetting($f, $field, $f_id, $tab_id, $post_data, $instance,$data);

                                if (!empty($f['childs']['fields'])) {
                                    foreach ($f['childs']['fields'] as $child_option_id => $child) {
                                        $this->saveSetting($child, $field, $child_option_id, $tab_id, $post_data, $instance,$data);
                                    }
                                }
                            }
                        }
                    }

                    // Дополнительная обработка поля
                    $processing_name = 'before' . ucfirst($field['type']) . 'Save';
                    if (method_exists($instance, $processing_name)) {
                        $instance->$processing_name($data);
                    }
                } catch (waException $e) {
                    $this->errors['messages'][] = $e->getMessage();
                    return;
                }

                // Обновляем основную информацию о поле
                $mf->updateById($field_id, $data['field']);
                unset($data['field']);

                // Обновляем параметры поля
                $mfp = new multiformFieldParamsModel();
                $mfp->save($field_id, $data);

                $this->response['id'] = $field_id;
            } else {
                $this->errors['messages'][] = _w('Field is undefined');
                return;
            }
        }
    }

    private function saveSetting($option, $field, $option_id, $tab_id, &$post_data, $instance, &$data)
    {
        if (isset($option['filter'])) {
            $post_data[$tab_id][$option_id] = isset($post_data[$tab_id][$option_id]) ? $post_data[$tab_id][$option_id] : "";
            $post_data[$tab_id][$option_id] = self::cast($post_data[$tab_id][$option_id], 'int');
        }
        // Отделяем обязательные свойства поля
        if (isset($option['basic'])) {
            $data['field'][$option_id] = isset($post_data[$tab_id][$option_id]) ? $post_data[$tab_id][$option_id] : 0;
            // Проверяем символьный код
            if ($option_id == 'code') {
                $data['field']['code'] = preg_replace("/[^a-zA-Z0-9\-_]/", "", $data['field']['code']);
            }
        } else {
            // Дополнительная обработка полей настроек
            $processing_set_name = 'before' . ucfirst($option_id) . 'SettingsSave';
            if (method_exists($instance, $processing_set_name)) {
                $instance->$processing_set_name($tab_id, $option_id, $field, $data, $post_data, $this->response, $this->errors);
            } elseif (method_exists($this, $processing_set_name)) {
                $data = $this->$processing_set_name($tab_id, $option_id, $field, $data, $post_data);
            } elseif (isset($post_data[$tab_id][$option_id])) {
                $data[$tab_id][$option_id] = $post_data[$tab_id][$option_id];
            }
        }
    }

    private static function cast($val, $type)
    {
        switch ($type) {
            case 'int':
                $val = (int) $val;
                break;
            default:
                break;
        }
        return $val;
    }

    private function beforeAttributeSettingsSave($tab_id, $f_id, $field, $data, $post_data)
    {
        $attributes = array();
        if (isset($post_data[$tab_id][$f_id]) && $post_data[$tab_id][$f_id] !== '') {
            foreach ($post_data[$tab_id][$f_id]['name'] as $id => $attr) {
                if (!empty($attr) && isset($post_data[$tab_id][$f_id]['value'][$id]) && trim($attr) && trim($post_data[$tab_id][$f_id]['value'][$id]) !== '') {
                    $attr = str_replace(array('"', "'", "<", ">"), "", $attr);
                    $post_data[$tab_id][$f_id]['value'][$id] = str_replace(array('"', "'", "<", ">"), "", $post_data[$tab_id][$f_id]['value'][$id]);
                    $attributes[] = array("name" => $attr, "value" => $post_data[$tab_id][$f_id]['value'][$id]);
                }
            }
        }
        $data[$tab_id][$f_id] = $attributes ? serialize($attributes) : '';

        return $data;
    }

    private function beforeMaskSettingsSave($tab_id, $f_id, $field, $data, $post_data)
    {
        if (!empty($post_data[$tab_id]['regexp'])) {
            // Проверяем регулярное выражение
            if (strpos($post_data[$tab_id]['regexp'], '/') !== 0) {
                $post_data[$tab_id]['regexp'] = '/' . $post_data[$tab_id]['regexp'];
            }
            if (substr($post_data[$tab_id]['regexp'], -1) !== '/') {
                $post_data[$tab_id]['regexp'] .= '/';
            }
            $data[$tab_id]['regexp'] = $post_data[$tab_id]['regexp'];
        } else {
            $data[$tab_id]['regexp'] = "";
        }
        $data[$tab_id]['regexp_error'] = $post_data[$tab_id]['regexp_error'];
        $data[$tab_id]['regexp_casein'] = !empty($post_data[$tab_id]['regexp_casein']) ? $post_data[$tab_id]['regexp_casein'] : "";
        $data[$tab_id][$f_id] = !empty($post_data[$tab_id][$f_id]) ? 1 : 0;
        return $data;
    }

}
