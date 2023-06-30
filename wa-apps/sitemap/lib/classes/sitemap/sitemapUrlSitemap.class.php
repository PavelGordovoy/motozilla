<?php

abstract class sitemapUrlSitemap extends sitemapSitemap
{
	private $url_xml;
	private $url_in_xml_count = 0;

	protected $change_frequency;

	public function __construct($domain_name, $real_domain_name)
	{
		parent::__construct($domain_name, $real_domain_name);

		$this->url_xml = new sitemapUrlSitemapXmlGenerator($this->real_domain);
		$this->change_frequency = new sitemapUrlSitemapChangeFrequency();
	}

	public function getSitemapDocumentContent()
	{
		return $this->url_xml->generate();
	}

	public function count()
	{
		return !$this->structure || $this->structure->hasShownElements()
			? 1
			: 0;
	}

	protected function getUrlInXmlCount()
	{
		return $this->url_in_xml_count;
	}

	protected function addUrl($url, $lastmod, $change_frequency, $priority)
	{
		if ($this->urlIsExcluded($url))
		{
			return;
		}

		$this->url_xml->addElement($url, $lastmod, $change_frequency, $priority);
		$this->url_in_xml_count++;
	}
}