<?php

class sitemapSitemapIndexEvent
{
	private $domain;

	const EVENT_NAME_SITEMAP_INDEX = 'app_sitemap_index_sitemap';

	public function __construct($domain)
	{
		$this->domain = $domain;
	}

	public function trigger($route = null)
	{
		$routes = array();

		if ($route !== null)
		{
			if (is_array($route) && array_key_exists('app', $route))
			{
				$routes[] = $route;
			}
		}
		else
		{
			$routes = wa()->getRouting()->getRoutes($this->domain);
		}

		$urls_params = array();

		foreach ($routes as $route)
		{
			if (!is_array($route) || !array_key_exists('app', $route) || !is_string($route['app']) || substr($route['app'], 0, 1) == ':')
			{
				continue;
			}

			$app_id = $route['app'];
			$route['domain'] = $this->domain;

			$event_params = array(
				'route' => $route,
				'domain' => $this->domain,
			);

			try
			{
				$system = wa($app_id);
			}
			catch (Exception $e)
			{
				$system = null;
			}

			if (!$system)
			{
				continue;
			}

			$index_sitemaps = $system->event(array($app_id, self::EVENT_NAME_SITEMAP_INDEX), $event_params);

			if (!is_array($index_sitemaps) || count($index_sitemaps) == 0)
			{
				continue;
			}

			foreach ($index_sitemaps as $plugin_key => $_urls_params)
			{
				if ($plugin_key == 'seofilter-plugin')
				{
					continue;
				}

				if(is_array($_urls_params)) {
                    foreach ($_urls_params as $url_params) {
                        $urls_params[] = array(
                            'url' => $url_params['url'],
                            'lastmod' => isset($url_params['lastmod']) ? $url_params['lastmod'] : date('c'),
                        );
                    }
                }
			}
		}

		return $urls_params;
	}
}