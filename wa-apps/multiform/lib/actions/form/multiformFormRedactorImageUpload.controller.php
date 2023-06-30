<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormRedactorImageUploadController extends waUploadJsonController
{

    protected function process()
    {
        $form_id = waRequest::get('form_id');
        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($form_id);
        if (!multiformHelper::hasFullFormAccess($form)) {
            throw new waRightsException('Access denied');
        }

        $f = waRequest::file('image');
        try {
            if ($this->processFile($f)) {
                $this->response['url'] = wa()->getDataUrl('multiform/' . $form_id . '/' . $f->name, true, 'site');
            }
        } catch (waException $e) {
            $this->errors[] = _w("Cannot upload file. Try to upload image");
        }
    }

    protected function getPath()
    {
        $form_id = waRequest::get('form_id', 0, waRequest::TYPE_INT);
        return wa()->getDataPath('multiform/' . ($form_id ? $form_id : ''), true, 'site');
    }

    private function getThumbPath()
    {
        $form_id = waRequest::get('form_id', 0, waRequest::TYPE_INT);
        return wa()->getDataPath('thumbs/' . ($form_id ? $form_id : ''), true, 'multiform');
    }

    protected function isValid($f)
    {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        if (!in_array(strtolower($f->extension), $allowed)) {
            $this->errors[] = sprintf(_w("Files with extensions %s are allowed only."), '*.' . implode(', *.', $allowed));
            return false;
        }
        // Получаем экземпляр класса waImage
        try {
            $image = waImage::factory($f);
        } catch (waException $e) {
            $this->errors[] = _w("Upload only images");
            return false;
        }
        return true;
    }

    protected function save(waRequestFile $f)
    {
        if (file_exists($this->path . DIRECTORY_SEPARATOR . $f->name)) {
            $j = strrpos($f->name, '.');
            $name = substr($f->name, 0, $j);
            $ext = substr($f->name, $j + 1);
            $i = 1;
            while (file_exists($this->path . DIRECTORY_SEPARATOR . $name . '-' . $i . '.' . $ext)) {
                $i++;
            }
            $this->name = $name . '-' . $i . '.' . $ext;
            $result = $f->moveTo($this->path, $this->name);
            $this->createThumb($this->path . DIRECTORY_SEPARATOR . $this->name, $this->name);
            return $result;
        }
        $result = $f->moveTo($this->path, $f->name);
        $this->createThumb($this->path . DIRECTORY_SEPARATOR . $f->name, $f->name);
        return $result;
    }

    private function createThumb($file, $name)
    {
        try {
            $image = waImage::factory($file);
            $image->resize('100', '75', 'AUTO', false);
            $image->save($this->getThumbPath() . '/' . $name);
        } catch (waException $e) {
            return false;
        }
    }

    public function display()
    {
        if (waRequest::isXMLHttpRequest()) {
            $this->getResponse()->addHeader('Content-Type', 'application/json');
        }
        $this->getResponse()->sendHeaders();
        if (!$this->errors) {
            $data = array($this->response);
            echo json_encode($data);
        } else {
            echo json_encode(array('error' => $this->errors));
        }
    }

}
