<?php

abstract class sitemapApplicationSettingsStorage extends sitemapSettingsStorage
{
	private static $app_settings_instances = array();

	/**
	 * @param int $domain_id
	 * @param string $route_url
	 * @return sitemapSettingsAccess
	 */
	public function getSettings($domain_id, $route_url)
	{
		$app_id = $this->getAppId();
		if (!array_key_exists($app_id, self::$app_settings_instances))
		{
			self::$app_settings_instances[$app_id] = array();
		}

		$domain_route_key = $domain_id . '_' . $route_url;
		if (!array_key_exists($domain_route_key, self::$app_settings_instances[$app_id]))
		{
			self::$app_settings_instances[$app_id][$domain_route_key] = $this->fetch($domain_id, $route_url);
		}

		return self::$app_settings_instances[$app_id][$domain_route_key];
	}

	public function save($domain_id, $route_url, $settings_assoc)
	{
		$this->store($domain_id, $route_url, $settings_assoc);
	}

	abstract protected function fetch($domain_id, $route_url);

	protected function getForAccessible($domain_id, $route_url)
	{
		$stored_settings = $this->fetchRaw($domain_id, $route_url);
		$stored_domain_settings = $this->fetchRaw($domain_id, self::DEFAULT_ROUTE_URL);
		$stored_default_settings = $this->fetchRaw(self::DEFAULT_DOMAIN_ID, self::DEFAULT_ROUTE_URL);

		foreach ($stored_domain_settings as $name => $value)
		{
			if (!array_key_exists($name, $stored_settings))
			{
				$stored_settings[$name] = $value;
			}
		}

		foreach ($stored_default_settings as $name => $value)
		{
			if (!array_key_exists($name, $stored_settings))
			{
				$stored_settings[$name] = $value;
			}
		}

		return $this->prepareStorableForAccessible($stored_settings);
	}
}