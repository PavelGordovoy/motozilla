<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformActionsLayout extends waLayout
{

    public function execute()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Expires: " . date("r"));

        $this->assign('form_id', waRequest::post("form_id", 0, waRequest::TYPE_INT));
    }

}
