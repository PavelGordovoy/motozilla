<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformCaptcha extends waPHPCaptcha
{

    public function getHtml($form_uid = 0, $absolute = false, $refresh = null)
    {
        $html = "<img alt='" . _wd('multiform', "Captcha image") . "' title='" . _wd('multiform', "Refresh") . "' src='" . wa()->getRootUrl() . 'multiform/captcha.php?v=' . uniqid(time()) . "&form_id=" . $form_uid . "'>
        <input type='text' id='wahtmlcontrol_fields_captcha_" . $form_uid . "' name='captcha-" . $form_uid . "' />";
        return $html;
    }

    public function isValid($code = null, &$error = '')
    {
        $form_id = waRequest::post("form_id", 0);
        if ($code === null) {
            $code = waRequest::post('captcha-' . $form_id);
        }
        $code = strtolower(trim($code));
        $captcha = wa()->getStorage()->get('captcha-' . $form_id);
        $app_id = $this->getAppId();
        if (isset($captcha[$app_id]) && $captcha[$app_id] === $code) {
            unset($captcha[$app_id]);
            wa()->getStorage()->set('captcha-' . $form_id, $captcha);
            return true;
        } else {
            $error = _ws('Invalid captcha');
            return false;
        }
    }

    public function display()
    {
        $form_id = waRequest::get("form_id", 0, waRequest::TYPE_INT);
        $code = $this->generateCode();
        wa()->getStorage()->set(array('captcha-' . $form_id, $this->getAppId()), $code);
        $this->responseImage($code);
    }

    private function getAppId()
    {
        $app_id = isset($this->options['app_id']) ? $this->options['app_id'] : wa()->getApp();
        if (!$app_id) {
            $app_id = 'webasyst';
        }
        return $app_id;
    }

    public function getOptions()
    {
        return $this->options;
    }

}
