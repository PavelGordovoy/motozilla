<?php

class sitemapShopStructureStorage extends sitemapApplicationStructureStorage
{
	const SUBSTRUCTURE_SEOFILTER = 'seofilter';
	const SUBSTRUCTURE_PRODUCT_IMAGES = 'product_images';

	const ELEMENT_MAIN = 'main';
	const ELEMENT_CATEGORY = 'category';
	const ELEMENT_PAGE = 'page';
	const ELEMENT_PRODUCT = 'product';
	const ELEMENT_PRODUCT_PAGE = 'product_page';
	const ELEMENT_TAG = 'tag';
	const ELEMENT_PRODUCT_IMAGES = 'product_images';

	CONST PLUGIN_TAGEDITOR = 'tageditor';
	CONST PLUGIN_SEOFILTER = 'seofilter';
	CONST PLUGIN_REGIONS = 'regions';

	/**
	 * @param $domain_id
	 * @param $route_url
	 * @return sitemapApplicationStructure
	 */
	public function get($domain_id, $route_url)
	{
		$structure = parent::get($domain_id, $route_url);

		$tageditor_is_used = $this->isElementsContainTageditorPlugin($structure->elements);

		$elements = array();
		foreach ($structure->elements as $element)
		{
			if ($tageditor_is_used && $element->element_id == self::ELEMENT_TAG)
			{
				continue;
			}

			$elements[] = $element;
		}
		unset($element);

		$structure->elements = $elements;

		return $structure;
	}

	/** @return sitemapApplicationStructureElementSettings[] */
	protected function getDefaultStructureElements()
	{
		$plugin_elements = $this->collectPluginElements();

		$element_ids = array(
			self::ELEMENT_CATEGORY,
			self::ELEMENT_PAGE,
			self::ELEMENT_MAIN,
			self::ELEMENT_PRODUCT,
			self::ELEMENT_PRODUCT_PAGE,
		);

		if (!$this->isElementsContainTageditorPlugin($plugin_elements))
		{
			$element_ids[] = self::ELEMENT_TAG;
		}

		$element_ids[] = self::ELEMENT_PRODUCT_IMAGES;

		$plugin_ids = array();
		if (sitemapHelper::isSeofilterPluginEnabled())
		{
			$plugin_ids[] = self::PLUGIN_SEOFILTER;
		}

		$elements = array();
		foreach ($element_ids as $element_id)
		{
			$element = new sitemapApplicationStructureElementSettings();
			$element->element_id = $element_id;
			$element->plugin_id = '';
			//$category->lastmod_mode = 1;
			$element->change_frequency = null;
			$element->priority = null;
			$element->is_shown = $element_id != self::ELEMENT_PRODUCT_IMAGES;

			$elements[] = $element;
		}
		unset($element);

		foreach ($plugin_elements as $plugin_element)
		{
			$elements[] = $plugin_element;
		}
		unset($plugin_element);

		return $elements;
	}

	/** @return string */
	protected function getAppId()
	{
		return 'shop';
	}

	protected function getSubstructuresSpecification()
	{
		$specification = array(
			self::SUBSTRUCTURE_SEOFILTER => array(
				self::ELEMENT_PLUGIN => array(
					self::PLUGIN_SEOFILTER => self::PLUGIN_SEOFILTER,
				),
			),
			self::SUBSTRUCTURE_PRODUCT_IMAGES => array(
				self::ELEMENT_PRODUCT_IMAGES => self::ELEMENT_PRODUCT_IMAGES,
			),
		);

		$seofilter_structure_settings = $this->getPluginStructureSettings(self::PLUGIN_SEOFILTER);
		if (!$seofilter_structure_settings || $seofilter_structure_settings->isShown())
		{
			unset($specification[self::SUBSTRUCTURE_SEOFILTER]);
		}

		return $specification;
	}

	/**
	 * @param sitemapApplicationStructureElementSettings[] $elements
	 * @return bool
	 */
	protected function isElementsContainTageditorPlugin($elements)
	{
		foreach ($elements as $element)
		{
			if ($element->element_id == self::ELEMENT_PLUGIN && $element->plugin_id == self::PLUGIN_TAGEDITOR)
			{
				return true;
			}
		}

		return false;
	}
}