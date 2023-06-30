<?php

/**
 * Class sitemapGeneralSettings
 *
 * @property bool $is_enabled
 * @property bool $apply_robots_txt_filter
 * @property bool $use_cached_sitemap
 * @property string $sitemap_cache_mode
 * @property array $excluded_urls
 */
class sitemapGeneralSettings extends sitemapSettingsAccess
{
	protected function getFields()
	{
		return array(
			'apply_robots_txt_filter',
			'use_cached_sitemap',
			'excluded_urls',
			'sitemap_cache_mode',
		);
	}
}