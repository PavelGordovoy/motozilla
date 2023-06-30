<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFrontendDownloadAttachmentController extends waController
{

    public function execute()
    {
        $hash = wa()->getEnv() == 'backend' ?  waRequest::get("hash") : waRequest::param("hash");
        multiformFiles::getAttachment($hash);
    }

}
