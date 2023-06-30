<?php

$system_config_patcher = new sitemapSystemConfigPatcher_1533278860();
if ($system_config_patcher->isPatched())
{
	$system_config_patcher->backupCurrentConfig();
	$system_config_patcher->restore();
	$system_config_patcher->patch();
}