<?php

/**
 * Class mylangCache
 */
class mylangCache
{
    protected $cache;
    
    public function __construct()
    {
        if (!$this->cache) {
            $this->cache = wa('mylang')->getCache();
            if (!$this->cache) {
                //fallback to file
                $this->cache = new waCache(new waFileCacheAdapter([]), 'mylang');
            }
        }
    }

    /**
     * @return waCache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param $key
     * @param $value
     * @param string $ttl
     * @param null $group
     * @return mixed
     */
    public function set($key, $value, $ttl = '3600', $group = null)
    {
        return $this->cache->set($key, $value, $ttl, $group);
    }

    /**
     * @param $key
     * @param null $group
     * @return mixed
     */
    public function get($key, $group = null)
    {
        return $this->cache->get($key, $group);
    }

    /**
     * @param $key
     * @param null $group
     * @return bool
     */
    public function isCached($key, $group = null)
    {
        return $this->get($key, $group) !== null;
    }
}
