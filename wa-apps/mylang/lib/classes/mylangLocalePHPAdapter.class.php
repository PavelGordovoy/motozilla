<?php
class mylangLocalePHPAdapter extends waLocalePHPAdapter
{
    /**
     * Array of domains and adapter objects and the paths to the po files
     * @var array
     */
    protected static $loaded_domains = [];
    protected static $locale;
    protected static $domain;
    protected static $cache = [];

    public function getMessages()
    {
        $cache = parent::$cache[parent::$locale]['mylang_custom'];
        return ifset($cache['messages'], []);
    }
}