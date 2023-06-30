<?php

class sitemapBlogUrlSitemap extends sitemapUrlSitemap
{
	private $blog_post_urls = array();
	private $main_page_route_lastmod = array();

	public function getAppId()
	{
		return 'blog';
	}

	protected function getStructure()
	{
		$structure_storage = new sitemapBlogStructureStorage();

		return $structure_storage->get($this->domain_id, sitemapApplicationStructureStorage::DEFAULT_ROUTE_URL);
	}

	public function formSitemapDocument($page = null)
	{
		wa('blog');

		foreach ($this->routes as $route)
		{
			$this->routing->setRoute($route);

			$this->collectPostUrls($route);

			foreach ($this->structure->elements as $element)
			{
				if (!$element->is_shown)
				{
					continue;
				}

				if ($element->element_id == sitemapBlogStructureStorage::ELEMENT_MAIN)
				{
					$this->addMainUrlToDocument($route, $element);
				}
				elseif ($element->element_id == sitemapBlogStructureStorage::ELEMENT_POST)
				{
					$this->addPostUrlsToDocument($route, $element);
				}
				elseif ($element->element_id == sitemapBlogStructureStorage::ELEMENT_PAGE)
				{
					$this->addPageUrlsToDocument($route, $element);
				}
			}
		}
	}

	private function addMainUrlToDocument($route, sitemapApplicationStructureElementSettings $element)
	{
		$this->addUrl(
			wa()->getRouteUrl('blog' . "/frontend", array(), true, $this->real_domain),
			$this->main_page_route_lastmod[$route['url']],
			$element->change_frequency === null ? $this->change_frequency->daily : $element->change_frequency,
			$element->priority === null ? 1 : $element->priority
		);
	}

	private function addPostUrlsToDocument($route, sitemapApplicationStructureElementSettings $element)
	{
		$route_url = $route['url'];

		foreach ($this->blog_post_urls[$route_url] as $url_params)
		{
			list($url, $lastmod) = $url_params;

			$this->addUrl(
				$url,
				$lastmod,
				$element->change_frequency === null ? $this->change_frequency->monthly : $element->change_frequency,
				$element->priority === null ? 0.5 : $element->priority
			);
		}
	}

	private function addPageUrlsToDocument($route, sitemapApplicationStructureElementSettings $element)
	{
		$main_url = wa()->getRouteUrl('blog' . "/frontend", array(), true, $this->real_domain);
		$domain = $this->routing->getDomain(null, true);
		$sql = '
SELECT full_url, url, create_datetime, update_datetime
FROM blog_page
WHERE status = 1 AND domain = s:domain AND route = s:route
';
		$pages = $this->model->query($sql, array('domain' => $domain, 'route' => $route['url']))->fetchAll();

		foreach ($pages as $p)
		{
			$this->addUrl(
				$main_url . $p['full_url'],
				$p['update_datetime'] ? $p['update_datetime'] : $p['create_datetime'],
				$element->change_frequency === null ? $this->change_frequency->monthly : $element->change_frequency,
				$element->priority === null ? 0.6 : $element->priority
			);
		}
	}

	private function collectPostUrls($route)
	{
		$route_url = $route['url'];

		$this->blog_post_urls[$route_url] = array();

		$blog_model = new blogBlogModel();
		$post_model = new blogPostModel();

		$blogs = $blog_model->getAvailable(false, array('id', 'name', 'url'));

		$max_lastmod = null;

		$default_blog_id = isset($route['blog_url_type']) ? (int)$route['blog_url_type'] : 0;
		$default_blog_id = max(0, $default_blog_id);
		$extend_options = array(
			'datetime' => true,
		);
		$extend_data = array(
			'blog' => $blogs,
		);
		foreach ($blogs as $blog_id => $blog)
		{
			if (!$default_blog_id || ($blog_id == $default_blog_id))
			{
				$search_options = array('blog_id' => $blog_id);
				$posts = $post_model
					->search($search_options, $extend_options, $extend_data)
					->fetchSearchAll('id,title,url,datetime,blog_id');
				foreach ($posts as $post)
				{
					$post['blog_url'] = $blog['url'];
					$post_lastmod = strtotime($post['datetime']);
					$max_lastmod = max($max_lastmod, $post_lastmod);
					if (!empty($post['comment_datetime']))
					{
						$post_lastmod = max($post_lastmod, strtotime($post['comment_datetime']));
					}

					$this->blog_post_urls[$route_url][] = array($post['link'], $post_lastmod);
				}
			}
		}

		$this->main_page_route_lastmod[$route_url] = $max_lastmod;
	}
}