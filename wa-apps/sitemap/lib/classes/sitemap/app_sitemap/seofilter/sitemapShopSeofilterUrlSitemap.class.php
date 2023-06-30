<?php

class sitemapShopSeofilterUrlSitemap extends sitemapUrlSitemap
{
	/** @var shopConfig */
	private $shop_config;
	private $seofilter_settings;

	public function __construct($domain_name, $real_domain_name)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->shop_config = waSystem::getInstance('shop')->getConfig();
		$this->seofilter_settings = shopSeofilterBasicSettingsModel::getSettings();
	}

	public function formSitemapDocument($page = null)
	{
		if ($this->structure && !$this->structure->hasShownElements())
		{
			return;
		}

		$seofilter_element = $this->getSeofilterElement();

		$page = $page ? $page : 1;

		$limit = shopSeofilterSitemapCachedSitemap::LIMIT;
		$offset = $limit * ($page - 1);

		foreach ($this->routes as $route)
		{
			$this->routing->setRoute($route, $this->real_domain);
			$sitemap = $this->getSeofilterSitemap($route);

			foreach ($sitemap->generate(shopSeofilterISitemap::ALL_URLS) as $url_params)
			{
				if ($this->getUrlInXmlCount() > $limit)
				{
					break 2;
				}

				$url = $url_params['loc'];

				if ($this->urlIsExcluded($url))
				{
					continue;
				}

				if ($offset > 0)
				{
					$offset--;

					continue;
				}

				$this->addUrl(
					$url,
					$url_params['lastmod'],
					$seofilter_element->change_frequency === null ? $url_params['changefreq'] : $seofilter_element->change_frequency,
					$seofilter_element->priority === null ? $url_params['priority'] : $seofilter_element->priority
				);
			}
		}
	}

	public function getAppId()
	{
		return 'shop';
	}

	protected function getSubSitemapType()
	{
		return sitemapSitemapFactory::SHOP_SUB_SITEMAP_SEOFILTER;
	}

	protected function getStructure()
	{
		$structure_storage = new sitemapShopStructureStorage();

		return $structure_storage->getSubstructure(
			$this->domain_id,
			sitemapShopStructureStorage::DEFAULT_ROUTE_URL,
			sitemapShopStructureStorage::SUBSTRUCTURE_SEOFILTER
		);
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

	/**
	 * @return sitemapApplicationStructureElementSettings
	 */
	private function getSeofilterElement()
	{
		$default_element = new sitemapApplicationStructureElementSettings();
		$default_element->element_id = sitemapShopStructureStorage::ELEMENT_PLUGIN;
		$default_element->plugin_id = sitemapShopStructureStorage::PLUGIN_SEOFILTER;
		$default_element->is_shown = true;

		if (!$this->structure)
		{
			return $default_element;
		}

		foreach ($this->structure->elements as $element)
		{
			if ($element->element_id == sitemapShopStructureStorage::ELEMENT_PLUGIN && $element->plugin_id == sitemapShopStructureStorage::PLUGIN_SEOFILTER)
			{
				return $element;
			}
		}

		return $default_element;
	}
}