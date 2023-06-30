<?php

abstract class mylangAutoTranslate
{
    public static $errors = false;
    protected $apikey;
    protected $cachefile;
    protected $source;
    protected $target;
    protected $skip_cache = false;
    protected $cache;

    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions($options)
    {
        $this->source = explode('_', $options['source'])[0];
        $this->target = explode('_', $options['target'])[0];
        $this->cachefile = strtolower($this->source . '-' . $this->target);
    }

    /**
     * @return array|bool
     * @throws waException
     */
    public function getProviders()
    {
        return wa('mylang')->getConfig()->getProviders();
    }

    /**
     * @param $text
     * @return mixed
     * Fix HTML tags after provider translate
     */
    public function fixHtmlTags($text)
    {
        $replace = [
            '< ' => '<',
            '> ' => '>',
            ' <' => '<',
            '>' => '>',
            '= ' => '=',
            ' =' => '=',
            '/ ' => '/'
        ];
        $text = str_ireplace(array_keys($replace), array_values($replace), $text);
        return $text;
    }

    abstract public function translate($text);

    protected function translateBigText($text, $url = null)
    {
        //TODO check 1 piece limit;
        $text = html_entity_decode($text);
        $r = preg_split('/(<[^>]*[^\/]>|<br \/>|\n\r)/i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $array_filter = [];
        foreach ($r as $key => $i) {
            if (strpos($i, '<') === false || !empty(trim($i))) {
                $array_filter[$key] = $i;
            }
        }
        $totrans = $array_filter;
        array_filter($totrans);
        $this->cache = new mylangJsonCache($this->cachefile, $this->skip_cache);
        $tr = $this->multitran($totrans, $url);
        if ($tr) {
            return implode('', $tr + $r);
        }
        return false;
    }

    /**
     * @param array $arr
     * @param string|null $baseurl
     * @return array|bool
     */
    public function multitran($arr, $baseurl = null)
    {
        $mc = JMathai\PhpMultiCurl\MultiCurl::getInstance();
        $calls = [];
        foreach ($arr as $k => $a) {
            if (strpos($a, '<') !== false || empty(trim($a))) {
                continue;
            }
            if ($tr = $this->cache->get($a)) {
                $arr[$k] = $tr;
            } else {
                $calls[$k] = $mc->addUrl($baseurl . rawurlencode($a));
            }
        }
        foreach ($calls as $k => $call) {
            if ($call->code != 200) {
                waLog::dump($call->url, 'mylang/net.log');
                $this->handleError($call->code, $call->response);
                return false;
            }
            $tr = $this->handleResponse($call->response);
            $this->cache->set($arr[$k], $tr);
            $tr = $this->fixHtmlTags($tr);
            $arr[$k] = $tr;
        }
        return $arr;
    }

    /**
     * @param $response
     * @return mixed
     */
    public function handleResponse($response)
    {
        return json_decode($response, true);
    }

    /**
     * @param $code
     * @param $response
     */
    public function handleError($code, $response)
    {
        $this->log($response);
        self::$errors = $this->apiCode($code);
    }

    /**
     * @param int $code
     * @return string
     */
    public function apiCode($code)
    {
        switch ($code) {
            case '401':
                $error = _w('Invalid API key');
                break;
            default:
                $error = _w('Unknown error. See logs.');
        }
        return $error;
    }
}
