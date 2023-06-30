<?php

class sitemapGeneralSettingsStorage extends sitemapSettingsStorage
{
	private static $settings_instances = array();

	/**
	 * @param int $domain_id
	 * @return sitemapGeneralSettings
	 */
	public function getSettings($domain_id)
	{
		if (!array_key_exists($domain_id, self::$settings_instances))
		{
			self::$settings_instances[$domain_id] = $this->get($domain_id);
		}

		return self::$settings_instances[$domain_id];
	}

	public function saveDomainSettings($domain_id, array $accessible_assoc)
	{
		$this->store($domain_id, '', $accessible_assoc);
	}

	public function getModifiedDomainIds()
	{
		$ids = array();

		$query = $this->settings_model
			->select('DISTINCT domain_id')
			->where('app_id = :app_id', array('app_id' => $this->getAppId()))
			->query();

		foreach ($query as $row)
		{
			$ids[] = intval($row['domain_id']);
		}

		return $ids;
	}

	public function getDefaultDomainSettings()
	{
		$settings = new sitemapGeneralSettings($this->getForAccessible(self::DEFAULT_DOMAIN_ID, ''));

		$config_patcher = new sitemapSystemConfigPatcher();
		$settings->is_enabled = $config_patcher->isPatched();

		return $settings;
	}

	public function getDomainSettings($domain_id)
	{
		$settings = new sitemapGeneralSettings();

		$domain_settings_raw = $this->fetchRaw($domain_id, '');

		foreach ($domain_settings_raw as $name => $raw_value)
		{
			$specification = $this->getFieldSpecification($name);
			if (!$specification)
			{
				continue;
			}

			$settings->$name = $specification->toAccessible($raw_value);
		}

		return $settings;
	}

	protected function get($domain_id)
	{
		$settings = $this->getDefaultDomainSettings();

		if ($domain_id == self::DEFAULT_DOMAIN_ID)
		{
			return $settings;
		}


		$domain_settings_raw = $this->fetchRaw($domain_id, '');

		foreach ($domain_settings_raw as $name => $raw_value)
		{
			$specification = $this->getFieldSpecification($name);
			if (!$specification)
			{
				continue;
			}

			$settings->$name = $specification->toAccessible($raw_value);
		}

		return $settings;
	}

	protected function accessSpecification()
	{
		$specifications = new sitemapDataFieldSpecificationFactory();

		$cache_mode_options = new sitemapSitemapCacheModeEnumOptions();

		return array(
			'apply_robots_txt_filter' => $specifications->boolean(false),
			'use_cached_sitemap' => $specifications->boolean(false),
			'excluded_urls' => $specifications->jsonArray($specifications->string('')),
			'sitemap_cache_mode' => $specifications->enum($cache_mode_options, $cache_mode_options->NONE),
		);
	}

	protected function getAppId()
	{
		return 'sitemap';
	}
}