<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformCaptchaController extends waCaptcha
{

    private $captcha_obj = null;

    public function __construct()
    {
        $this->captcha_obj = new multiformCaptcha();
    }

    public function getFactory($type = 'captcha')
    {
        static $classes = array();

        $factories = array(
            'captcha' => 'multiformCaptcha',
            'recaptcha' => 'multiformReCaptcha',
        );
        if (!isset($factories[$type])) {
            throw new waException('Unable to load type ' . $type);
        }

        if (!isset($classes[$type])) {
            $options = array();
            if ($type == 'recaptcha') {
                $config = $this->readCaptchaConfig();
                $options = $config[1];
            }
            $classes[$type] = new $factories[$type]($options);
        }
        $this->captcha_obj = $classes[$type];
        return $this->captcha_obj;
    }

    public function display()
    {
        $this->captcha_obj->display();
    }

}
