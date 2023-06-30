<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformMasksSaveController extends waJsonController
{

    public function execute()
    {
        if (!wa()->getUser()->isAdmin('multiform')) {
            throw new waRightsException('Access denied');
        }

        $multiform_mask = new multiformMaskModel();

        $masks = waRequest::post('mask', array());
        // Удаляем маски
        $mask_keys = $multiform_mask->getKeys();
        $delete_masks = array_diff($mask_keys, array_keys($masks));
        if ($delete_masks) {
            $multiform_mask->delete($delete_masks);
        }

        // Сохраняем данные
        if ($masks) {
            foreach ($masks as $id => $mask) {
                if (empty($mask['name'])) {
                    $this->errors['messages'][] = _w("Specify mask name");
                    break;
                }
                if (empty($mask['mask'])) {
                    $this->errors['messages'][] = _w("Print regexp mask");
                    break;
                }
                // Проверяем маски на обязательные символы регулярное выражения
                if (strpos($mask['mask'], '/') !== 0) {
                    $mask['mask'] = '/' . $mask['mask'];
                }
                if (substr($mask['mask'], -1) !== '/') {
                    $mask['mask'] .= '/';
                }
                $this->response = $multiform_mask->save($id, $mask);
            }
        }
    }

}
