<?php

class sitemapGeneralSettingsSaveController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		$state_json = waRequest::post('state');
		$state = json_decode($state_json, true);

		if (!is_array($state))
		{
			return;
		}
		$domains_settings = $state['domain_settings'];

		$this->saveApplicationSettings($state);
		$this->saveDomainSettings($domains_settings);

		$this->response['success'] = true;
	}

	private function saveApplicationSettings($state)
	{
		$is_enabled = !!$state['is_enabled'];

		$app_installer = new sitemapSystemConfigPatcher();

		if ($app_installer->isPatched() != $is_enabled)
		{
			$is_enabled
				? $app_installer->patch()
				: $app_installer->restore();
		}


		$excluded_urls = preg_split('/\r?\n/', $state['excluded_urls_raw_text']);

		$storage = new sitemapGeneralSettingsStorage();
		$settings = $storage->getSettings(sitemapGeneralSettingsStorage::DEFAULT_DOMAIN_ID);

		$settings->excluded_urls = array_filter(array_map('trim', $excluded_urls));
		$settings->sitemap_cache_mode = $state['sitemap_cache_mode'];

		$storage->saveDomainSettings(sitemapGeneralSettingsStorage::DEFAULT_DOMAIN_ID, $settings->getAssoc());
	}

	/**
	 * @param $domains_settings
	 */
	protected function saveDomainSettings($domains_settings)
	{
		$storage = new sitemapGeneralSettingsStorage();

		foreach ($domains_settings as $domain_id => $domain_settings_assoc)
		{
			unset($domain_settings_assoc['sitemap_cache_mode']);
			//$domain_settings = new sitemapGeneralSettings($domain_settings_assoc);

			$storage->saveDomainSettings($domain_id, $domain_settings_assoc);
		}
	}
}