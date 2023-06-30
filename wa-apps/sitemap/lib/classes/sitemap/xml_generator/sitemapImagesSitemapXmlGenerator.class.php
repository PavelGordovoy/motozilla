<?php

class sitemapImagesSitemapXmlGenerator extends sitemapSitemapXmlGenerator
{
	private $xml;
	private $root_node;

	public function __construct($domain)
	{
		parent::__construct($domain);

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;

		//$xml->appendChild($xml->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . sitemapUrlHelper::getRootUrl($domain, true) . '/wa-content/xml/sitemap.xsl"'));

		$root_node = $xml->createElement('urlset');
		$root_node->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$root_node->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');

		$xml->appendChild($root_node);


		$this->xml = $xml;
		$this->root_node = $root_node;
	}

	public function generate()
	{
		return $this->xml->saveXML();
	}

	public function add($location, $images)
	{
		$xml = $this->xml;

		$url_node = $xml->createElement('url');

		$url_node->appendChild($xml->createElement('loc', htmlspecialchars($location)));
		if (is_array($images))
		{
			foreach ($images as $image)
			{
				$url = $image['url'];
				$description = $image['description'];

				$image_node = $xml->createElement('image:image');
				$image_node->appendChild($xml->createElement('image:loc', htmlspecialchars($url)));
				if (is_string($description) && strlen(trim($description)))
				{
					$image_node->appendChild($xml->createElement('image:caption', htmlspecialchars($description)));
				}

				$url_node->appendChild($image_node);
			}
		}

		$this->root_node->appendChild($url_node);
	}
}