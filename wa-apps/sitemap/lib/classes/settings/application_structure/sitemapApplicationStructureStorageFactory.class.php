<?php

class sitemapApplicationStructureStorageFactory
{
	/**
	 * @param $app_id
	 * @return sitemapApplicationStructureStorage
	 * @throws sitemapException
	 */
	public function getForApp($app_id)
	{
		if ($app_id == 'shop')
		{
			return new sitemapShopStructureStorage();
		}
		elseif ($app_id == 'site')
		{
			return new sitemapSiteStructureStorage();
		}
		elseif ($app_id == 'blog')
		{
			return new sitemapBlogStructureStorage();
		}

		throw new sitemapException("no sitemap structure storage for app [{$app_id}]");
	}
}