<?php

class sitemapApplicationSettingsStorageFactory
{
	/**
	 * @param $app_id
	 * @return sitemapApplicationSettingsStorage
	 * @throws sitemapException
	 */
	public function getForApp($app_id)
	{
		if ($app_id == 'shop')
		{
			return new sitemapShopSettingsStorage();
		}

		throw new sitemapException("cant find settings storage for application [{$app_id}]");
	}
}