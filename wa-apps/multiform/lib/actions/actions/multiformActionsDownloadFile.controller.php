<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsDownloadFileController extends waController
{

    public function execute()
    {
        $hash = waRequest::get("hash");
        multiformFiles::getActionFile($hash);
    }

}
