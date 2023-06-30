<?php

class sitemapShopProductImagesUrlSitemap extends sitemapImagesSitemap
{
	const LIMIT = 10000;

	private $shop_settings_storage;

	/** @var shopViewHelper */
	private $shop_view_helper;

	private $route;

	public function __construct($domain_name, $real_domain_name, $route)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->shop_settings_storage = new sitemapShopSettingsStorage();
		$this->route = $route;
	}

	public function count()
	{
		if (!$this->structure->hasShownElements())
		{
			return 0;
		}

		$count = $this->countByRoute($this->route);

		return ceil($count / self::LIMIT);
	}

	public function countByRoute($route)
	{
		if (!is_array($route))
		{
			return 0;
		}

		list($images_sql, $query_params) = $this->getImagesSql($route, true);

		$sql = 'SELECT COUNT(i.id)' . PHP_EOL . $images_sql;

		return intval($this->model->query($sql, $query_params)->fetchField());
	}

	public function formSitemapDocument($page = null)
	{
		if ($this->structure && !$this->structure->hasShownElements())
		{
			throw new waException('', 404);
		}

		wa('shop');
		$this->shop_view_helper = new shopViewHelper(waSystem::getInstance('shop'));

		//foreach ($this->routes as $route)
		//{
		//	$this->routing->setRoute($route);
		//
		//	$offset = $page === null
		//		? 0
		//		: self::LIMIT * ($page - 1);
		//
		//	$this->collectRouteUrls($route, $offset, self::LIMIT);
		//
		//	break;
		//}

		if (!is_array($this->route))
		{
			return;
		}

		$this->routing->setRoute($this->route);

		$offset = $page === null
			? 0
			: self::LIMIT * ($page - 1);

		$this->collectRouteUrls($this->route, $offset, self::LIMIT);
	}

	/**
	 * @param string $route_url
	 * @return sitemapShopSettings
	 */
	protected function getRouteSettings($route_url)
	{
		return $this->shop_settings_storage->getSettings($this->domain_id, $route_url);
	}

	public function getAppId()
	{
		return 'shop';
	}

	protected function getSubSitemapType()
	{
		return sitemapSitemapFactory::SHOP_SUB_SITEMAP_PRODUCT_IMAGES;
	}

	protected function getStructure()
	{
		$structure_storage = new sitemapShopStructureStorage();

		return $structure_storage->getSubstructure(
			$this->domain_id,
			sitemapShopStructureStorage::DEFAULT_ROUTE_URL,
			sitemapShopStructureStorage::SUBSTRUCTURE_PRODUCT_IMAGES
		);
	}

	private function collectRouteUrls($route, $offset, $limit)
	{
		$product_url_template = $this->routing->getUrl('shop/frontend/product', array(
			'product_url' => '%PRODUCT_URL%',
			'category_url' => '%CATEGORY_URL%'
		), true, $this->real_domain);
        $products_images = $this->getImagesGroupByProducts($route, $offset, $limit);

        foreach($products_images as $product_id => $params) {
            $this->writeProductImagesToXml($params['product_info'], $params['images_info'], $product_url_template);
        }
	}
    private function getImagesGroupByProducts($route, $offset, $limit) {
        $settings = $this->getRouteSettings($route['url']);

        list($sql, $query_params) = $this->getImagesSql($route);

        $sql = 'SELECT i.*, p.url AS product_url, c.url AS category_url, c.full_url AS category_full_url' . PHP_EOL . $sql . PHP_EOL . "LIMIT {$offset}, {$limit}";

        $images = $this->model->query($sql, $query_params);
        $products_images = array();
        foreach ($images as $image) {
            if(!isset($products_images[$image['product_id']]['product_info'])) {
                $products_images[$image['product_id']]['product_info'] = array(
                    'category_full_url' => $image['category_full_url'],
                    'product_url' => $image['product_url']
                );
                $products_images[$image['product_id']]['images_info'] = array();
            }

            $products_images[$image['product_id']]['images_info'][] = array(
                'url' => $this->shop_view_helper->imgUrl($image, $settings->product_images_size, true),
                'description' => $image['description']
            );
        }
        return $products_images;
    }

	private function getImagesSql($route, $is_for_count_query = false)
	{
		$query_params = array();
		$where_parts = array();
		$settings = $this->getRouteSettings($route['url']);
		if (!$settings->show_hidden_products)
		{
			$where_parts[] = 'p.`status` = 1';
		}

		$group_by = 'GROUP BY i.id';
		$join_category = '	LEFT JOIN shop_category AS c
		ON c.id = p.category_id';
		$order_by = 'ORDER BY p.id, i.id DESC';

		if (!empty($route['type_id']))
		{
			$where_parts[] = 'p.type_id IN (i:type_id)';
			$query_params['type_id'] = $route['type_id'];
		}

		if ($is_for_count_query)
		{
			$group_by = $join_category = $order_by = '';
		}

		$where = '';
		if (count($where_parts) > 0)
		{
			$where = 'WHERE ' . implode(' AND ', $where_parts);
		}

		$sql = "
FROM shop_product_images AS i
	JOIN shop_product AS p
		ON p.id = i.product_id
{$join_category}
{$where}
{$group_by}
{$order_by}
";

		return array($sql, $query_params);
	}

	private function writeProductImagesToXml($product_params, $images, $product_url_template)
	{
		$category_url = $product_params['category_full_url'];

		$product_url = is_string($category_url) && strlen($category_url) > 0
			? str_replace(array('%PRODUCT_URL%', '%CATEGORY_URL%'), array($product_params['product_url'], $category_url), $product_url_template)
			: str_replace(array('%PRODUCT_URL%', '/%CATEGORY_URL%'), array($product_params['product_url'], ''), $product_url_template);

		$this->addImages($product_url, $images);
	}

	/**
	 * @return sitemapApplicationStructureElementSettings
	 */
	private function getProductImagesElement()
	{
		$default_element = new sitemapApplicationStructureElementSettings();
		$default_element->element_id = sitemapShopStructureStorage::ELEMENT_PRODUCT_IMAGES;
		$default_element->plugin_id = '';
		$default_element->is_shown = true;

		if (!$this->structure)
		{
			return $default_element;
		}

		foreach ($this->structure->elements as $element)
		{
			if ($element->element_id == sitemapShopStructureStorage::ELEMENT_PRODUCT_IMAGES)
			{
				return $element;
			}
		}

		return $default_element;
	}
}