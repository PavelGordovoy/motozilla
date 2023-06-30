<?php

/**
 * Class sitemapShopSettingsStorage
 *
 * @method sitemapShopSettings getSettings($domain_id, $route_url)
 */
class sitemapShopSettingsStorage extends sitemapApplicationSettingsStorage
{
	const DEFAULT_PRODUCT_IMAGE_SIZE = '970';

	protected function fetch($domain_id, $route_url)
	{
		return new sitemapShopSettings($this->getForAccessible($domain_id, $route_url));
	}

	/**
	 * @return sitemapIDataFieldSpecification[]
	 */
	protected function accessSpecification()
	{
		$specifications = new sitemapDataFieldSpecificationFactory();

		return array(
			'show_hidden_categories' => $specifications->boolean(false),
			'show_hidden_products' => $specifications->boolean(false),
			'add_image_sitemap_in_index' => $specifications->boolean(true),
			'links_to_canonical' => $specifications->boolean(false),
			'product_subpages' => $specifications->jsonArray($specifications->string()),
			'product_images_size' => $specifications->string(self::DEFAULT_PRODUCT_IMAGE_SIZE),
		);
	}

	protected function getAppId()
	{
		return 'shop';
	}
}