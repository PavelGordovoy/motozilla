<?php

class sitemapShopUrlSitemap extends sitemapUrlSitemap
{
	private $limit = 10000;

	private $category_routes;

	/** @var sitemapShopSettings[] */
	private $route_settings;
	private $shop_settings_storage;

	private $route_plugin_url_params = array();

	public function __construct($domain_name, $real_domain_name)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->category_routes = array();

		$this->shop_settings_storage = new sitemapShopSettingsStorage();
		$this->route_settings = array();
	}

	public function formSitemapDocument($page = null)
	{
		wa('shop');

		$offset = $page === null
			? 0
			: $this->limit * ($page - 1);
		$collected_urls_count = 0;

		foreach ($this->routes as $route)
		{
			$this->routing->setRoute($route);

			foreach ($this->structure->elements as $element)
			{
				if (!$element->is_shown)
				{
					continue;
				}

				$route_element_urls_count = $this->countRouteUrlsByElement($route, $element);
				if ($offset > $route_element_urls_count)
				{
					$offset = $offset - $route_element_urls_count;
					continue;
				}

				$urls_params = $this->getRouteElementUrlParams($route, $element, $offset, $this->limit - $collected_urls_count);
				if ($element->element_id === sitemapShopStructureStorage::ELEMENT_PRODUCT)
				{
					$offset = 0;
				}

				for ($index = $offset; $index < count($urls_params); $index++)
				{
					$url_params = $urls_params[$index];

					$this->addElementUrl($url_params, $element);

					$collected_urls_count++;

					if ($collected_urls_count >= $this->limit)
					{
						break 3;
					}
				}

				$offset = 0;
			}
		}
	}

	public function count()
	{
		wa('shop');

		if (!$this->structure->hasShownElements())
		{
			return 0;
		}

		$count = 0;

		foreach ($this->routes as $route)
		{
			foreach ($this->structure->elements as $element)
			{
				if (!$element->is_shown)
				{
					continue;
				}

				$count += $this->countRouteUrlsByElement($route, $element);
			}
		}

		return intval(ceil($count / $this->limit));
	}

	public function getAppId()
	{
		return 'shop';
	}

	/**
	 * @param $route_url
	 * @return sitemapShopSettings
	 */
	protected function getRouteSettings($route_url)
	{
		if (!array_key_exists($route_url, $this->route_settings))
		{
			$this->route_settings[$route_url] = $this->shop_settings_storage->getSettings($this->domain_id, $route_url);
		}

		return $this->route_settings[$route_url];
	}

	protected function getStructure()
	{
		$structure_storage = new sitemapShopStructureStorage();

		return $structure_storage->get($this->domain_id, sitemapShopStructureStorage::DEFAULT_ROUTE_URL);
	}

	/**
	 * @param $element_id
	 * @param string $plugin_id
	 * @return sitemapApplicationStructureElementSettings
	 */
	private function getElement($element_id, $plugin_id = '')
	{
		foreach ($this->structure->elements as $element)
		{
			if ($element->element_id == $element_id)
			{
				if ($element->element_id != sitemapShopStructureStorage::ELEMENT_PLUGIN || $element->plugin_id == $plugin_id)
				{
					return $element;
				}
			}
		}

		$default_element = new sitemapApplicationStructureElementSettings();
		$default_element->element_id = $element_id;
		$default_element->$plugin_id = $plugin_id;

		return $default_element;
	}

	private function countRouteUrlsByElement($route, sitemapApplicationStructureElementSettings $element)
	{
		$element_id = $element->element_id;

		if ($element_id == sitemapShopStructureStorage::ELEMENT_MAIN)
		{
			return 1;
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PAGE)
		{
			return $this->countPagesByRoute($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_TAG)
		{
			return $this->countTagsByRoute($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PRODUCT)
		{
			return $this->countProductsByRoute($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PRODUCT_PAGE)
		{
			return $this->countProductPagesByRoute($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_CATEGORY)
		{
			return $this->countCategoriesByRoute($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PLUGIN)
		{
			return $this->countHookUrlsByRoute($route, $element->plugin_id);
		}

		return 0;
	}

	private function getRouteElementUrlParams($route, sitemapApplicationStructureElementSettings $element, $offset, $limit)
	{
		$element_id = $element->element_id;

		if ($element_id == sitemapShopStructureStorage::ELEMENT_MAIN)
		{
			return $this->getMainUrl();
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PAGE)
		{
			return $this->collectPageUrls($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_TAG)
		{
			return $this->collectTagUrls();
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PRODUCT)
		{
			return $this->collectProductUrls($route, $offset, $limit);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PRODUCT_PAGE)
		{
			return $this->collectProductPageUrls($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_CATEGORY)
		{
			return $this->collectCategoryUrls($route);
		}
		elseif ($element_id == sitemapShopStructureStorage::ELEMENT_PLUGIN)
		{
			return $this->collectHookUrls($route, $element->plugin_id);
		}

		return array();
	}

	private function collectCategoryUrls($route)
	{
		$urls_params = array();

		$storefront = $this->real_domain . '/' . $route['url'];
		$settings = $this->getRouteSettings($route['url']);
		$category_model = new shopCategoryModel();

		$select = 'c.id,c.parent_id,c.left_key,c.url,c.full_url,c.create_datetime,c.edit_datetime';
		$canonical_join = '';

		if (sitemapHelper::isLinkcanonicalPluginInstalled() && $settings->links_to_canonical)
		{
			$select .= ', COALESCE(canonical_storefront.canonical, canonical_general.canonical) AS canonical';

			$canonical_join = '
	LEFT JOIN shop_linkcanonical_category_canonical AS canonical_general
		ON c.id = canonical_general.category_id AND canonical_general.storefront = \'*\'
	LEFT JOIN shop_linkcanonical_category_canonical AS canonical_storefront
		ON c.id = canonical_storefront.category_id AND canonical_storefront.storefront = :storefront';

			$query_params['storefront'] = $storefront;
		}
		else
		{
			$select .= ',NULL AS canonical';
		}

		$sql = "
SELECT {$select}
FROM shop_category AS c
{$canonical_join}
	LEFT JOIN shop_category_routes AS cr
		ON c.id = cr.category_id
WHERE (cr.route IS NULL OR cr.route = :storefront)
";
		$query_params = array(
			'storefront' => $storefront,
		);

		if (!$settings->show_hidden_categories)
		{
			$sql .= 'AND c.status = 1';
		}

		$sql .= PHP_EOL . 'ORDER BY c.left_key';

		$categories = $category_model->query($sql, $query_params)->fetchAll('id');
		$category_url_template = $this->routing->getUrl('shop/frontend/category', array('category_url' => '%CATEGORY_URL%'), true, $this->real_domain);
		$current_route = $this->routing->getRoute();
		$is_category_url_with_category_keyword = ifset($current_route, 'url_type', '0') != '2';
		$storefront_url = $this->routing->getUrl('', array('module' => 'frontend'), true); // todo fix for CLI

		foreach ($categories as $c_id => $c)
		{
			if ($c['parent_id'] && !isset($categories[$c['parent_id']]))
			{
				unset($categories[$c_id]);
				continue;
			}

			$category_canonical = $c['canonical'];
			$is_absolute = false;
			if (is_string($category_canonical) && trim($category_canonical) !== '')
			{
				$category_canonical = trim($category_canonical);
				if (mb_substr($category_canonical, 0, 7) === 'http://' || mb_substr($category_canonical, 0, 8) === 'https://')
				{
					$url = $category_canonical;
					$is_absolute = true;
				}
				else
				{
					if ($is_category_url_with_category_keyword)
					{
						$category_canonical = preg_replace('@^/category/@', '', $category_canonical);
					}

					$url = $storefront_url . trim($category_canonical, '/');
				}
			}
			else
			{
				if (isset($route['url_type']) && $route['url_type'] == 1)
				{
					$url = $c['url'];
				}
				else
				{
					$url = $c['full_url'];
				}
			}

			$urls_params[] = array(
				$is_absolute ? $url : str_replace('%CATEGORY_URL%', $url, $category_url_template),
				$c['edit_datetime'] ? $c['edit_datetime'] : $c['create_datetime'],
				$this->change_frequency->weekly,
				0.6,
			);
		}

		return $urls_params;
	}

	private function collectPageUrls($route)
	{
		$urls_params = array();

		$sql = "
SELECT p.id, p.parent_id, p.name, p.title, p.full_url as url, p.create_datetime, p.update_datetime, pp.value as priority
FROM shop_page AS p
	LEFT JOIN shop_page_params AS pp
		ON p.id = pp.page_id AND pp.name = 'priority'
WHERE domain = s:domain AND p.route = s:route AND p.status = 1
ORDER BY sort";

		$query_params = array(
			'domain' => $this->real_domain,
			'route' => $route['url']
		);
		$pages = $this->model->query($sql, $query_params)->fetchAll('id');

		$root_url = $this->routing->getUrlByRoute($route, $this->real_domain);
		foreach ($pages as $page)
		{
			$priority = !empty($page['priority']) && $page['priority'] >= 0 && $page['priority'] <= 100
				? intval($page['priority']) / 100.0
				: false;


			if (!$page['url'])
			{
				if ($priority === false)
				{
					$priority = 1;
				}

				$change = $this->change_frequency->weekly;
			}
			else
			{
				if ($priority === false)
				{
					$priority = $page['parent_id'] ? 0.2 : 0.6;
				}

				$change = $this->change_frequency->monthly;
			}

			$page_absolute_url = $root_url . $page['url'];
			if (strpos($page_absolute_url, '<') === false)
			{
				$urls_params[] = array(
					$page_absolute_url,
					$page['update_datetime'],
					$change,
					$priority,
				);
			}
		}

		return $urls_params;
	}

	private function collectHookUrls($route, $plugin_id)
	{
		if (!array_key_exists($plugin_id, $this->route_plugin_url_params))
		{
			$this->route_plugin_url_params[$plugin_id] = array();
		}

		if (array_key_exists($route['url'], $this->route_plugin_url_params[$plugin_id]))
		{
			return $this->route_plugin_url_params[$plugin_id][$route['url']];
		}

		$plugin_urls_params = array();
		foreach ($this->getHookUrls($route, $plugin_id) as $url)
		{
			if (is_string($url))
			{
				$url_params = array(
					$url,
					time(),
					null,
					null,
				);
			}
			elseif (is_array($url))
			{
				$url_params = array(
					$url['loc'],
					isset($url['lastmod']) ? $url['lastmod'] : time(),
					isset($url['changefreq']) ? $url['changefreq'] : null,
					isset($url['priority']) ? $url['priority'] : null,
				);
			}
			else
			{
				continue;
			}

			$plugin_urls_params[] = $url_params;
		}

		$this->route_plugin_url_params[$plugin_id][$route['url']] = $plugin_urls_params;

		return $this->route_plugin_url_params[$plugin_id][$route['url']];
	}

	private function getMainUrl()
	{
		$main_url = $this->routing->getUrl('shop/frontend', array(), true, $this->real_domain);

		// убирает "/" с конца, если перед ним домен первого уровня
		// например, у http://site.ru/
		// но не у http://site.ru/subdomain/
		$main_url = preg_replace('@(\.[^/]+)/$@', '$1', $main_url);

		$main_page_params = array(
			$main_url,
			time(),
			$this->change_frequency->daily,
			1,
		);

		return array($main_page_params);
	}

	private function collectProductUrls($route, $offset, $limit)
	{
		$urls_params = array();

		$settings = $this->getRouteSettings($route['url']);
		$storefront = $this->real_domain . '/' . $route['url'];

		$product_page_element = $this->getElement(sitemapShopStructureStorage::ELEMENT_PRODUCT_PAGE);

		$skip_count = 0;
		$query_offset = $offset;
		$query_limit = $limit;

		$product_subpages = $settings->product_subpages;
		if ($product_page_element->is_shown)
		{
			$product_urls_count = 1 + count($product_subpages);

			$query_offset = floor($offset / $product_urls_count + 1e-6) + 0;
			$query_limit = ceil($limit / $product_urls_count - 1e-6) + 0;

			$skip_count = $offset % $product_urls_count;
		}
		else
		{
			$product_subpages = array();
		}

		$category_routes_model = new shopCategoryRoutesModel();

		$links_to_canonical = sitemapHelper::isLinkcanonicalPluginInstalled() && $settings->links_to_canonical;
		$products_query = $this->getProductsQuery($route, $storefront, $settings->show_hidden_products, $links_to_canonical, $query_offset, $query_limit);

		$product_url_template = $this->routing->getUrl('shop/frontend/product', array(
			'product_url' => '%PRODUCT_URL%',
			'category_url' => '%CATEGORY_URL%'
		), true, $this->real_domain);

		$current_route = $this->routing->getRoute();
		$is_product_url_with_product_keyword = ifset($current_route, 'url_type', '0') == '1';

		$storefront_url = $this->routing->getUrl('shop', array('module' => 'frontend'), true);

		// Output products, fetching product pages in batch
		$batch_size = 120;
		$iterator = $products_query->getIterator();
		while ($iterator->valid())
		{
			// Fetch next $batch_size products
			$products = array();
			$new_category_ids = array();
			for ($i = 0; $i < $batch_size && $iterator->valid(); $i++)
			{
				$product = $iterator->current();
				$product['pages'] = array();
				$products[$product['id']] = $product;
				$iterator->next();

				if (!empty($product['category_id']))
				{
					$new_category_ids[$product['category_id']] = $product['category_id'];
				}
			}

			// Fetch info about which categories are enabled for current storefront
			$category_disabled = array();
			$this->category_routes += $category_routes_model->getRoutes(array_values(array_diff_key($new_category_ids, $this->category_routes)), false);
			$this->category_routes += array_fill_keys(array_keys($new_category_ids), null);
			foreach ($new_category_ids as $category_id)
			{
				if (!empty($this->category_routes[$category_id]))
				{
					if (!in_array($storefront, $this->category_routes[$category_id]))
					{
						$category_disabled[$category_id] = true;
					}
				}
			}

			foreach($products as $product)
			{
				$category_url = array_key_exists('category_id', $product) && $product['category_id'] > 0 && empty($category_disabled[$product['category_id']])
					? $product['category_url']
					: '';

				$product_canonical = $product['canonical'];
				if (is_string($product_canonical) && trim($product_canonical) !== '')
				{
					$product_canonical = trim($product_canonical);
					if (mb_substr($product_canonical, 0, 7) === 'http://' || mb_substr($product_canonical, 0, 8) === 'https://')
					{
						$url = $product_canonical;
					}
					else
					{
						if ($is_product_url_with_product_keyword)
						{
							$product_canonical = preg_replace('@^/product/@', '', $product_canonical);
						}

						$url = $storefront_url . ltrim($product_canonical, '/');
					}
				}
				else
				{
					$url = strlen($category_url) > 0
						? str_replace(array('%PRODUCT_URL%', '%CATEGORY_URL%'), array($product['url'], $category_url), $product_url_template)
						: str_replace(array('%PRODUCT_URL%', '/%CATEGORY_URL%'), array($product['url'], ''), $product_url_template);
				}

				if ($skip_count == 0)
				{
					$urls_params[] = array(
						$url,
						$product['edit_datetime'] ? $product['edit_datetime'] : $product['create_datetime'],
						$this->change_frequency->monthly,
						0.8,
					);
				}
				else
				{
					$skip_count--;
				}

				foreach ($product_subpages as $subpage_url)
				{
					if ($skip_count == 0)
					{
						$urls_params[] = array(
							$url . $subpage_url . '/',
							time(),
							$this->change_frequency->monthly,
							0.4,
							sitemapShopStructureStorage::ELEMENT_PRODUCT_PAGE,
						);
					}
					else
					{
						$skip_count--;
					}
				}
			}
		}

		return $urls_params;
	}

	private function collectProductPageUrls($route)
	{
		$settings = $this->getRouteSettings($route['url']);
		$storefront = $this->real_domain . '/' . $route['url'];

		$page_model = new shopProductPagesModel();

		$product_pages = array();

		$pages = $page_model->select('product_id, url, create_datetime, update_datetime')
			->where('status = 1')
			->query();

		foreach ($pages as $page)
		{
			$product_id = $page['product_id'];
			if (!array_key_exists($product_id, $product_pages))
			{
				$product_pages[$product_id] = array();
			}

			$product_pages[$product_id][] = $page;
		}

		if (count($product_pages) == 0)
		{
			return array();
		}

		$product_url_template = $this->routing->getUrl('shop/frontend/product', array(
			'product_url' => '%PRODUCT_URL%',
			'category_url' => '%CATEGORY_URL%'
		), true, $this->real_domain);

		$where = array();
		$where[] = 'p.id IN (' . implode(',', array_keys($product_pages)) . ')';

		$products_query = $this->getProductsQuery(
			$route,
			$storefront,
			$settings->show_hidden_products,
			false,
			0,
			0,
			$where
		);

		$urls_params = array();
		foreach ($products_query as $product)
		{
			$product_id = $product['id'];

			$category_url = array_key_exists('category_id', $product) && $product['category_id'] > 0 && empty($category_disabled[$product['category_id']])
				? $product['category_url']
				: '';

			$url = strlen($category_url) > 0
				? str_replace(array('%PRODUCT_URL%', '%CATEGORY_URL%'), array($product['url'], $category_url), $product_url_template)
				: str_replace(array('%PRODUCT_URL%', '/%CATEGORY_URL%'), array($product['url'], ''), $product_url_template);

			foreach ($product_pages[$product_id] as $product_page)
			{
				$urls_params[] = array(
					$url . $product_page['url'] . '/',
					$product_page['update_datetime'] ? $product_page['update_datetime'] : $product_page['create_datetime'],
					$this->change_frequency->monthly,
					0.4,
				);
			}
		}

		return $urls_params;
	}

	private function getProductsQuery($route, $storefront, $show_hidden_products, $links_to_canonical, $offset, $limit, $where = array())
	{
		$query_params = array();

		$product_model = new shopProductModel();
		$category_model = new shopCategoryModel();

		$select = 'p.id, p.url, p.create_datetime, p.edit_datetime';
		$from = $product_model->getTableName() . ' p';
		$joins = array();

		if (isset($route['url_type']) && $route['url_type'] == 2)
		{
			$select .= ', c.full_url category_url, p.category_id';
		}

		if (isset($route['url_type']) && $route['url_type'] == 2)
		{
			$joins[] = " LEFT JOIN " . $category_model->getTableName() . " c ON p.category_id = c.id";
		}

		if ($links_to_canonical)
		{
			$select .= ', COALESCE(canonical_storefront.canonical, canonical_general.canonical) AS canonical';

			$joins[] = "\tLEFT JOIN shop_linkcanonical_product_canonical AS canonical_general
		ON p.id = canonical_general.product_id AND canonical_general.storefront = '*'";


			$joins[] = "\tLEFT JOIN shop_linkcanonical_product_canonical AS canonical_storefront
		ON p.id = canonical_storefront.product_id AND canonical_storefront.storefront = :storefront";

			$query_params['storefront'] = $storefront;
		}
		else
		{
			$select .= ', NULL AS canonical';
		}

		if (!$show_hidden_products)
		{
			$where[] = 'p.status = 1';
		}

		if (!empty($route['type_id']))
		{
			$where[] = 'p.type_id IN (i:type_id)';
			$query_params['type_id'] = $route['type_id'];
		}

		$sql = "
SELECT {$select}
FROM {$from}";

		if (count($joins))
		{
			$sql .= PHP_EOL . implode(PHP_EOL, $joins);
		}

		if (count($where))
		{
			$sql .= PHP_EOL . 'WHERE ' . implode(' AND ', $where);
		}

		$sql .= PHP_EOL . 'GROUP BY p.id';
		$sql .= PHP_EOL . 'ORDER BY p.id ASC';

		if ($limit > 0)
		{
			if (!wa_is_int($offset))
			{
				$offset = 0;
			}

			$sql .= PHP_EOL . "LIMIT {$offset}, {$limit}";
		}

		return $this->model->query($sql, $query_params);
	}

	private function countPagesByRoute($route)
	{
		$model = new shopPageModel();

		return intval($model->select('COUNT(id)')
			->where('domain = s:domain', array('domain' => $this->real_domain))
			->where('route = s:route', array('route' => $route['url']))
			->where('status = 1')
			->fetchField());
	}

	private function countTagsByRoute($route)
	{
		$sql = '
SELECT COUNT(DISTINCT t.id)
FROM shop_tag t
	JOIN shop_product_tags AS pt
		ON pt.tag_id = t.id
';

		return intval($this->model->query($sql)->fetchField());
	}

	private function countProductsByRoute($route)
	{
		$settings = $this->getRouteSettings($route['url']);

		$sql = "SELECT COUNT(*) FROM shop_product AS p";

		$where = array();
		if (!$settings->show_hidden_products)
		{
			$where[] = 'p.status = 1';
		}

		if (!empty($route['type_id']))
		{
			$where[] = 'p.type_id IN (i:type_id)';
			$query_params['type_id'] = $route['type_id'];
		}

		if (count($where))
		{
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

		$products_count = intval($this->model->query($sql, $route)->fetchField());

		$product_page_element = $this->getElement(sitemapShopStructureStorage::ELEMENT_PRODUCT_PAGE);

		return $product_page_element->is_shown
			? $products_count + $products_count * count($settings->product_subpages)
			: $products_count;
	}

	private function countProductPagesByRoute($route)
	{
		$settings = $this->getRouteSettings($route['url']);

		$sql = "
SELECT COUNT(pp.id)
FROM shop_product_pages AS pp
	JOIN shop_product AS p
		ON p.id = pp.product_id
";

		$where = array();
		$where[] = 'pp.status = 1';
		if (!$settings->show_hidden_products)
		{
			$where[] = 'p.status = 1';
		}

		if (!empty($route['type_id']))
		{
			$where[] = 'p.type_id IN (i:type_id)';
			$query_params['type_id'] = $route['type_id'];
		}

		if (count($where))
		{
			$sql .= PHP_EOL . 'WHERE ' . implode(' AND ', $where);
		}

		return intval($this->model->query($sql, $route)->fetchField());
	}

	private function countCategoriesByRoute($route)
	{
		$storefront = $this->real_domain . '/' . $route['url'];

		$settings = $this->getRouteSettings($route['url']);

		$sql = '
SELECT COUNT(DISTINCT c.id)
FROM shop_category AS c
	LEFT JOIN shop_category_routes AS cr
		ON cr.category_id = c.id
';

		$where = array();
		$where[] = '(cr.route IS NULL OR cr.route = s:storefront)';

		if (!$settings->show_hidden_categories)
		{
			$where[] = 'c.`status` = 1';
		}

		$query_params = array(
			'storefront' => $storefront,
		);

		if (count($where))
		{
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}

		return intval($this->model->query($sql, $query_params)->fetchField());
	}

	private function countHookUrlsByRoute($route, $plugin_id = null)
	{
		$urls = $this->getHookUrls($route, $plugin_id);

		return count($urls);
	}

	private function addElementUrl($url_params, sitemapApplicationStructureElementSettings $element)
	{
		list($url, $lastmod, $change_frequency, $priority) = $url_params;
		if (isset($url_params[4]))
		{
			$element_id = $url_params[4];
			$plugin_id = isset($url_params[5]) ? $url_params[5] : '';

			$element = $this->getElement($element_id, $plugin_id);
		}

		$this->addUrl(
			$url,
			$lastmod,
			$element->change_frequency === null ? $change_frequency : $element->change_frequency,
			$element->priority === null ? $priority : $element->priority
		);
	}
	// todo app hooks

	private function getHookUrls($route, $plugin_id = null)
	{
		if (!$plugin_id)
		{
			return array();
		}


		$plugin_urls = array();

		/**
		 * @event sitemap
		 * @param array $route
		 * @return array $urls
		 */
		$plugins_urls = wa('shop')->event(array('shop', 'sitemap'), $route);

		$plugins_cache_index = array();

		foreach ($plugins_urls as $plugin_key => $urls)
		{
			if (strlen($plugin_key) > 7 && substr($plugin_key, -7) === '-plugin')
			{
				$_plugin = substr($plugin_key, 0, -7);
				$key = $_plugin;

				$plugins_cache_index[$_plugin] = $key;
				$cache = new waSerializeCache($key, 2 * 3600, 'sitemap');
				$cache->set($urls);

				if ($_plugin === $plugin_id)
				{
					$plugin_urls = $urls;
				}
			}
		}

		if (!$plugin_urls)
		{
			return array();
		}

		return $plugin_urls;
	}

	private function collectTagUrls()
	{
		$tags_cache = new waVarExportCache('sitemap/shop_tags_' . $this->domain_id, 3600, 'sitemap'); // todo
		if ($tags_cache->isCached())
		{
			$sitemap_links = $tags_cache->get();
		}
		else
		{
			$sitemap_links = array();

			$sql = '
SELECT
	t.name AS url,
	COALESCE(MAX(p.edit_datetime), MAX(p.create_datetime)) AS last_modified
FROM shop_tag t
	JOIN shop_product_tags AS pt
		ON pt.tag_id = t.id
	JOIN shop_product AS p
		ON p.id = pt.product_id AND p.status <> 0
GROUP BY t.id
';
			$tag_query = $this->model->query($sql);

			foreach ($tag_query as $row)
			{
				$sitemap_links[$row['url']] = $row['last_modified'];
			}
		}

		if (count($sitemap_links) == 0)
		{
			return array();
		}

		$urls_params = array();

		$tag_url_template = $this->routing->getUrl('shop/frontend/tag', array('tag' => '%TAG_URL%'), true, $this->real_domain);
		foreach ($sitemap_links as $url => $last_modified)
		{
			$urls_params[] = array(
				str_replace('%TAG_URL%', urlencode($url), $tag_url_template),
				$last_modified,
				$this->change_frequency->weekly,
				0.6,
			);
		}

		return $urls_params;
	}
}
