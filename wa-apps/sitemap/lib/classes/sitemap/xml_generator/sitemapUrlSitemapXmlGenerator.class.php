<?php

class sitemapUrlSitemapXmlGenerator extends sitemapSitemapXmlGenerator
{
	private $change_frequency;
	private $xml;
	private $root_node;

	public function __construct($domain)
	{
		parent::__construct($domain);

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;

		$xml->appendChild($xml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . sitemapUrlHelper::getRootUrl($domain, true) . '/wa-content/xml/sitemap.xsl"'));

		$root_node = $xml->createElement('urlset');
		$root_node->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$root_node->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
		$root_node->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$xml->appendChild($root_node);


		$this->change_frequency = new sitemapUrlSitemapChangeFrequency();
		$this->xml = $xml;
		$this->root_node = $root_node;
	}

	/**
	 * @param string $url
	 * @param string|int $lastmod string or timestamp
	 * @param string $change_frequency
	 * @param double $priority
	 */
	public function addElement($url, $lastmod, $change_frequency, $priority)
	{
		$sitemap_xml = $this->xml->createElement('url');

		$sitemap_xml->appendChild($this->xml->createElement('loc', htmlspecialchars($url)));
		$sitemap_xml->appendChild($this->xml->createElement('lastmod',  date('c', wa_is_int($lastmod) ? $lastmod : strtotime($lastmod))));
		$sitemap_xml->appendChild($this->xml->createElement('changefreq',  $change_frequency));
		$sitemap_xml->appendChild($this->xml->createElement('priority',  str_replace(',', '.', min(1.0, max(0.0, $priority)))));

		$this->root_node->appendChild($sitemap_xml);
	}

	public function generate()
	{
		return $this->xml->saveXML();
	}
}