<?php

/**
 * Class sitemapSitemapCacheModeEnumOptions
 *
 * @property-read string $NONE
 * @property-read string $AUTO
 * @property-read string $BY_CRON
 */
class sitemapSitemapCacheModeEnumOptions extends sitemapEnumOptions
{
	protected function getOptionValues()
	{
		return array(
			'NONE',
			'AUTO',
			'BY_CRON',
		);
	}
}