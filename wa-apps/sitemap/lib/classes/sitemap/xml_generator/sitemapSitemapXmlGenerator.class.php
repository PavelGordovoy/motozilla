<?php

abstract class sitemapSitemapXmlGenerator
{
	private $domain;

	public function __construct($domain)
	{
		$this->domain = $domain;
	}
}