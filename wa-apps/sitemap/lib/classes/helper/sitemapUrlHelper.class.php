<?php

class sitemapUrlHelper
{
	public static function getRootUrl($domain, $absolute = false)
	{
		$result = waRouting::getDomainUrl($domain, $absolute);
		if ($absolute) {
			if (parse_url('http://'.$domain, PHP_URL_HOST) == waRequest::server('HTTP_HOST')) {
				$https = waRequest::isHttps();
			} else {
				$https = false;
			}
			$result = 'http'.($https ? 's' : '').'://'.$result;
		}

		return preg_replace('/<url.*?>/i', '', $result);
	}

	public static function getIndexSitemapAppUrl($domain, $app_id, $app_sub_sitemap = null, $page = null)
	{
		$sitemap_parts = array('sitemap', $app_id);

		if (is_string($app_sub_sitemap) && strlen($app_sub_sitemap) > 0)
		{
			$sitemap_parts[] = $app_sub_sitemap;
		}

		if (wa_is_int($page) && $page > 0)
		{
			$sitemap_parts[] = $page;
		}

		return self::getRootUrl($domain, true) . '/' . implode('-', $sitemap_parts) . '.xml';
	}
}