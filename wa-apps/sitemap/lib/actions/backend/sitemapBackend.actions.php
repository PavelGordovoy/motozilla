<?php

class sitemapBackendActions extends sitemapBackendViewActions
{
	public function defaultAction()
	{
		$this->addJs('assets/structure_settings.js');
		$this->addCss('assets/structure_settings.css');

		return new sitemapSitemapStructureViewAction();
	}

	public function generalSettingsAction()
	{
		$this->addJs('assets/general_settings.js');
		$this->addCss('assets/general_settings.css');

		return new sitemapGeneralSettingsViewAction();
	}
}