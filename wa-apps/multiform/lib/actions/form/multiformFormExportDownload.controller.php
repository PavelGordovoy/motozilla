<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormExportDownloadController extends waController
{

    public function execute()
    {
        $filename = waRequest::get("file");
        $form_id = waRequest::get("form_id");

        $mform = new multiformFormModel();
        $form = $mform->getFormSettings($form_id);
        if (multiformHelper::recordAccess($form) !== 3) {
            throw new waRightsException('Access denied');
        }

        $file = wa()->getTempPath('csv/export/' . $form_id . '/' . $filename);
        if (file_exists($file)) {
            waFiles::readFile($file, $filename);
        }
    }

}
