<?php

class sitemapPluginStructureSettings
{
	private $settings;

	public function __construct($settings)
	{
		$this->settings = is_array($settings) ? $settings : array();
	}

	public function isShown()
	{
		return array_key_exists('is_shown', $this->settings) && $this->settings['is_shown'];
	}
}