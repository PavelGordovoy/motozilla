<?php

class sitemapBlogStructureStorage extends sitemapApplicationStructureStorage
{
	const ELEMENT_MAIN = 'main';
	const ELEMENT_POST = 'post';
	const ELEMENT_PAGE = 'page';

	/** @return sitemapApplicationStructureElementSettings[] */
	protected function getDefaultStructureElements()
	{
		$element_ids = array(
			self::ELEMENT_MAIN,
			self::ELEMENT_POST,
			self::ELEMENT_PAGE,
		);

		$elements = array();
		foreach ($element_ids as $element_id)
		{
			$element = new sitemapApplicationStructureElementSettings();
			$element->element_id = $element_id;
			$element->plugin_id = '';
			//$category->lastmod_mode = 1;
			$element->change_frequency = null;
			$element->priority = null;
			$element->is_shown = true;

			$elements[] = $element;
		}
		unset($element);

		foreach ($this->collectPluginElements() as $plugin_element)
		{
			$elements[] = $plugin_element;
		}
		unset($plugin_element);

		return $elements;
	}

	/** @return string */
	protected function getAppId()
	{
		return 'blog';
	}
}