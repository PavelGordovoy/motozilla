<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsSaveFileAction extends multiformActionsSave
{

    private $files = array();
    private $saved_files = array();
    private $param;

    public function execute()
    {
        $this->param = waRequest::post("param");

        // Если отсутствуют идентификаторы действия, прерываем обработку
        if (!$this->action_id) {
            $this->addError(_wd('multiform', "Cannot save files"));
        }

        $field_files = waRequest::file('files_' . $this->param, '');
        try {
            // Получаем информацию о действии
            $cache = new waSerializeCache('multiform_action_' . $this->action_id);
            // Получаем информацию о всех полях формы
            if ($cache->isCached()) {
                $this->action = $cache->get();
                if ($field_files->uploaded()) {
                    // Если переданное поле существует
                    if (!empty($this->action['settings'][$this->param]) && $this->action['settings'][$this->param]['control_type'] == 'file') {
                        foreach ($field_files as $file) {
                            if ($this->isValid($file, $this->action['settings'][$this->param])) {
                                // Сохраняем файлы для последующей обработки
                                $this->add($file);
                            }
                        }
                        $this->save();
                    }
                }
            } else {
                $this->addError(_wd('multiform', "Cannot save files"));
            }
        } catch (Exception $e) {
            $this->addError(_wd('multiform', "Cannot save files"));
            multiformHelper::log($e->getMessage());
        }
        // Если хоть один файл не удалось сохранить, прерываем весь процесс сохранения
        if ($this->hasErrors()) {
            $this->deleteFiles();
            $this->generateResponse();
        }

        // Подготавливаем данные загруженных файлов для передачи на сайт
        if ($this->saved_files) {
            foreach ($this->saved_files as &$sf) {
                try {
                    new waImage($sf['path']);
                    $sf['url'] = multiformFiles::getActionFileUrl($this->action_id, $this->param, $sf['filename'], empty($this->action['settings'][$this->param]['private']));
                    $sf['isImage'] = 1;
                } catch (waException $e) {
                    $sf['isImage'] = 0;
                }
                $sf['filename'] = multiformHelper::secureString($sf['filename']);
                $sf['hash'] = multiformHelper::secureString($sf['hash']);
                $sf['param'] = multiformHelper::secureString($this->param);
            }
        }

        // Если пришел указатель на необходимость завершения общего процесса сохранения формы
        if (waRequest::post("isLast")) {
            // Очищаем кеш
            $cache->delete();
            // Проверка обязательных полей
            foreach ($this->action['settings'] as $k => $as) {
                // Если не существует такого поля среди переданных, идем дальше
                if (!isset($this->action['fields'][$k])) {
                    continue;
                }
                // Проверка обязательных полей
                if (!empty($as['required']) && $as['control_type'] == 'file' && empty($this->action['fields'][$k])) {
                    $this->addError(array('required' => _wd('multiform', "Fill in the required fields")), $k);
                    $this->deleteFiles();
                    $this->generateResponse();
                }
            }
            $this->result['data']['has_files'] = 1;
            $this->result['data']['id'] = $this->action_id;
            $this->result['data']['saved'] = $this->saved_files;

            if (!empty($this->action['create'])) {
                $this->result['data']['reload'] = 1;
            }
            // Выполняем заключительный этап обработки формы
            $this->postExecute();
        } else {
            $this->result['data']['id'] = $this->action_id;
            $this->result['data']['form_id'] = $this->form_id;
            $this->result['data']['saved'] = $this->saved_files;
            $this->generateResponse();
        }
    }

    /**
     * Save files
     */
    public function save()
    {
        $values = array();
        try {
            foreach ($this->files as $files) {
                foreach ($files as $file) {
                    if ($fileinfo = $this->_save($file)) {
                        $values[] = array('form_id' => $this->form_id, "action_id" => $this->action_id, "param" => $this->param, "ext" => $fileinfo['hash'], "value" => $fileinfo['filename']);
                    }
                }
            }
            if ($values) {
                $mafs = new multiformActionFormSettingsModel();
                $mafs->multipleInsert($values);
            }
        } catch (Exception $e) {
            $this->addError(_wd('multiform', "Cannot save files"));
            multiformHelper::log($e->getMessage());
        }
    }

    /**
     * Save file
     * 
     * @param waRequestFileIterator $file
     * @param int $field_id
     * @return array|boolean
     */
    private function _save($file)
    {
        $filename = $new_filename = multiformHelper::secureString($file->name);
        // Создаем папку, в которую будем сохранять файлы
        $save_path = wa()->getDataPath('files/actions/' . $this->action_id . '/' . $this->param . '/', empty($this->action['settings'][$this->param]['private']), 'multiform');
        // Если не удается создать каталог для сохранения файла, прерываем всю обработку
        if (!waFiles::create($save_path)) {
            $this->addError(sprintf(_wd('multiform', "Cannot save file \"%s\""), $filename), $this->param);
            return false;
        }
        // Создаем уникальное название файла
        $pathinfo = pathinfo($filename);
        $c = 0;
        do {
            $new_filename = $pathinfo['filename'] . ($c > 0 ? '(' . $c . ')' : '') . '.' . $pathinfo['extension'];
            $c++;
        } while (file_exists($save_path . $new_filename));

        // Создаем уникальный хеш для файла
        do {
            $hash = substr(md5(uniqid()), rand(0, 9), rand(18, 20));
        } while ($this->issetHash($hash));

        // Сохраняем файл
        waFiles::copy($file, $save_path . $new_filename);
        $this->saved_files[$hash] = array("filename" => $new_filename, "path" => $save_path . $new_filename, "hash" => $hash);

        return array("filename" => $new_filename, "hash" => $hash);
    }

    /**
     * File validation
     * 
     * @staticvar array $system_max_filesize
     * @param waRequestFileIterator $files
     * @param array $settings
     * @return boolean
     */
    public function isValid($files, $settings)
    {
        static $system_max_filesize;
        $result = 1;
        if (!$system_max_filesize) {
            // Получаем настройки php.ini. Максимальный размер загружаемых файлов
            $ini_max_filesize = ini_get('upload_max_filesize');
            $ini_post_max_size = ini_get('post_max_size');
            $ini_max_filesize_converted = wa('multiform')->getConfig()->convertShortcuts($ini_max_filesize);
            $ini_post_max_size_converted = wa('multiform')->getConfig()->convertShortcuts($ini_post_max_size);
            $system_max_filesize = $ini_max_filesize_converted <= $ini_post_max_size_converted ? array("ini_value" => $ini_max_filesize, "converted" => $ini_max_filesize_converted) : array("ini_value" => $ini_post_max_size, "converted" => $ini_post_max_size_converted);
        }

        // Ограничение по количеству передаваемых файлов
        $file_max_count = !empty($settings['file_max_count']) ? $settings['file_max_count'] : 10;
        if (count($files) > (int) $file_max_count) {
            $this->addError(_wd('multiform', 'Maximum quantity of files') . _wd('multiform', ' for the field ') . multiformHelper::secureString($settings['name']) . ": " . $file_max_count, $this->param);
            return false;
        }

        foreach ($files as $file) {
            // Максимальный размер передаваемого файла
            $file_max_size = !empty($settings['file_max_size']) ? $settings['file_max_size'] : 2000000;
            $file_max_size = (!empty($file_max_size) && $file_max_size < $system_max_filesize['converted']) ? $file_max_size : $system_max_filesize['converted'];
            // Проверяем размер загруженного файла
            if ($file->size > $file_max_size) {
                $this->addError(sprintf(_wd('multiform', "File \"%s\" is too large"), multiformHelper::secureString($file->name)) . ". " . sprintf(_wd('multiform', "Maximum filesize is %d byte"), $file_max_size), $this->param);
                $result = 0;
                continue;
            }

            // Проверяем расширение
            $file_extension = strtolower($file->extension);
            if (!$this->extensionCheck($file, $file_extension, $settings)) {
                $this->addError(sprintf(_wd('multiform', "File extension \"%s\" is not permitted"), $file_extension) . _wd('multiform', ' for the field ') . multiformHelper::secureString($settings['title']), $this->param);
                $result = 0;
                continue;
            }
        }
        return $result;
    }

    /**
     * Add file to array
     * 
     * @param waRequestFileIterator $file
     */
    public function add($file)
    {
        $this->files[] = $file;
    }

    /**
     * Check file extension. If we have image(jpg, jpeg, jpe, png, gif), then return waImage object.
     * Else return waRequestFile $file
     * 
     * @param waRequestFile $file
     * @param array $settings - field info
     * @return false|waImage|waRequestFile
     */
    private function extensionCheck($file, $file_extension, $settings)
    {
        $extensions = !empty($settings['file_extension']) ? $settings['file_extension'] : array('jpg', 'jpeg', 'jpe', 'png');
        // Проверяем допустимые расширения файла
        if (($extensions && is_array($extensions) && !in_array($file_extension, $extensions)) || (!is_array($extensions) && $extensions !== '*')) {
            return false;
        }
        // Если требуется передать изображение, то получаем экземпляр класса waImage
        if (in_array($file_extension, array('jpg', 'jpeg', 'jpe', 'png', 'gif'))) {
            try {
                new waImage($file);
                $image = waImage::factory($file);
                return $image;
            } catch (waException $e) {
                multiformHelper::log($e->getMessage());
                return false;
            }
        }
        return $file;
    }

    /**
     * Count files in progress
     * 
     * @return int
     */
    public function count()
    {
        return count($this->files);
    }

    /**
     * Check, if file hash exists
     * 
     * @param string $hash
     * @return int
     */
    private function issetHash($hash)
    {
        return wao(new multiformActionFormSettingsModel())->countByField(array('form_id' => $this->form_id, "action_id" => $this->action_id, "param" => $this->param, "ext" => $hash));
    }

    /**
     * 
     */
    private function deleteFiles()
    {
        if (!empty($this->action['create']) || empty($this->action)) {
            $maf = new multiformActionFormModel();
            $maf->delete($this->form_id, $this->action_id);
        }
        // Если мы редактируем существующее, тогда удаляем только файлы
        elseif ($this->saved_files) {
            $mafs = new multiformActionFormSettingsModel();
            foreach ($this->saved_files as $sf_hash => $sf_name) {
                $mafs->deleteByField(array("form_id" => $this->form_id, "action_id" => $this->action_id, "param" => $this->param, "ext" => $sf_hash));
                $files_path = wa()->getDataPath('files/actions/' . $this->action_id . '/' . $this->param . '/' . $sf_name, empty($this->action['settings'][$this->param]['private']), 'multiform');
                waFiles::delete($files_path, true);
            }
        }
        $this->saved_files = array();
    }

}
