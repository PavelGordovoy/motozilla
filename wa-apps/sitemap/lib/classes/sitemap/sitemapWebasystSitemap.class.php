<?php

class sitemapWebasystSitemap extends sitemapSitemap
{
	public function getAppId()
	{
		return 'webasyst';
	}

	protected function getStructure()
	{
		return null;
	}

	public function formSitemapDocument($page = null)
	{
		$system = waSystem::getInstance();
		$apps = $system->getApps();
		$routes = $this->routing->getRoutes($this->domain);
		$domain_apps = array();
		foreach ($routes as $r)
		{
			if (isset($r['app']) && empty($r['private']) && isset($apps[$r['app']]))
			{
				if (!isset($domain_apps[$r['app']]))
				{
					$domain_apps[$r['app']] = $apps[$r['app']];
				}
			}
		}

		$sitemap_factory = new sitemapSitemapFactory($this->domain, $this->real_domain);
		$current_time = time();

		foreach ($domain_apps as $app_id => $app)
		{
			if ($app_id == 'sitemap')
			{
				continue;
			}

			try
			{
				$sitemap = $sitemap_factory->getForApp($app_id);
			}
			catch (Exception $e)
			{
				continue;
			}


			$count = $sitemap->count();
			for ($page = 1; $page <= $count; $page++)
			{
				$this->addSitemap(
					sitemapUrlHelper::getIndexSitemapAppUrl($this->real_domain, $app_id, null, $count > 1 ? $page : null),
					$current_time
				);
			}


			foreach ($sitemap_factory->getAppSubSitemaps($app_id) as $sub_sitemap_key => $sub_sitemap)
			{
				$count = $sub_sitemap->count();

				for ($page = 1; $page <= $count; $page++)
				{
					$this->addSitemap(
						sitemapUrlHelper::getIndexSitemapAppUrl($this->real_domain, $app_id, $sub_sitemap_key, $count > 1 ? $page : null),
						$current_time
					);
				}
			}
		}

		$sitemap_index_event = new sitemapSitemapIndexEvent($this->domain);
		foreach ($sitemap_index_event->trigger() as $url_params)
		{
			$this->addSitemap($url_params['url'], $url_params['lastmod']);
		}
	}
}