<?php

class sitemapShopSeofilterSitemap extends sitemapMixedSitemap
{
	/** @var shopConfig */
	private $shop_config;
	private $seofilter_settings;

	/** @var sitemapSitemap */
	private $sitemap;

	public function __construct($domain_name, $real_domain_name)
	{
		wa('shop');

		parent::__construct($domain_name, $real_domain_name);

		$this->shop_config = waSystem::getInstance('shop')->getConfig();
		$this->seofilter_settings = shopSeofilterBasicSettingsModel::getSettings();
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
		$structure_storage = new sitemapShopStructureStorage();
		$structure = $structure_storage->getSubstructure(
			$this->domain_id,
			sitemapShopStructureStorage::DEFAULT_ROUTE_URL,
			sitemapShopStructureStorage::SUBSTRUCTURE_SEOFILTER
		);

		return $structure->hasShownElements()
			? parent::count()
			: 0;
	}

	protected function pagesCount()
	{
		$structure_storage = new sitemapShopStructureStorage();

		$structure = $structure_storage->getSubstructure(
			$this->domain_id,
			sitemapShopStructureStorage::DEFAULT_ROUTE_URL,
			sitemapShopStructureStorage::SUBSTRUCTURE_SEOFILTER
		);

		if (!$structure)
		{
			throw new waException('Отдельный sitemap SEO-фильтра выключен (ссылки выводятся в sitemap приложения "Магазин")', 404);
		}

		if (!$structure->hasShownElements())
		{
			return 0;
		}

		$count = 0;
		foreach ($this->routes as $route)
		{
			$count += $this->countByRoute($route);
		}

		$limit = shopSeofilterSitemapCachedSitemap::LIMIT;

		return ceil($count / $limit - 1e-6);
	}

	private function getSeofilterSitemap($route)
	{
		if (isset($route['currency']) && strlen($route['currency']))
		{
			$currency = $route['currency'];
		}
		else
		{
			$currency = $this->shop_config->getCurrency();
		}

		$route['domain'] = $this->real_domain;

		return new shopSeofilterSitemapCachedSitemap($route, $currency, $this->seofilter_settings->consider_category_filters);
	}

	private function countByRoute($route)
	{
		$this->routing->setRoute($route);

		if (method_exists('shopSeofilterSitemapCachedSitemap', 'countLinks'))
		{
			$sitemap = $this->getSeofilterSitemap($route);

			return $sitemap->countLinks();
		}

		$storefront = $this->real_domain . '/' . $route['url'];
		$in_stock_only = array_key_exists('drop_out_of_stock', $route) && $route['drop_out_of_stock'] == 2;

		$cache_model = new shopSeofilterSitemapCacheModel();
		$cache_by_category = $cache_model->getByStorefrontQuery(
			$storefront,
			$in_stock_only,
			$this->seofilter_settings->consider_category_filters
		);

		$count = 0;
		foreach ($cache_by_category as $data)
		{
			$ids = explode(',', $data['filter_ids']);
			$count += count($ids);
		}

		return $count;
	}

	public function display($page = null)
	{
		$sitemap = $this->getSitemap($page);

		$sitemap->display($page);
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
					$sitemap = new sitemapShopSeofilterIndexSitemap($this->domain, $this->real_domain, $pages_count);
				}
			}

			if ($sitemap === null)
			{
				$sitemap = new sitemapShopSeofilterUrlSitemap($this->domain, $this->real_domain);
			}

			$this->sitemap = $sitemap;
		}

		return $this->sitemap;
	}
}