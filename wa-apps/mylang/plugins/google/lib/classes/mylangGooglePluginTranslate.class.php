<?php
/**
 * Created by PhpStorm.
 * User: Demollc
 * Date: 20.01.2020
 * Time: 12:33
 */
require __DIR__.'/../vendors/autoload.php';

use Google\Cloud\Translate\V2\TranslateClient;

class mylangGooglePluginTranslate extends mylangAutoTranslate
{
    /* @var $client TranslateClient */
    private $client;

    /**
     * @return bool
     */
    private function getKey()
    {
        if (!$this->apikey) {
            $asm = new waAppSettingsModel();
            $this->apikey = $asm->get('mylang.google', 'api');
        }
        if (empty($this->apikey)) {
            self::$errors = _w('Api error. Check the token key and quota.');
            return false;
        }
        return true;
    }

    /**
     * @param $input
     * @return bool|mixed|string
     */
    public function translate($input)
    {
        if ($this->getKey()) {
            $this->client = new TranslateClient(
                [
                    'key' => $this->apikey
                ]
            );
            return $this->translateBigText($input, '');
        }
        return false;
    }

    public function multitran($arr, $baseurl = null)
    {
        $options = [
            'target' => $this->target,
            'source' => $this->source,
            'model'  => 'base',
        ];
        foreach ($arr as $k => $a) {
            if (strpos($a, '<') !== false || empty(trim($a))) {
                continue;
            }
            if ($tr = $this->cache->get($a)) {
                $arr[$k] = $tr;
            } else {
                try {
                    $calls[$k] = $this->client->translate($a, $options);
                } catch (Google\Cloud\Core\Exception\BadRequestException $e){
                    $this->handleError($e->getCode(), $e->getMessage());
                    return false;
                }
            }
        }
        if (!empty($calls)) {
            foreach ($calls as $k => $call) {
                $tr = $call['text'];
                $this->cache->set($arr[$k], $tr);
                $tr = $this->fixHtmlTags($tr);
                $arr[$k] = $tr;
            }
        }
        return $arr;
    }

    /**
     * @param $response
     * @return mixed
     */
    public function handleResponse($response)
    {
        $r = json_decode($response, true);
        return $r['data']['translations'][0]['translatedText'];
    }

    /**
     * @param $code
     * @param $response
     */
    public function handleError($code, $response)
    {
        $this->log($response);
        self::$errors = json_decode($response, 1)['error']['message'];
    }

    /**
     * @param $error
     */
    private function log($error)
    {
        waLog::dump($error, 'mylang/google.log');
    }

    /**
     * @return bool
     */
    public function getErrors()
    {
        return self::$errors;
    }

    /**
     * @return string
     * @throws waException
     */
    public static function getLink()
    {
        $text = '<img src="' . wa()->getAppStaticUrl('mylang/plugins/google/') . 'img/attr/greyscale-short.svg"/>';
        $link = _wp('https://translate.google.com');
        //
        return '<a href="' . $link . '" target="_blank">' . $text . '</a>';
    }
}