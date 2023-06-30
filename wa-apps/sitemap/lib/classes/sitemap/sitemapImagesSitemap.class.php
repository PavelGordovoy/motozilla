<?php

abstract class sitemapImagesSitemap extends sitemapSitemap
{
	private $images_xml;
	private $images_count = 0;

	public function __construct($domain_name, $real_domain_name)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->images_xml = new sitemapImagesSitemapXmlGenerator($this->real_domain);
	}

	public function getSitemapDocumentContent()
	{
		return $this->images_xml->generate();
	}

	protected function getImagesCount()
	{
		return $this->images_count;
	}

	protected function addImages($page_url, $images)
	{
		if ($this->urlIsExcluded($page_url))
		{
			return;
		}

		$images = array_filter($images, array($this, 'urlIsIncluded'));
		if (count($images) == 0)
		{
			return;
		}

		$this->images_xml->add($page_url, $images);
		$this->images_count += count($images);
	}

	private function urlIsIncluded($image)
	{
		return !$this->urlIsExcluded($image['url']);
	}
}