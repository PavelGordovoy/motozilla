<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformReCaptcha extends waReCaptcha
{

    protected $options = array(
        'sitekey' => '',
        'secret' => ''
    );

    public function getHtml($form_uid = 0, $sitekey = '')
    {
        static $loaded = 0;

        $html = '';

        if (!$loaded) {
            $html .= <<<HTML
    <script>
        function onloadRecapMultiformCallback() {
            jQuery.each(jQuery(".multiform-wrap .multiform-gap-captcha.multiform-recaptcha"), function() {
                var form = jQuery(this).closest('.multiform-wrap'),
                    id = 'wahtmlcontrol_fields_captcha_' + form.attr('data-uid');
                grecaptcha.render(id, {
                  'sitekey' : $('#' + id).data('sitekey')
                });
            });
        }
        (function($) {
           $(function() {
                setTimeout(function() {
                    if (typeof grecaptcha == 'undefined') {
                        $.getScript('https://www.google.com/recaptcha/api.js?onload=onloadRecapMultiformCallback&render=explicit');
                    } else {
                        onloadRecapMultiformCallback();
                    }
                }, 500);
           });
        })(jQuery);        
    </script>
HTML;
            $loaded = 1;
        }
        $html .= "<div class='multiform-gap-value' data-sitekey='{$sitekey}' id='wahtmlcontrol_fields_captcha_{$form_uid}'></div>";
        return $html;
    }

    public function isValid($code = null, &$error = '', $secret = '')
    {
        if ($code === null) {
            $code = waRequest::post('g-recaptcha-response');
        }
        $handle = curl_init(self::SITE_VERIFY_URL);
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'secret' => $secret,
                'response' => $code,
                'remoteip' => waRequest::getIp(),
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
            CURLINFO_HEADER_OUT => false,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true
        );
        curl_setopt_array($handle, $options);
        $response = curl_exec($handle);
        curl_close($handle);
        if ($response) {
            $response = json_decode($response, true);
            if (isset($response['success']) && $response['success'] == true) {
                return true;
            } elseif (isset($response['error-codes'])) {
                $errors = array();
                foreach ($response['error-codes'] as $error_code) {
                    switch ($error_code) {
                        case 'missing-input-secret':
                            $errors[] = _ws('The secret parameter is missing.');
                            break;
                        case 'invalid-input-secret':
                            $errors[] = _ws('The secret parameter is invalid or malformed.');
                            break;
                        case 'missing-input-response':
                            $errors[] = _ws('The response parameter is missing.');
                            break;
                        case 'invalid-input-response':
                            $errors[] = _ws('The response parameter is invalid or malformed.');
                            break;
                        default:
                            $errors[] = $error_code;
                    }
                    $error = implode('<br>', $errors);
                }
            }
        }
        return false;
    }

}
