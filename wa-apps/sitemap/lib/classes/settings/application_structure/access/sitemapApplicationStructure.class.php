<?php

/**
 * Class sitemapApplicationStructure
 *
 * @property string $app_id
 * @property int $domain_id
 * @property string $route_url
 * @property sitemapApplicationStructureElementSettings[] $elements
 */
class sitemapApplicationStructure extends sitemapPropertyAccess
{
	public function getAssoc()
	{
		$assoc = parent::getAssoc();

		if (!is_array($assoc['elements']))
		{
			$assoc['elements'] = array();
		}

		foreach (array_keys($assoc['elements']) as $index)
		{
			/** @var sitemapApplicationStructureElementSettings $element */
			$element = $assoc['elements'][$index];
			$assoc['elements'][$index] = $element->getAssoc();
		}

		return $assoc;
	}

	public function hasShownElements()
	{
		foreach ($this->elements as $element)
		{
			if ($element->is_shown)
			{
				return true;
			}
		}

		return false;
	}
}