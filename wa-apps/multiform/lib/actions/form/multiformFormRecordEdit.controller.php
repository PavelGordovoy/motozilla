<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFormRecordEditController extends waController
{

    public function execute()
    {
        if (waRequest::method() == 'post') {
            if (waRequest::post('data') == 'files') {
                $process = new multiformProcessingFileAction();
            } else {
                $process = new multiformProcessingDataAction();
            }
            $process->execute();
            $process->postExecute();
        }
    }

}
