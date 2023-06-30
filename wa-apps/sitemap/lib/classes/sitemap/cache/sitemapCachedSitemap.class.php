<?php

class sitemapCachedSitemap extends sitemapSitemap
{
	/** @var sitemapSitemap */
	private $sitemap;

	private $cache_meta_cache;
	private $cache_meta;

	private $is_cached;

	public function __construct(sitemapSitemap $sitemap)
	{
		$this->sitemap = $sitemap;

		parent::__construct($sitemap->domain, $sitemap->real_domain);

		$this->cache_meta_cache = new sitemapDataSerializeCache(
			'cache_meta/' . $this->getAppId() . '_sitemap_meta',
			123,
			'sitemap'
		); // todo ttl
		$this->cache_meta = $this->cache_meta_cache->isCached()
			? $this->cache_meta_cache->get()
			: array();

		if ($this->cache_meta_cache->isCached() && $this->checkSettingsHash() && $this->sitemapCacheFilesExist())
		{
			$this->is_cached = true;
		}
		else
		{
			$this->is_cached = false;
		}
	}

	public function count()
	{
		return parent::count();
	}

	public function formSitemapDocument($page = null)
	{
		if (!$this->is_cached)
		{
			$this->sitemap->formSitemapDocument($page);
		}
	}

	public function getSitemapDocumentContent()
	{
		if ($this->is_cached)
		{
			$this->getCachedSitemapDocumentContent();
		}

		$content = parent::getSitemapDocumentContent();
		$this->cacheSitemapContent($content);


		return $content;
	}

	protected function getRouteSettings($route_url)
	{
		return $this->sitemap->getRouteSettings($route_url);
	}

	public function getAppId()
	{
		return $this->sitemap->getAppId();
	}

	protected function getStructure()
	{
		return $this->sitemap->getStructure();
	}

	private function checkSettingsHash()
	{
		if (!array_key_exists('settings_hash', $this->cache_meta))
		{
			return false;
		}

		$cached_hash = $this->cache_meta['settings_hash'];
		$current_settings_hash = $this->currentSettingsHash();

		return $current_settings_hash == $cached_hash;
	}

	private function accessToAssoc(sitemapPropertyAccess $settings)
	{
		$assoc = $settings->getAssoc();
		ksort($assoc);

		return $assoc;
	}

	private function currentSettingsHash()
	{
		$all_settings_assoc = array(
			$this->accessToAssoc($this->general_settings),
		);

		foreach ($this->routes as $route)
		{
			if (array_key_exists('url', $route))
			{
				$route_settings = $this->getRouteSettings($route['url']);

				if ($route_settings instanceof sitemapSettingsAccess)
				{
					$all_settings_assoc[] = $this->accessToAssoc($route_settings);
				}
			}
		}

		if ($this->structure instanceof sitemapApplicationStructure)
		{
			$all_settings_assoc[] = $this->accessToAssoc($this->structure);
		}

		return md5(serialize($all_settings_assoc));
	}

	private function sitemapCacheFilesExist()
	{
		return true; // todo
	}

	private function getCachedSitemapDocumentContent()
	{
		return ''; // todo
	}

	// todo
	private function cacheSitemapContent($content)
	{
	}
}