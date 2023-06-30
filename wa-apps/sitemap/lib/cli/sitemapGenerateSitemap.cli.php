<?php

class sitemapGenerateSitemapCli extends waCliController
{
	private $model;
	private $routing;
	private $real_domains;
	private $all_domains;
	private $alias_domains;
	private $system;
	private $apps;

	private $domain_general_settings = array();
	private $general_settings;

	public function __construct()
	{
		$this->model = new waModel();
		$this->routing = wa()->getRouting();
		$this->real_domains = array();
		foreach ($this->routing->getDomains() as $real_domain)
		{
			$this->real_domains[$real_domain] = $real_domain;
		}

		$routing_config = include (wa()->getConfigPath() . '/routing.php');
		$this->all_domains = array();
		$this->alias_domains = array();
		foreach (array_keys($routing_config) as $domain)
		{
			$this->all_domains[$domain] = $domain;

			if (
				$this->routing->isAlias($domain)
				&& is_string($routing_config[$domain])
				&& isset($this->real_domains[$routing_config[$domain]])
			)
			{
				$this->alias_domains[$domain] = $routing_config[$domain];
			}
		}



		$this->system = waSystem::getInstance();
		$this->apps = $this->system->getApps();

		$settings_storage = new sitemapGeneralSettingsStorage();
		$this->general_settings = $settings_storage->getDefaultDomainSettings();
	}

	public function execute()
	{
		if (!$this->general_settings->is_enabled)
		{
			return;
		}

		$path = wa()->getDataPath('sitemaps', false, 'sitemap', true);
		waFiles::delete($path);

		$domain_index_elements = $this->generateAppSitemaps();

		$this->generateIndexSitemaps($domain_index_elements);
	}

	private function generateAppSitemaps()
	{
		$domain_index_elements = array();

		$cache_mode_options = new sitemapSitemapCacheModeEnumOptions();

		foreach ($this->all_domains as $domain)
		{
			$_SERVER['HTTP_HOST'] = $domain;
			$_SERVER['HTTPS'] = $this->isDomainHttps($domain) ? 'on' : 'off';

			$real_domain = isset($this->alias_domains[$domain])
				? $this->alias_domains[$domain]
				: $domain;

			$domain_general_settings = $this->getDomainGeneralSettings($real_domain);
			if (!$domain_general_settings)
			{
				continue;
			}

			if ($domain_general_settings->sitemap_cache_mode != $cache_mode_options->BY_CRON)
			{
				continue;
			}

			$routes = $this->routing->getRoutes($real_domain);
			$domain_apps = array();

			foreach ($routes as $r)
			{

                if(isset($r['type_id'])) {
                    waRequest::setParam('type_id', $r['type_id']);
                }

				if (isset($r['app']) && is_string($r['app']) && substr($r['app'], 0, 1) != ':' && empty($r['private']) && isset($this->apps[$r['app']]))
				{
					if (!isset($domain_apps[$r['app']]))
					{
						$domain_apps[$r['app']] = $this->apps[$r['app']];
					}
				}
			}

			$sitemap_factory = new sitemapSitemapFactory($domain, $real_domain);
			$domain_index_elements[$domain] = array();

			foreach ($domain_apps as $app_id => $app)
			{
				if ($app_id == 'sitemap')
				{
					continue;
				}

				$sitemaps = array();

				try
				{
					$sitemaps['main'] = $sitemap_factory->getForApp($app_id);

					foreach ($sitemap_factory->getAppSubSitemaps($app_id) as $sub_sitemap_type => $sub_sitemap)
					{
						$sitemaps[$sub_sitemap_type] = $sub_sitemap;
					}
				}
				catch (Exception $e)
				{
					continue;
				}

				foreach ($sitemaps as $sitemap_type => $_sitemap)
				{
					$sub_sitemap_type = $sitemap_type != 'main' ? $sitemap_type : null;
					$pages_count = $_sitemap->count();

					$domain_index_elements[$domain][] = array(
						'app_id' => $app_id,
						'sub_sitemap_type' => $sub_sitemap_type,
						'count' => $pages_count,
					);

					for ($page = 1; $page <= $pages_count; $page++)
					{
						$sitemap = $sitemap_factory->getForApp($app_id, $sub_sitemap_type);
						$sitemap->formSitemapDocument($page);

						$saved_shop_sitemap = new sitemapSavedSitemapPage($domain, $sitemap->getAppId(), $sub_sitemap_type, $page);

						$saved_shop_sitemap->saveContent($sitemap->getSitemapDocumentContent());
					}
				}
			}
		}

		return $domain_index_elements;
	}



	private function generateIndexSitemaps($domain_index_elements)
	{
		$current_time = time();

		foreach ($domain_index_elements as $domain => $index_elements)
		{
			$real_domain = isset($this->alias_domains[$domain])
				? $this->alias_domains[$domain]
				: $domain;

			$_SERVER['HTTP_HOST'] = $real_domain;
			$_SERVER['HTTPS'] = $this->isDomainHttps($real_domain) ? 'on' : 'off';

			$index_xml = new sitemapIndexSitemapXmlGenerator($domain);

			$sitemap_factory = new sitemapSitemapFactory($domain, $real_domain);

			$index_sitemap = $sitemap_factory->getForApp('webasyst');

			foreach ($index_elements as $index_element)
			{
				$app_id = $index_element['app_id'];
				$sub_sitemap_type = $index_element['sub_sitemap_type'];
				$pages_count = $index_element['count'];

				for ($page = 1; $page <= $pages_count; $page++)
				{
					$url = sitemapUrlHelper::getIndexSitemapAppUrl($domain, $app_id, $sub_sitemap_type, $pages_count > 1 ? $page : null);

					if (!$index_sitemap->urlIsExcluded($url))
					{
						$index_xml->addElement($url, $current_time);
					}
				}
			}

			$sitemap_index_event = new sitemapSitemapIndexEvent($domain);
			foreach ($sitemap_index_event->trigger() as $url_params)
			{
				$url = $url_params['url'];
				if (!$index_sitemap->urlIsExcluded($url))
				{
					$index_xml->addElement($url, $url_params['lastmod']);
				}
			}

			$saved_index_sitemap = new sitemapSavedSitemapPage($domain, 'webasyst');
			$saved_index_sitemap->saveContent($index_xml->generate());
		}
	}

	/**
	 * @param string $domain
	 * @return sitemapGeneralSettings|null
	 */
	private function getDomainGeneralSettings($domain)
	{
		if (!array_key_exists($domain, $this->domain_general_settings))
		{
			$settings_storage = new sitemapGeneralSettingsStorage();
			$domain_id = sitemapHelper::getDomainIdByName($domain);
			$this->domain_general_settings[$domain] = $domain_id > 0
				? $settings_storage->getSettings($domain_id)
				: null;
		}

		return $this->domain_general_settings[$domain];
	}

	private function isDomainHttps($domain)
	{
		if (strpos($domain, 'freshburg') !== false)
		{
			return true;
		}

		$domain_config_path = $this->getConfig()->getConfigPath('domains/' . $domain . '.php', true, 'site');

		if (!file_exists($domain_config_path))
		{
			return false;
		}

		$orig_domain_config = include($domain_config_path);

		return is_array($orig_domain_config)
			&& array_key_exists('ssl_all', $orig_domain_config)
			&& $orig_domain_config['ssl_all'];
	}
}