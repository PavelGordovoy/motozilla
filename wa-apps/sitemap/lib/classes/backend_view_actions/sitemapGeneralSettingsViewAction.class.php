<?php

class sitemapGeneralSettingsViewAction implements sitemapBackendViewAction
{
	const MENU_ID = 'general_settings';

	public function execute($request)
	{
		$settings_storage = new sitemapGeneralSettingsStorage();
		$general_settings = $settings_storage->getSettings(sitemapGeneralSettingsStorage::DEFAULT_DOMAIN_ID);

		$domain_settings_assoc = $general_settings->getAssoc();
		unset($domain_settings_assoc['is_enabled']);

		return array(
			'general_settings' => array(
				'is_enabled' => $general_settings->is_enabled,
				'sitemap_cache_mode' => $general_settings->sitemap_cache_mode,
				'excluded_urls' => $general_settings->excluded_urls,
				'domain_settings' => array(
					sitemapGeneralSettingsStorage::DEFAULT_DOMAIN_ID => $domain_settings_assoc,
				),
			),

			'domain_names' => $this->getDomainNames(),
			'modified_domain_ids' => $settings_storage->getModifiedDomainIds(),
			'cli_path' => wa()->getConfig()->getRootPath() . DIRECTORY_SEPARATOR . 'cli.php',
		);
	}

	public function activeMenuElement()
	{
		return self::MENU_ID;
	}

	private function getDomainNames()
	{
		$sql = '
SELECT `id`, `name`, `title`
FROM `site_domain`
';

		$domain_names = array();
		$model = new waModel();
		foreach ($model->query($sql) as $row)
		{
			$domain_names[$row['id']] = strlen(trim($row['title']))
				? $row['title']
				: $row['name'];
		}

		return $domain_names;
	}
}