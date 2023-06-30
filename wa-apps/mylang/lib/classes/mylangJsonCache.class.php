<?php
require_once wa()->getAppPath('lib/vendors/autoload.php', 'mylang');
use Flintstone\Flintstone;
use Flintstone\Formatter\JsonFormatter;

class mylangJsonCache
{
    private $cachePath;
    private $cache;
    private $skip_cache;

    public function __construct($cachefile = 'mydb', $skip_cache = false)
    {
        $this->cachePath = wa()->getDataPath('cache/', false, 'mylang');
        $this->cache = new Flintstone($cachefile.'_json', ['dir' => $this->cachePath, 'formatter' => new JsonFormatter()]);
        $this->skip_cache = $skip_cache;
    }

    public function get($key)
    {
        if ($this->skip_cache) {
            return false;
        }
        return $this->cache->get(md5($key));
    }

    public function set($key, $value)
    {
        $this->cache->set(md5($key), $value);
    }

    public function getFiles()
    {
        return waFiles::listdir($this->cachePath);
    }
}
