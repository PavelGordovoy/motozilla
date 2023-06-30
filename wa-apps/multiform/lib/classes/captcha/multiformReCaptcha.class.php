<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformReCaptcha extends waReCaptcha
{

    public function getHtml($form_uid = 0)
    {
        return parent::getHtml();
    }

    public function isValid($code = null, &$error = '')
    {
        return parent::isValid($code, $error);
    }

    public function display()
    {

    }

    public function getOptions()
    {
        return $this->options;
    }

}
