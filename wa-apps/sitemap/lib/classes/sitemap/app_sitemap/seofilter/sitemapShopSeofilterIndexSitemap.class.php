<?php

class sitemapShopSeofilterIndexSitemap extends sitemapSitemap
{
	private $pages;

	public function __construct($domain_name, $real_domain_name, $pages)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->pages = $pages;
	}

	public function formSitemapDocument($page = null)
	{
		if ($this->structure && !$this->structure->hasShownElements())
		{
			return;
		}

		for ($page = 1; $page <= $this->pages; $page++)
		{
			$url = sitemapUrlHelper::getIndexSitemapAppUrl($this->real_domain, $this->getAppId(), $this->getSubSitemapType(), $page);

			$this->addSitemap($url, date('c'));
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
}