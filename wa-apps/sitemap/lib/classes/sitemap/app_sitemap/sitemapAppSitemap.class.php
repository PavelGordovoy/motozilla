<?php

class sitemapAppSitemap extends sitemapSitemap
{
	private $app_id;

	/** @var waSitemapConfig $sitemap */
	private $app_sitemap_config;

	public function __construct($domain_name, $real_domain_name, $app_id)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->app_id = $app_id;

		$path = waSystem::getInstance()->getAppPath('lib/config/'.$app_id.'SitemapConfig.class.php', $app_id);
		if (!file_exists($path))
		{
			throw new waException('App don\'t have sitemap', 404);
		}

		include_once($path);

		$class_name = $app_id . 'SitemapConfig';
		$this->app_sitemap_config = new $class_name;
	}

	public function display($page = null)
	{
		wa($this->app_id, true);

		$this->app_sitemap_config->display(wa_is_int($page) && $page > 1 ? intval($page) : 1);
	}

	public function count()
	{
		return $this->app_sitemap_config->count();
	}

	public function formSitemapDocument($page = null)
	{
	}

	public function getAppId()
	{
		return $this->app_id;
	}

	protected function getStructure()
	{
		return null;
	}
}