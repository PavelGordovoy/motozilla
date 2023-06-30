<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFrontendDownloadFileController extends waController
{

    public function execute()
    {
        $hash = waRequest::param("hash");
        multiformFiles::getFile($hash);
    }

}
