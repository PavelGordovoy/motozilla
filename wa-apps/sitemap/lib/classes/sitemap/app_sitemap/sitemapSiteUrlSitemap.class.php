<?php

class sitemapSiteUrlSitemap extends sitemapUrlSitemap
{
	private $url_groups = array(
		sitemapSiteStructureStorage::ELEMENT_MAIN => array(),
		sitemapSiteStructureStorage::ELEMENT_PAGE => array(),
	);

	public function getAppId()
	{
		return 'site';
	}

	protected function getStructure()
	{
		$structure_storage = new sitemapSiteStructureStorage();

		return $structure_storage->get($this->domain_id, sitemapApplicationStructureStorage::DEFAULT_ROUTE_URL);
	}

	public function formSitemapDocument($page = null)
	{
		foreach ($this->routes as $route)
		{
			$this->collectRouteUrls($route);
		}

		foreach ($this->structure->elements as $element)
		{
			if (!$element->is_shown)
			{
				continue;
			}

			$this->addElementUrlsToDocument($element);
		}
	}

	private function addElementUrlsToDocument(sitemapApplicationStructureElementSettings $element)
	{
		if (!array_key_exists($element->element_id, $this->url_groups))
		{
			return;
		}

		foreach ($this->url_groups[$element->element_id] as $url_params)
		{
			list($url, $lastmod, $change_frequency, $priority) = $url_params;

			$this->addUrl(
				$url,
				$lastmod,
				$element->change_frequency === null ? $change_frequency : $element->change_frequency,
				$element->priority === null ? $priority : $element->priority
			);
		}
	}

	private function collectRouteUrls($route)
	{
		$sql = "
SELECT p.id, p.parent_id, p.name, p.title, p.full_url as url, p.create_datetime, p.update_datetime, pp.value AS priority
FROM site_page AS p
LEFT JOIN site_page_params AS pp
	ON p.id = pp.page_id AND pp.name = 'priority'
WHERE p.domain_id = i:domain_id AND p.route = s:route AND p.status = 1
ORDER BY sort
";
		$query_params = array(
			'domain_id' => $this->domain_id,
			'route' => $route['url']
		);

		$pages = $this->model->query($sql, $query_params)->fetchAll('id');

		$route_absolute_url = $this->routing->getUrlByRoute($route, $this->real_domain);
		foreach ($pages as $page)
		{
			$url_group = sitemapSiteStructureStorage::ELEMENT_PAGE;

			if (!empty($page['priority']) && $page['priority'] >= 0 && $page['priority'] <= 100)
			{
				$priority = (int)$page['priority'] / 100.0;
			}
			else
			{
				$priority = false;
			}

			if (!$page['url'])
			{
				if ($priority === false)
				{
					$priority = 1;
				}
				$change_frequency = $this->change_frequency->weekly;

				$url_group = sitemapSiteStructureStorage::ELEMENT_MAIN;
			}
			else
			{
				if ($priority === false)
				{
					$priority = $page['parent_id'] ? 0.2 : 0.6;
				}
				$change_frequency = $this->change_frequency->monthly;
			}

			$page_url = $route_absolute_url . $page['url'];
			if (strpos($page_url, '<') === false)
			{
				$this->url_groups[$url_group][] = array(
					$page_url,
					$page['update_datetime'],
					$change_frequency,
					$priority,
				);
			}
		}
	}
}