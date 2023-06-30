<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsSaveController extends waController
{

    public function execute()
    {
        if (waRequest::method() == 'post') {
            if (waRequest::post('data') || waRequest::post('custom_name')) {
                $process = new multiformActionsSaveDataAction();
            } else {
                $process = new multiformActionsSaveFileAction();
            }
            $process->execute();
            $process->postExecute();
        }
    }

}
