<?php

class sitemapStructureSettingsGetApplicationSettingsController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		$app_id = waRequest::get('app_id');
		$domain_id = waRequest::get('domain_id');

		if (
			!is_string($app_id) || !strlen($app_id)
			|| !wa_is_int($domain_id) || $domain_id < 0
		)
		{
			return;
		}

		$storage_factory = new sitemapApplicationSettingsStorageFactory();
		$domain_id = intval($domain_id);

		try
		{
			$storage = $storage_factory->getForApp($app_id);
		}
		catch (Exception $e)
		{
			return;
		}

		//$model = new waModel();
		//$domain = $model
		//	->query('SELECT IF(LENGTH(title), title, name) FROM site_domain WHERE id = :id', array('id' => $domain_id))
		//	->fetchField();
		//
		$settings = $storage->getSettings($domain_id, sitemapApplicationSettingsStorage::DEFAULT_ROUTE_URL);

		//$settings_assoc = $settings->getAssoc();
		//$settings_assoc['image_sitemap_url'] = $domain
		//	? $domain . '/sitemap-shop-' . sitemapSitemapFactory::SHOP_SUB_SITEMAP_PRODUCT_IMAGES . '.xml'
		//	: '';

		$this->response['settings'] = $settings->getAssoc();
		$this->response['success'] = true;
	}
}