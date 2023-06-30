<?php

class sitemapHelper
{
	private static $is_seofilter_installed = null;
	private static $is_seofilter_enabled = null;

	private static $is_linkcanonical_installed = null;

	public static function getDomainIdByName($domain_name)
	{
		$model = new waModel();

		$sql = 'SELECT `id` FROM `site_domain` WHERE `name` = s:domain_name';

		return $model->query($sql, array('domain_name' => $domain_name))->fetchField();
	}

	public static function getPath($path)
	{
		return wa('sitemap')->getAppPath($path, 'sitemap');
	}

	public static function getStaticUrl($url, $absolute = false)
	{
		return wa('sitemap')->getAppStaticUrl('sitemap', $absolute) . $url;
	}

	public static function isSeofilterPluginInstalled()
	{
		if (self::$is_seofilter_installed === null)
		{
			$info = wa('shop')->getConfig()->getPluginInfo('seofilter');

			self::$is_seofilter_installed =
				$info !== array()
				&& class_exists('shopSeofilterPluginSettings')
				&& class_exists('shopSeofilterFiltersStorage');
		}

		return self::$is_seofilter_installed;
	}

	public static function isSeofilterPluginEnabled()
	{
		if (self::$is_seofilter_enabled === null)
		{
			if (self::isSeofilterPluginInstalled())
			{
				$settings = shopSeofilterBasicSettingsModel::getSettings();

				$is_seofilter_enabled = $settings->is_enabled;
			}
			else
			{
				$is_seofilter_enabled = false;
			}

			self::$is_seofilter_enabled = $is_seofilter_enabled;
		}

		return self::$is_seofilter_enabled;
	}

	/**
	 * @return shopSeofilterPluginSettings|null
	 */
	public static function getSeofilterSettings()
	{
		return self::isSeofilterPluginInstalled()
			? shopSeofilterBasicSettingsModel::getSettings()
			: null;
	}

	public static function isLinkcanonicalPluginInstalled()
	{
		if (self::$is_linkcanonical_installed === null)
		{
			$info = wa('shop')->getConfig()->getPluginInfo('linkcanonical');

			$is_linkcanonical_installed = true;

			if ($info === array() || !array_key_exists('version', $info))
			{
				$is_linkcanonical_installed = false;
			}
			else
			{
				$version = $info['version'];

				if (version_compare($version, '1.10', '<'))
				{
					$is_linkcanonical_installed = false;
				}
			}

			self::$is_linkcanonical_installed = $is_linkcanonical_installed;
		}

		return self::$is_linkcanonical_installed;
	}
}