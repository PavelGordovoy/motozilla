<?php

class sitemapSavedSitemapPage
{
	private $domain;
	private $app_id;
	private $sub_sitemap;
	private $page;

	private $file_path;

	public function __construct($domain, $app_id, $sub_sitemap = null, $page = null)
	{
		$this->domain = $domain;
		$this->app_id = $app_id;
		$this->sub_sitemap = $sub_sitemap;
		$this->page = $page;

		$this->file_path = $this->getFilePath();
	}


	public function getContent()
	{
		return file_exists($this->file_path)
			? file_get_contents($this->file_path)
			: null;
	}

	public function saveContent($content)
	{
		file_put_contents($this->file_path, $content);
	}

	public function isSaved()
	{
		return file_exists($this->file_path);
	}

	public function display()
	{
		if ($this->isSaved())
		{
			$this->sendHeaders();

			echo $this->getContent();
		}
	}

	private function sendHeaders()
	{
		$system = waSystem::getInstance();
		$system->getResponse()->addHeader('Content-Type', 'application/xml; charset=UTF-8');
		$system->getResponse()->sendHeaders();
	}

	private function getFilePath()
	{
		$parts = array(
			preg_replace('/[^-a-z0-9_.]/', '_', $this->domain),
			$this->app_id,
		);
		if ($this->sub_sitemap)
		{
			$parts[] = $this->sub_sitemap;
		}

		$parts[] = $this->page ? $this->page : 1;

		$file = wa()->getDataPath('sitemaps', false, 'sitemap', true) . '/' . implode('_', $parts) . '.xml';

		return $file;
	}
}