<?php

class sitemapSitemapConfig extends waSitemapConfig
{
	private $is_disabled;

	private $app_id;
	private $page;
	private $sub_sitemap;

	private $real_domain;

	private $sitemap_factory;
	private $general_settings;

	public function __construct()
	{
		parent::__construct();
		$this->real_domain = $this->routing->getDomain(null, true, false);

		$this->is_disabled = !class_exists('SitemapAppSystemConfig');

		if ($this->is_disabled)
		{
			return;
		}

		$this->app_id = waRequest::param('sitemap_app_id');
		$this->page = waRequest::param('sitemap_page');
		$this->sub_sitemap = waRequest::param('sitemap_sub_sitemap');

		$domain_name = $this->routing->getDomain(null, true);
		$this->sitemap_factory = new sitemapSitemapFactory(
			$domain_name,
			$this->routing->getDomain(null, true, false)
		);

		$settings_storage = new sitemapGeneralSettingsStorage();
		$this->general_settings = $settings_storage->getSettings(sitemapHelper::getDomainIdByName($domain_name));
	}

	public function display()
	{
		if ($this->is_disabled)
		{
			return;
		}

		$cache_mode_options = new sitemapSitemapCacheModeEnumOptions();

		$saved_root_sitemap_page = new sitemapSavedSitemapPage($this->domain, 'webasyst');
		$is_saved = $saved_root_sitemap_page->isSaved();
		if (waRequest::cookie('kor'))
		{
			//wa_dump($this->domain, $saved_root_sitemap_page);
		}
		if ($is_saved && $this->general_settings->sitemap_cache_mode == $cache_mode_options->BY_CRON)
		{
			$saved_sitemap_page = new sitemapSavedSitemapPage(
				$this->real_domain,
				$this->app_id,
				$this->sub_sitemap,
				$this->page
			);

			if (!$saved_sitemap_page->isSaved())
			{
				throw new waException('', 404);
			}

			$saved_sitemap_page->display();
		}
		else
		{
			$sitemap = $this->sitemap_factory->getForApp($this->app_id, $this->sub_sitemap);

			$sitemap->display($this->page);
		}
	}

	public function count()
	{
		return $this->is_disabled ? 0 : 1;
	}
}