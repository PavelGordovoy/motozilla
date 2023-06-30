<?php

interface sitemapBackendViewAction
{
	public function execute($request);

	public function activeMenuElement();
}