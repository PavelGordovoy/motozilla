<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 20.01.2020
 * Time: 12:33
 */

class mylangMicrosoftPluginTranslate extends mylangAutoTranslate
{
    private $headers = [];
    const API_URL = 'https://api.cognitive.microsofttranslator.com/translate?api-version=3.0';

    private function getKey()
    {
        if (!$this->apikey) {
            $asm = new waAppSettingsModel();
            $this->apikey = $asm->get('mylang.microsoft', 'api');
        }
        if (empty($this->apikey)) {
            self::$errors = _w('Api error. Check the token key and quota.');
            return false;
        }
        return $this->headers = [
            'Ocp-Apim-Subscription-Key' => $this->apikey,
        ];
    }

    public function translate($input)
    {
        if ($this->getKey()) {
            $this->cache = new mylangJsonCache($this->cachefile);
            return $this->translateBigText($input);
        }
        return false;
    }

    /**
     * @param array $arr
     * @param null $url
     * @return array|bool|void
     */
    public function multitran($arr, $url = null)
    {
        $options = [
            'format' => waNet::FORMAT_JSON,
            'timeout' => 10,
        ];
        $calls = [];
        $translateUrl = self::API_URL . '&to=' . $this->target . '&from=' . $this->source;
        waNet::multiQuery('mylang.bing');
        $net = new waNet($options, $this->headers);
        foreach ($arr as $k => $a) {
            if (strpos($a, '<') !== false || empty(trim($a))) {
                continue;
            }
            if ($tr = $this->cache->get($a)) {
                $arr[$k] = $tr;
            } else {
                try {
                    $calls[$k] = $net->query($translateUrl, json_encode([['text' => $a]]), waNet::METHOD_POST);
                } catch (waException $ex) {
                    return $this->handleApiException($net, $ex);
                }
            }
        }
        waNet::multiQuery('mylang.bing');
        foreach ($calls as $k => $call) {
            $tr = ifset($call, '0', 'translations', '0', 'text', false);
            $tr = $this->fixHtmlTags($tr);
            if (!empty($tr)) {
                $this->cache->set($arr[$k], $tr);
            }
            $arr[$k] = $tr;
            unset($call);
        }
        return $arr;
    }

    private function handleApiException($net, $ex)
    {
        $message = $ex->getMessage();
        if ($net) {
            $response = $net->getResponse();
            if (empty($response)) {
                $response = $net->getResponse(true);
            }
            $message .= "\n" . var_export(compact('response'), true);
        }
        $this->log($message);
        return null;
    }

    private function log($error)
    {
        waLog::dump($error, 'mylang/microsoft.log');
    }

    public function apiCode($code)
    {
        switch ($code) {
            case '400':
                $error = 'One of the query parameters is missing or not valid. Correct request parameters before retrying.';
                break;
            case '401':
                $error = 'The request could not be authenticated. Check that credentials are specified and valid.';
                break;
            case '403':
            case '404':
                $error = 'The request is not authorized. Check the details error message. This often indicates that all free translations provided with a trial subscription have been used up.';
                break;
            case '408':
                $error = 'The request could not be fulfilled because a resource is missing. Check the details error message. When using a custom category, this often indicates that the custom translation system is not yet available to serve requests. The request should be retried after a waiting period (e.g. 1 minute).';
                break;
            case '429':
                $error = 'The caller is sending too many requests.';
                break;
            case '500':
                $error = 'An unexpected error occurred. If the error persists, report it with: date and time of the failure, request identifier from response header X-RequestId, and client identifier from request header X-ClientTraceId.';
                break;
            case '503':
                $error = 'Server temporarily unavailable. Retry the request. If the error persists, report it with: date and time of the failure, request identifier from response header X-RequestId, and client identifier from request header X-ClientTraceId.';
                break;
            default:
                $error = _w('Unknown error. See logs.');
        }
        return $error;
    }

    /**
     * @return string
     * @throws waException
     */
    public static function getLink()
    {
        $text = _wp('Translated by'); //TODO
        $link = 'http://aka.ms/MicrosoftTranslatorAttribution';
        //
        return '<a href="' . $link . '" target="_blank">' . $text . '</a>';
    }

    /**
     * @inheritDoc
     */
    public function handleResponse($response)
    {

    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return self::$errors;
    }
}