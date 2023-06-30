<?php

/**
 * Class sitemapGeneralSettings
 *
 * @property bool $show_hidden_categories
 * @property bool $show_hidden_products
 * @property bool $links_to_canonical
 * @property bool $add_image_sitemap_in_index
 * @property string[] $product_subpages
 * @property string $product_images_size
 */
class sitemapShopSettings extends sitemapSettingsAccess
{
	protected function getFields()
	{
		return array(
			'show_hidden_categories',
			'show_hidden_products',
			'links_to_canonical',
			'add_image_sitemap_in_index',
			'product_subpages',
			'product_images_size'
		);
	}
}