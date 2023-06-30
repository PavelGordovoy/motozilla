<?php

abstract class sitemapSitemap implements sitemapISitemap
{
	private static $excluded_urls = null;

	private $sitemap_xml;
	private $url_in_sitemap_count = 0;

	protected $model;

	/*** @var waRouting */
	protected $routing;

	protected $domain;
	protected $real_domain;
	protected $domain_id;

	protected $general_settings;

	/** @var sitemapRobotsParser|null */
	protected $robots_parser;

	protected $sitemap_config;
	protected $routes;

	/** @var sitemapApplicationStructure|null */
	protected $structure;

	public function __construct($domain_name, $real_domain_name)
	{
		$this->model = new waModel();
		$this->routing = wa()->getRouting();

		$this->domain = $domain_name;
		$this->real_domain = $real_domain_name;
		$this->domain_id = sitemapHelper::getDomainIdByName($this->domain);

		if (!($this->domain_id > 0))
		{
			throw new sitemapException('invalid domain');
		}

		$this->sitemap_xml = new sitemapIndexSitemapXmlGenerator($this->real_domain);

		$settings_storage = new sitemapGeneralSettingsStorage();
		$this->general_settings = $settings_storage->getSettings($this->domain_id);

		$this->prepareExcludedUrls();

		$this->robots_parser = $this->general_settings->apply_robots_txt_filter
			? new sitemapRobotsParser($this->getRobotsTxt())
			: null;

		$app_id = $this->getAppId();
		$this->routes = $app_id == 'webasyst'
			? array()
			: $this->getRoutes();

		$this->structure = $this->getStructure();
	}

	abstract public function formSitemapDocument($page = null);

	abstract public function getAppId();

	/**
	 * @return sitemapApplicationStructure|null
	 */
	abstract protected function getStructure();

	protected function getSubSitemapType()
	{
		return null;
	}

	/**
	 * @param string $route_url
	 * @return sitemapSettingsAccess|null
	 */
	protected function getRouteSettings($route_url)
	{
		return null;
	}

	public function display($page = null)
	{
		$this->formSitemapDocument($page);
		$this->sendHeaders();
		$this->echoSitemapDocument();
	}

	public function count()
	{
		return 1;
	}

	public function urlIsExcluded($url)
	{
		if (
			$this->isUrlExcludedByExcludedList($url)
			|| ($this->robots_parser && $this->robots_parser->isDisallowed($url, '*'))
		)
		{
			return true;
		}

		return false;
	}

	protected function sendHeaders()
	{
		$system = waSystem::getInstance();
		$system->getResponse()->addHeader('Content-Type', 'application/xml; charset=UTF-8');
		$system->getResponse()->sendHeaders();
	}

	public function getSitemapDocumentContent()
	{
		return $this->sitemap_xml->generate();
	}

	protected function addSitemap($url, $lastmod, $check = true)
	{
		if ($check && $this->urlIsExcluded($url))
		{
			return;
		}

		$this->sitemap_xml->addElement($url, $lastmod);
		$this->url_in_sitemap_count++;
	}

	protected function getUrlInSitemapCount()
	{
		return $this->url_in_sitemap_count;
	}

	protected function getRoutes()
	{
		$routes = $this->routing->getRoutes($this->domain);
		foreach ($routes as $r_id => $r)
		{
			if (!isset($r['app']) || $r['app'] != $this->getAppId() || !empty($r['private']))
			{
				unset($routes[$r_id]);
			}
		}

		return $routes ? $routes : array();
	}

	private function echoSitemapDocument()
	{
		echo $this->getSitemapDocumentContent();
	}

	private function getRobotsTxt()
	{
		$root_robots_path = realpath('./robots.txt');
		if (is_string($root_robots_path) && file_exists($root_robots_path))
		{
			return file_get_contents($root_robots_path);
		}

		$path = wa()->getDataPath('data/'.$this->domain.'/', true, 'site').'robots.txt';

		$str = file_exists($path)
			? file_get_contents($path)
			: '';

		return $str;
	}

	private function prepareExcludedUrls()
	{
		if (is_array(self::$excluded_urls))
		{
			return;
		}

		self::$excluded_urls = array();

		foreach ($this->general_settings->excluded_urls as $excluded_url)
		{
			if (mb_substr($excluded_url, 0, 7) === 'http://')
			{
				self::$excluded_urls[$excluded_url] = true;
				self::$excluded_urls[str_replace('http://', 'https://', $excluded_url)] = true;
			}
			elseif (mb_substr($excluded_url, 0, 8) === 'https://')
			{
				self::$excluded_urls[$excluded_url] = true;
				self::$excluded_urls[str_replace('https://', 'http://', $excluded_url)] = true;
			}
			else
			{
				if (
					(mb_substr($excluded_url, 0, mb_strlen($this->domain)) === $this->domain)
					|| (mb_substr($excluded_url, 0, mb_strlen($this->real_domain)) === $this->real_domain)
				)
				{
					self::$excluded_urls['http://' . $excluded_url] = true;
					self::$excluded_urls['https://' . $excluded_url] = true;
				}
				else
				{
					self::$excluded_urls['http://' . $this->domain . '/' . ltrim($excluded_url, '/')] = true;
					self::$excluded_urls['https://' . $this->domain . '/' . ltrim($excluded_url, '/')] = true;
					if ($this->domain !== $this->real_domain)
					{
						self::$excluded_urls['http://' . $this->real_domain . '/' . ltrim($excluded_url, '/')] = true;
						self::$excluded_urls['https://' . $this->real_domain . '/' . ltrim($excluded_url, '/')] = true;
					}
				}
			}
		}
	}

	public function isUrlExcludedByExcludedList($url)
	{
		return array_key_exists($url, self::$excluded_urls);
	}
}