<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormOptionsUploadController extends waUploadJsonController
{

    public function execute()
    {
        $this->options_model = new multiformFieldOptionsModel();
        $this->option_id = waRequest::post("option_id", 0, waRequest::TYPE_INT);
        $this->field_id = $this->options_model->select("field_id")->where("id = i:id", array("id" => $this->option_id))->fetchField();

        $mf = new multiformFieldModel();
        $form_id = $mf->select("form_id")->where("id = i:id", array("id" => $this->field_id))->fetchField();
        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($form_id);
        if (!multiformHelper::hasFullFormAccess($form)) {
            throw new waRightsException('Access denied');
        }

        parent::execute();
    }

    protected function process()
    {
        $f = waRequest::file('image');

        try {
            $this->processFile($f);
        } catch (waException $e) {
            $this->errors[] = _w("Cannot upload file. Try to upload image");
        }
    }

    protected function getPath()
    {
        return multiformImage::getOptionImagePath($this->option_id, $this->field_id);
    }

    protected function isValid($f)
    {
        if ($this->option_id <= 0 || !$this->option_id) {
            $this->errors[] = _w('Option ID is empty');
            return false;
        }

        // Получаем ID поля
        if (!$this->field_id) {
            $this->errors[] = _w("Option doesn't exist");
            return false;
        }

        return true;
    }

    protected function save(waRequestFile $f)
    {
        $original_name = multiformHelper::secureString($f->name);

        // Получаем экземпляр класса waImage
        try {
            $image = waImage::factory($f);
        } catch (waException $e) {
            $this->errors[] = _w("Upload only images");
            return false;
        }

        try {
            // Создаем папку, в которую будем сохранять файлы
            if (!waFiles::create($this->path)) {
                $this->errors[] = sprintf(_w("Cannot save file \"%s\""), $original_name);
                return false;
            }

            // Создаем новое название файла
            $file_extension = strtolower($f->extension);
            do {
                $filename = uniqid() . '.original.' . $file_extension;
            } while (file_exists($this->path . $filename));

            // Удаляем старые изображения
            multiformImage::deleteImage($this->option_id, $this->field_id);

            // Сохраняем изображение
            $image->save($this->path . $filename);

            // Добавляем запись в БД. В случае ошибки удаляем созданный файл
            if (!$this->options_model->updateById($this->option_id, array("image" => $filename))) {
                $this->errors[] = sprintf(_w("Cannot save file \"%s\""), $original_name);
                waFiles::delete($this->path . $filename, true);
            } else {
                $this->response['image'] = $filename;
                $this->response['image_url'] = multiformImage::getOptionImage($this->option_id, $this->field_id, $filename, '50x50');
                return true;
            }
        } catch (Exception $e) {
            $this->errors[] = _w("Cannot save file. Check log file for more information.");
            multiformHelper::log($e->getMessage());
        }
        return false;
    }

}
