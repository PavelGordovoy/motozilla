<?php

class sitemapShopProductImagesSitemap extends sitemapMixedSitemap
{
	/** @var sitemapSitemap */
	private $sitemap;

	private $route;

	public function __construct($domain_name, $real_domain_name)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->route = null;
		foreach (array_reverse($this->routes) as $route)
		{
			$this->route = $route;

			break;
		}
	}

	public function formSitemapDocument($page = null)
	{
		$sitemap = $this->getSitemap($page);

		$sitemap->formSitemapDocument($page);
	}

	public function getSitemapDocumentContent()
	{
		$sitemap = $this->getSitemap();

		return $sitemap->getSitemapDocumentContent();
	}

	public function getAppId()
	{
		return 'shop';
	}

	public function count()
	{
		$sitemap = $this->getSitemap();

		return $sitemap->structure->hasShownElements() ? 1 : 0;
	}

	public function display($page = null)
	{
		$sitemap = $this->getSitemap($page);

		$sitemap->display($page);
	}

	protected function pagesCount()
	{
		$sitemap = new sitemapShopProductImagesUrlSitemap($this->domain, $this->real_domain, $this->route);

		return $sitemap->count();
	}

	private function getSitemap($page = null)
	{
		if ($this->sitemap === null)
		{
			$sitemap = null;

			if ($page === null)
			{
				$pages_count = $this->pagesCount();

				if ($pages_count > 1)
				{
					$sitemap = new sitemapShopProductImagesIndexSitemap($this->domain, $this->real_domain, $pages_count);
				}
			}

			if ($sitemap === null)
			{
				$sitemap = new sitemapShopProductImagesUrlSitemap($this->domain, $this->real_domain, $this->route);
			}

			$this->sitemap = $sitemap;
		}

		return $this->sitemap;
	}
}