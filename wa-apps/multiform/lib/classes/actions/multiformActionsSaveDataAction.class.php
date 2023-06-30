<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsSaveDataAction extends multiformActionsSave
{

    public function preExecute()
    {
        // Если не передан ID действия, то необходимо создать новое действие и присвоить его форме
        $this->getActionId();
    }

    public function execute()
    {

        $maf = new multiformActionFormModel();

        // Проверяем принадлежность действия к форме
        if ($action = $maf->getFormActions($this->form_id, $this->action_id, $this->parent_id)) {
            // Если мы только что создали действие, запоминаем. Это пригодится при обработке файлов.
            $values = array();
            $has_files = array();
            $default_values = array('custom_name' => waRequest::post('custom_name'), 'custom_description' => waRequest::post('custom_description'), 'custom_color' => waRequest::post('custom_color'), 'auto_execute' => waRequest::post('auto_execute'));
            $data = waRequest::post('data', array());
            $data = array_merge($data, $default_values);
            // Перебираем все поля настроек у действия
            foreach ($action['settings'] as $k => $as) {
                $is_file = !empty($as['control_type']) && $as['control_type'] == 'file';
                // Проверка обязательных полей
                if (!empty($as['required']) && empty($data[$k]) && !$is_file) {
                    $this->addError(array('required' => _wd('multiform', "Fill in the required fields")), $k);
                }

                // Если не существует такого поля среди переданных, это не файл и не дефолтное поле, идем дальше
                if (!isset($data[$k]) && !$is_file) {
                    continue;
                }

                // Составляем массив для сохранения значений настроек
                if (!empty($data[$k])) {
                    if (is_array($data[$k])) {
                        foreach ($data[$k] as $key => $value) {
                            $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $k, "ext" => $key, "value" => $value);
                        }
                    } else {
                        $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $k, "ext" => '', "value" => $data[$k]);
                    }
                }
                // Если используется javascript и имеются активные поля файлов, указываем, что необходимо выполнить загрузку файлов прежде,
                // чем закончить обработку
                if ($is_file) {
                    $has_files[] = $k;
                }
            }
            // Добавляем дефолтные поля
            foreach ($default_values as $df_k => $df) {
                $values[] = array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $df_k, "ext" => '', "value" => $df);
            }
            // Сохраняем статус действия
            $maf->updateById($this->action_id, array('status' => waRequest::post('custom_status', 0, waRequest::TYPE_INT)));

            // Если имеются ошибки, то прерываем обработку
            if ($this->hasErrors()) {
                // Если только что создали действие, то удаляем его
                if ($this->is_create) {
                    $maf->delete($this->form_id, $this->action_id);
                }
                $this->generateResponse();
            }

            // Если работаем с созданным действием и имеются старые файлы.
            // Проверяем было ли что-нибудь удалено
            if (!$this->is_create && $has_files) {
                $delete_files = array();
                foreach ($has_files as $k) {
                    if (!empty($action['fields'][$k])) {
                        $delete_files = array_diff_key($action['fields'][$k], !empty($data[$k]) ? $data[$k] : array());
                        if ($delete_files) {
                            foreach ($delete_files as $df) {
                                $files_path = wa()->getDataPath('files/actions/' . $this->action_id . '/' . $k . '/' . $df, empty($action['settings'][$k]['private']), 'multiform');
                                waFiles::delete($files_path, true);
                            }
                        }
                    }
                }
            }

            // Сохраняем основные данные в БД
            $this->save($values);

            $this->result['data']['id'] = $this->action_id;
            $this->result['data']['form_id'] = $this->form_id;

            // Если есть поля файлов
            if ($has_files) {
                $this->result['data']['has_files'] = 1;

                // Сохраняем данные действия
                $cache_action = new waSerializeCache('multiform_action_' . $this->action_id);
                $actual_action = $maf->getFormActions($this->form_id, $this->action_id, $this->parent_id);
                $actual_action['create'] = $this->is_create;
                $cache_action->set($actual_action);

                $this->generateResponse();
            } else {
                $this->result['data']['reload'] = 1;
            }
            return;
        } else {
            $this->addError(_w('Action does not exist'));
            $this->generateResponse();
        }
    }

    /**
     * Insert or update data
     *
     * @param array $data
     * @return int 
     * 
     */
    private function save($data)
    {
        $mafs = new multiformActionFormSettingsModel();
        $mafs->deleteByField(array("form_id" => $this->form_id, "action_id" => $this->action_id));
        if ($data) {
            $mafs->multipleInsert($data);
        }
        return true;
    }

}
