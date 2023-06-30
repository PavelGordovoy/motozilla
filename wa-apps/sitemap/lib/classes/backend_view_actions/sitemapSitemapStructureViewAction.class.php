<?php

class sitemapSitemapStructureViewAction implements sitemapBackendViewAction
{
	const MENU_ID = 'structure';

	public function execute($request)
	{
		$applications_structure = $this->getApplicationsStructure();


		$routing = wa()->getRouting();

		$domains_routes = array();
		list($domain_names, $domain_ids) = $this->getDomains();

		$applications_structure_assoc = array();
		$applications_structure_assoc[sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID] = array();
		foreach ($applications_structure as $app_id => $application_structure)
		{
			$applications_structure_assoc[sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID][$app_id] = $application_structure->getAssoc();

			$app_routing = $routing->getByApp($app_id);

			foreach ($app_routing as $domain_name => $routes)
			{
				if (!array_key_exists($domain_name, $domain_ids))
				{
					continue;
				}
				$domain_routes = array();

				foreach ($routes as $route)
				{
					if (array_key_exists('url', $route) && is_string($route['url']) && strlen(trim($route['url'])))
					{
						$domain_routes[] = $route['url'];
					}
				}

				$domains_routes[$domain_ids[$domain_name]] = $domain_routes;
			}
		}
		unset($application_structure);


		$applications_sub_structures_assoc = array(
			sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID => array(),
		);

		$applications_sub_structures = $this->getApplicationsSubStructure();
		foreach ($applications_sub_structures as $app_id => $application_sub_structures)
		{
			if (!isset($applications_sub_structures_assoc[sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID][$app_id]))
			{
				$applications_sub_structures_assoc[sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID][$app_id] = array();
			}

			foreach ($application_sub_structures as $substructure_id => $application_sub_structure)
			{
				$applications_sub_structures_assoc[sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID][$app_id][$substructure_id] = $application_sub_structure->getAssoc();
			}
		}
		unset($application_sub_structures);

		/** @var shopConfig $shop_config */
		$shop_config = wa('shop')->getConfig();
		$image_sizes = array_values($shop_config->getImageSizes());

		return array(
			'applications_structure' => $applications_structure_assoc,
			'applications_sub_structures' => $applications_sub_structures_assoc,
			'domain_with_personal_settings' => $this->getDomainWithPersonalSettings(),

			'domains_routes' => $domains_routes,
			'domain_names' => $domain_names,
			'domain_ids' => $domain_ids,
			'applications_info' => $this->getApplicationInfo(array_keys($applications_structure)),
			'image_sizes' => $image_sizes,
		);
	}

	public function activeMenuElement()
	{
		return self::MENU_ID;
	}

	/**
	 * @return sitemapApplicationStructure[]
	 * @throws waException
	 */
	private function getApplicationsStructure()
	{
		$structure_storage_factory = new sitemapApplicationStructureStorageFactory();

		$applications_structure = array();

		foreach (wa()->getApps() as $app_id => $app_config)
		{
			try
			{
				$structure_storage = $structure_storage_factory->getForApp($app_id);
			}
			catch (waException $e)
			{
				continue;
			}

			$application_structure = $structure_storage->get(
				sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID,
				sitemapApplicationStructureStorage::DEFAULT_ROUTE_URL
			);
			$applications_structure[$app_id] = $application_structure;
		}

		return $applications_structure;
	}

	/**
	 * @return sitemapApplicationStructure[][]
	 * @throws waException
	 */
	private function getApplicationsSubStructure()
	{
		$structure_storage_factory = new sitemapApplicationStructureStorageFactory();

		$applications_sub_structures = array();

		foreach (wa()->getApps() as $app_id => $app_config)
		{
			try
			{
				$structure_storage = $structure_storage_factory->getForApp($app_id);
			}
			catch (waException $e)
			{
				continue;
			}

			$application_sub_structures = $structure_storage->getSubstructures(
				sitemapApplicationStructureStorage::DEFAULT_DOMAIN_ID,
				sitemapApplicationStructureStorage::DEFAULT_ROUTE_URL
			);

			$applications_sub_structures[$app_id] = $application_sub_structures;
		}

		return $applications_sub_structures;
	}

	private function getDomains()
	{
		$sql = '
SELECT `id`, `name`, `title`
FROM `site_domain`
';

		$domain_names = array();
		$domain_ids = array();

		$model = new waModel();
		foreach ($model->query($sql) as $row)
		{
			$domain_id = intval($row['id']);
			$domain_name = strlen(trim($row['title']))
				? $row['title']
				: $row['name'];

			$domain_names[$domain_id] = $domain_name;
			$domain_ids[$domain_name] = $domain_id;
		}

		return array($domain_names, $domain_ids);
	}

	private function getApplicationInfo($app_ids)
	{
		$info = array();
		$root_url = wa()->getRootUrl();

		foreach ($app_ids as $app_id)
		{
			$app_config = wa($app_id)->getConfig();

			$app_info = wa()->getAppInfo($app_id);

			$icon_url = array();
			if (array_key_exists('icon', $app_info))
			{
				foreach ($app_info['icon'] as $icon_size => $icon_relative_path)
				{
					$icon_url[$icon_size] = $root_url . $icon_relative_path;
				}
			}

			$app_info['icon_url'] = $icon_url;
			$app_info['plugins'] = $app_config->getInfo('plugins')
					? $app_config->getPlugins()
					: array();

			$info[$app_id] = $app_info;
		}

		return $info;
	}

	private function getDomainWithPersonalSettings()
	{
		$model = new sitemapApplicationStructureModel();

		$domains = array();
		foreach ($model->select('DISTINCT domain_id')->fetchAll() as $row)
		{
			$domains[] = intval($row['domain_id']);
		}

		return $domains;
	}
}