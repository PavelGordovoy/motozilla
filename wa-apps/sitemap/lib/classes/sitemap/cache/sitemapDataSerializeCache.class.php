<?php

class sitemapDataSerializeCache extends waSerializeCache
{
	private $key_path;
	private $key_name;

	public function __construct($key, $ttl = -1, $app_id = null)
	{
		parent::__construct($key, $ttl, $app_id);

		$key_parts = explode('/', $this->key);

		$this->key_name = array_pop($key_parts);
		$this->key_path = implode('/', $key_parts);
	}

	protected function getFilePath()
	{
		$dir_path = wa()->getDataPath('cache/' . $this->key_path, false, 'sitemap', true);

		return $dir_path . $this->key_name . '.php';
	}
}