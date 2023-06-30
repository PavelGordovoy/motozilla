<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormAttachmentUploadController extends waUploadJsonController
{

    public function execute()
    {
        $this->field_id = waRequest::post("fieldId", 0, waRequest::TYPE_INT);
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
        $f = waRequest::file('attachment');

        try {
            $this->processFile($f);
        } catch (waException $e) {
            $this->errors[] = _w("Cannot upload file.");
        }
    }

    protected function getPath()
    {
        return multiformFiles::getAttachmentPath($this->field_id);
    }

    protected function isValid($f)
    {
        $mfm = new multiformFieldModel();
        // Проверяем поле, действительно ли оно для файлов
        if ($mfm->select("type")->where("id=i:id", array("id" => $this->field_id))->fetchField() !== 'file') {
            $this->errors[] = _w("Trying to add attachment to wrong field");
            return false;
        }

        if ($f->error) {
            $this->errors[] = _w("Uploaded file's size exceeds the upload_max_filesize or post_max_size limit");
            return false;
        }

        return true;
    }

    protected function save(waRequestFile $f)
    {

        $mfpm = new multiformFieldParamsModel();

        $original_name = multiformHelper::secureString($f->name);

        try {
            // Удаляем старый файл
            waFiles::delete($this->path, true);
            $mfpm->deleteByField(array(
                'field_id' => $this->field_id,
                'param' => 'display',
                'ext' => 'attach_file'
            ));

            // Создаем папку, в которую будем сохранять файл
            if (!waFiles::create($this->path)) {
                $this->errors[] = sprintf(_w("Cannot save file \"%s\""), $original_name);
                return false;
            }

            $f->moveTo($this->path, $original_name);

            $hash = md5(time() . $this->field_id . $original_name);
            $hash = substr($hash, 0, 16) . $this->field_id . substr($hash, 16);
            // Добавляем запись в БД. В случае ошибки удаляем созданный файл
            if (!$mfpm->insert(array('field_id' => $this->field_id, 'param' => 'display', 'ext' => 'attach_file', 'value' => serialize(array("hash" => $hash, "name" => $original_name))))) {
                $this->errors = sprintf(_w("Cannot save file \"%s\""), $original_name);
                waFiles::delete($this->path, true);
            } else {
                $this->response['file'] = $hash;
                $this->response['name'] = $original_name;
                return true;
            }
        } catch (Exception $e) {
            $this->errors[] = _w("Cannot save file. Check log file for more information.");
            multiformHelper::log($e->getMessage());
        }
        return false;
    }

}
