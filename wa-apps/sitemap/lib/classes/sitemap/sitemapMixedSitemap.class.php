<?php

abstract class sitemapMixedSitemap extends sitemapSitemap
{
	//protected $domain;
	//protected $real_domain;

	protected $change_frequency;

	public function __construct($domain_name, $real_domain_name)
	{
		parent::__construct($domain_name, $real_domain_name);
		//
		//$this->domain = $domain_name;
		//$this->real_domain = $real_domain_name;
		//
		//$this->index_xml = new sitemapIndexSitemapXmlGenerator($domain_name);
		//$this->url_xml = new sitemapUrlSitemapXmlGenerator($domain_name);

		$this->change_frequency = new sitemapUrlSitemapChangeFrequency();
	}

	protected function getStructure()
	{
		return null;
	}
}