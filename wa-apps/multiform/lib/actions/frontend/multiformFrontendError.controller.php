<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFrontendErrorController extends waController
{

    public function execute()
    {
        if (waRequest::isXMLHttpRequest()) {
            $response = array(
                "status" => 'fail',
                "errors" => array(
                    "messages" => array(_w("CSRF Protection"))
                )
            );

            exit(json_encode($response));
        } else {
            throw new waException('Form not found', 404);
        }
    }

}
