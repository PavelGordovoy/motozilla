<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformProcessingFileAction extends multiformProcessingAction
{

    private $files = array();

    public function __construct()
    {
        $this->record_id = waRequest::post("record_id", 0, waRequest::TYPE_INT);
        parent::__construct();
    }

    public function execute()
    {
        $this->record_id = waRequest::post("record_id", 0, waRequest::TYPE_INT);
        $field_id = waRequest::post("field_id", 0, waRequest::TYPE_INT);
        $unique_id = waRequest::post("unique_id", 0, waRequest::TYPE_INT);
        $cloned_index = waRequest::post("cloned_index", 0);

        // Пропустить шаги проверок, если форма обрабатывается из бекэнда.
        $skip = wa()->getEnv() == 'backend' ? 1 : 0;

        // Если отсутствуют идентификаторы поля или записи, прерываем обработку
        if (!$this->record_id) {
            $this->addError(_wd('multiform', "Cannot save files"));
            multiformHelper::log("Lost record_id");
        }

        $mrm = new multiformRecordModel();
        $field_files = waRequest::file('files_' . $field_id . ($cloned_index ? '_' . str_replace('.', '_', $cloned_index) : ''));

        try {
            // Получаем информацию о форме
            $cache = new waSerializeCache('multiform_form_' . $this->record_id);
            // Получаем информацию о всех полях формы
            if ($cache->isCached()) {
                $this->form = $cache->get();
            } else {
                $mf = new multiformFormModel();
                $form_id = $mrm->select("form_id")->where("id = i:id", array("id" => $this->record_id))->fetchField();
                $this->form = $mf->getFullForm($form_id, true);
                $cache->set($this->form);
            }
            $form_fields = $this->form['fields'];

            // Если форма не общедоступна
            if (!$this->form['type'] && !wa()->getUser()->isAdmin('multiform') && wa()->getUser()->getRights("multiform", "forms.{$this->form['id']}") <= 1) {
                throw new waRightsException('Access denied');
            }

            if ($field_files->uploaded()) {
                // Если переданное поле существует
                if (isset($form_fields[$unique_id]) && $form_fields[$unique_id]['type'] == 'file' && (!isset($form_fields[$unique_id]['properties']['save_data']) || (isset($form_fields[$unique_id]['properties']['save_data']) && $form_fields[$unique_id]['properties']['save_data']))) {
                    foreach ($field_files as $file) {
                        if ($this->isValid($file, $form_fields[$unique_id])) {
                            // Сохраняем файлы для последующей обработки
                            $this->add($form_fields[$unique_id]['id'], $file, $cloned_index);
                        }
                    }
                    $this->save();
                }
            }
        } catch (Exception $e) {
            $this->addError(_wd('multiform', "Cannot save files"));
            multiformHelper::log($e->getMessage());
        }
        // Если хоть один файл не удалось сохранить, прерываем весь процесс сохранения
        if ($this->hasErrors()) {
            // Проверяем. не пытается ли кто-то подменить данные
            $record = $mrm->getRecord($this->record_id);
            // Если IP создателя формы совпадают и не прошло более 5 минут после создания формы, удаляем все данные
            if ($record && $this->isValidUser($record)) {
                if (!$skip) {
                    $mrm->delete($this->record_id);
                }
                $this->generateResponse();
            }
        }
        // Если пришел указатель на необходимость завершения общего процесса сохранения формы (отправление уведомлений и тд)
        if (waRequest::post("isLast")) {
            $mrm = new multiformRecordModel();
            $this->record = $mrm->getRecord($this->record_id);
            if ($this->isValidUser($this->record)) {
                // Очищаем кеш
                $cache->delete();
                // Доступ к частным полям
                $private_field_access = multiformHelper::privateFieldAccess($this->form);
                // Проверка обязательных полей
                foreach ($form_fields as $ff) {
                    if (!$ff['status'] || ($ff['private'] && !$private_field_access) || (!$skip && $ff['hidden']) || ($skip && isset($ff['properties']['save_data']) && !$ff['properties']['save_data'])) {
                        continue;
                    }
                    if ($ff['type'] == 'file' && $ff['required'] && empty($ff['multiform_hide']) && empty($this->record['fields'][$ff['id']])) {
                        $this->addError(array('required' => _wd('multiform', "Fill in the required fields")), $ff['id']);
                        if (!$skip) {
                            $mrm->delete($this->record_id);
                        }
                        $this->generateResponse();
                    }
                }
                $this->result['data']['has_files'] = 1;
                // Выполняем заключительный этап обработки формы
                $this->postExecute();
            }
        } else {
            $this->result['data'] = $this->record_id;
            $this->generateResponse();
        }
    }

    /**
     * Save files
     *
     * @param int $record_id
     */
    public function save($record_id = 0)
    {
        if ($record_id) {
            $this->record_id = (int) $record_id;
        }
        $values = $file_values = array();

        try {
            foreach ($this->files as $file) {
                $field_id = $file['field_id'];
                foreach ($file['file'] as $f) {
                    if ($fileinfo = $this->_save($f, $field_id, $file['index'])) {
                        $values[] = array("record_id" => $this->record_id, "field_id" => $field_id, "ext" => 'file', "value" => '', 'cloned_index' => $file['index']);
                        $file_values[] = array("filename" => $fileinfo['filename'], "hash" => $fileinfo['hash']);
                    }
                }
            }
            if ($values) {
                $mrvm = new multiformRecordValuesModel();
                $mfiles = new multiformFilesModel();
                foreach ($values as $k => $value) {
                    $record = $mrvm->getByField($value);
                    $record_increment_id = $record ? $record['id'] : $mrvm->insert($value, 2);
                    $file_values[$k]['record_id'] = $record_increment_id;
                    $mfiles->insert($file_values[$k]);
                }
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
     * @param string $index
     * @return array|boolean
     */
    private function _save($file, $field_id, $index)
    {
        $filename = $new_filename = multiformHelper::secureString($file->name);
        // Создаем папку, в которую будем сохранять файлы
        $save_path = wa()->getDataPath('files/' . $this->record_id . '/' . $field_id . '/' . ($index ? $index : '') . '/', false, 'multiform');
        // Если не удается создать каталог для сохранения файла, прерываем всю обработку
        if (!waFiles::create($save_path)) {
            $this->addError(sprintf(_wd('multiform', "Cannot save file \"%s\""), $filename), $field_id);
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
        $file_max_count = !empty($settings['validation']['file_max_count']) ? $settings['validation']['file_max_count'] : '';
        if ($file_max_count !== '' && count($files) > (int) $file_max_count) {
            $this->addError(_wd('multiform', 'Maximum quantity of files') . _wd('multiform', ' for the field ') . multiformHelper::secureString($settings['title']) . ": " . $file_max_count, $settings['id']);
            return false;
        }

        foreach ($files as $file) {
            // Максимальный размер передаваемого файла
            $file_max_size = (!empty($settings['validation']['file_max_size']) && $settings['validation']['file_max_size'] < $system_max_filesize['converted']) ? $settings['validation']['file_max_size'] : $system_max_filesize['converted'];
            // Проверяем размер загруженного файла
            if ($file->size > $file_max_size) {
                $this->addError(sprintf(_wd('multiform', "File \"%s\" is too large"), multiformHelper::secureString($file->name)) . ". " . sprintf(_wd('multiform', "Maximum filesize is %d byte"), $file_max_size), $settings['id']);
                $result = 0;
                continue;
            }

            // Проверяем расширение
            $file_extension = strtolower($file->extension);
            if (!$this->extensionCheck($file, $file_extension, $settings)) {
                $this->addError(sprintf(_wd('multiform', "File extension \"%s\" is not permitted"), $file_extension) . _wd('multiform', ' for the field ') . multiformHelper::secureString($settings['title']), $settings['id']);
                $result = 0;
                continue;
            }
        }
        return $result;
    }

    /**
     * Add file to array
     *
     * @param int $field_id
     * @param waRequestFile $file
     * @param string $index
     */
    public function add($field_id, $file, $index)
    {
        $this->files[$field_id . '_' . $index] = [
            'field_id' => $field_id,
            'file' => $file,
            'index' => $index
        ];
    }

    /**
     * Check file extension. If we have image(jpg, jpeg, jpe, png, gif), then return waImage object.
     * Else return waRequestFile $file
     *
     * @param waRequestFile $file
     * @param $file_extension
     * @param array $settings - field info
     * @return false|waImage|waRequestFile
     */
    private function extensionCheck($file, $file_extension, $settings)
    {
        $extensions = !empty($settings['validation']['file_extension']) ? unserialize($settings['validation']['file_extension']) : array();
        // Проверяем допустимые расширения файла
        if ($extensions && !in_array($file_extension, $extensions)) {
            return false;
        }
        // Если требуется передать изображение, то получаем экземпляр класса waImage
        if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'jpe' || $file_extension == 'png' || $file_extension == 'gif') {
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
     * Check, if user request is not malware
     *
     * @param array $record
     * @return bool
     */
    private function isValidUser($record)
    {
        return (time() - strtotime($record['create_datetime'])) < 300 && !$record['status'] && (($record['ip'] == waRequest::getIp() && wa()->getEnv() !== 'backend') || (wa()->getEnv() == 'backend'));
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
        return (new multiformFilesModel())->countByField('hash', $hash);
    }

}
