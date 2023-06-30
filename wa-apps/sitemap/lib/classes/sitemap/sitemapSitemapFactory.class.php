<?php

class sitemapSitemapFactory
{
	const SHOP_SUB_SITEMAP_PRODUCT_IMAGES = 'product_images';
	const SHOP_SUB_SITEMAP_SEOFILTER = 'filter';

	private $domain_name;
	private $real_domain_name;

	private $domain_id;

	public function __construct($domain_name, $real_domain_name)
	{
		$this->domain_name = $domain_name;
		$this->real_domain_name = $real_domain_name;

		$this->domain_id = sitemapHelper::getDomainIdByName($domain_name);
	}

	/**
	 * @param string $app_id
	 * @param string $sub_sitemap
	 * @return sitemapSitemap
	 * @throws sitemapException
	 * @throws waException
	 */
	public function getForApp($app_id, $sub_sitemap = null)
	{
		if ($app_id == 'webasyst')
		{
			return new sitemapWebasystSitemap($this->domain_name, $this->real_domain_name);
		}
		elseif ($app_id == 'shop')
		{
			if ($sub_sitemap === null)
			{
				return new sitemapShopUrlSitemap($this->domain_name, $this->real_domain_name);
			}
			elseif ($sub_sitemap == self::SHOP_SUB_SITEMAP_PRODUCT_IMAGES)
			{
				return new sitemapShopProductImagesSitemap($this->domain_name, $this->real_domain_name);
			}
			elseif ($sub_sitemap == self::SHOP_SUB_SITEMAP_SEOFILTER)
			{
				return new sitemapShopSeofilterSitemap($this->domain_name, $this->real_domain_name);
			}
		}
		elseif ($app_id == 'site')
		{
			if ($sub_sitemap === null)
			{
				return new sitemapSiteUrlSitemap($this->domain_name, $this->real_domain_name);
			}
		}
		elseif ($app_id == 'blog')
		{
			if ($sub_sitemap === null)
			{
				return new sitemapBlogUrlSitemap($this->domain_name, $this->real_domain_name);
			}
		}
		else
		{
			return new sitemapAppSitemap($this->domain_name, $this->real_domain_name, $app_id);
		}

		throw new waException();

		//$class_names = array(
		//	'sitemap' . ucfirst($app_id) . 'Sitemap',
		//	'sitemap' . ucfirst($app_id) . 'UrlSitemap',
		//);
		//
		//foreach ($class_names as $class_name)
		//{
		//	if (class_exists($class_name))
		//	{
		//		return new $class_name;
		//	}
		//}
		//
		//return new sitemapAppSitemap($app_id);
	}

	/**
	 * @param string $app_id
	 * @return sitemapSitemap[]
	 * @throws sitemapException
	 * @throws waException
	 */
	public function getAppSubSitemaps($app_id)
	{
		$app_settings_storage_factory = new sitemapApplicationSettingsStorageFactory();
		try
		{
			$app_settings_storage = $app_settings_storage_factory->getForApp($app_id);
		}
		catch (sitemapException $e)
		{
			$app_settings_storage = null;
		}

		if ($app_id == 'shop')
		{
			$sub_sitemaps = array(
				//self::SHOP_SUB_SITEMAP_SEOFILTER => // todo
			);

			if ($app_settings_storage)
			{
				/** @var sitemapShopSettings $app_settings */
				$app_settings = $app_settings_storage->getSettings($this->domain_id, sitemapApplicationSettingsStorage::DEFAULT_ROUTE_URL);
				if ($app_settings->add_image_sitemap_in_index)
				{
					$sub_sitemaps[self::SHOP_SUB_SITEMAP_PRODUCT_IMAGES] = $this->getForApp($app_id, self::SHOP_SUB_SITEMAP_PRODUCT_IMAGES);
				}
			}

			if (sitemapHelper::isSeofilterPluginEnabled() && $seofilter_settings = sitemapHelper::getSeofilterSettings())
			{
				if (!$seofilter_settings->use_sitemap_hook)
				{
					$sub_sitemaps[self::SHOP_SUB_SITEMAP_SEOFILTER] = $this->getForApp($app_id, self::SHOP_SUB_SITEMAP_SEOFILTER);
				}
			}

			return $sub_sitemaps;
		}

		return array();
	}
}