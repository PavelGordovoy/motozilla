<?php

class sitemapGeneralSettingsGetDomainSettingsController extends waJsonController
{
	public function execute()
	{
		$this->response['success'] = false;

		$domain_id = waRequest::get('domain_id', -1, waRequest::TYPE_INT);
		if ($domain_id < 0)
		{
			return;
		}

		$storage = new sitemapGeneralSettingsStorage();
		$settings = $storage->getDomainSettings($domain_id);

		$this->response['success'] = true;
		$this->response['domain_settings'] = $settings->getAssoc();
	}
}