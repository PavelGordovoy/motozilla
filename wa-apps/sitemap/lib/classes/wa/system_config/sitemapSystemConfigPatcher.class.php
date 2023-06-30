<?php

class sitemapSystemConfigPatcher
{
	private static $is_patched = null;

	private $system_config_path;
	private $system_config_backup_path;

	public function __construct()
	{
		if (self::$is_patched === null)
		{
			self::$is_patched = class_exists('SitemapAppSystemConfig');
		}

		$this->system_config_path = wa()->getConfigPath() . '/SystemConfig.class.php';
		$this->system_config_backup_path = $this->system_config_path . '.sitemap_app_backup';

		$read_file_result = file_get_contents($this->system_config_path);

		if ($read_file_result === false || strlen($read_file_result) === 0)
		{
			throw new waException("can't read current SystemConfig [{$this->system_config_path}]");
		}
	}

	public function isPatched()
	{
		return self::$is_patched;
	}

	public function patch()
	{
		if ($this->isPatched())
		{
			return;
		}

		$system_config_code = $this->getSystemConfigCode();

		list ($system_config_code, $parent_class) = $this->tryRenameOriginalClass($system_config_code);

		if (!$parent_class)
		{
			throw new waException("cant find valid declaration of class [SystemConfig] in [{$this->system_config_path}]");
		}

		$system_config_code = $this->prependPatchedClassCode($system_config_code, $parent_class);
		$this->overwriteSystemConfig($system_config_code);

		self::$is_patched = true;
	}

	public function restore()
	{
		if (!$this->isPatched())
		{
			return;
		}

		$this->restoreOriginalSystemConfig();

		self::$is_patched = false;
	}

	private function tryRenameOriginalClass($system_config_code)
	{
		if (!preg_match('/class\s+SystemConfig\s+extends\s+([a-z_][a-z0-9_]*)(\s|$|\{)/i', $system_config_code, $matches))
		{
			return false;
		}

		$parent_class = $matches[1];

		$system_config_code = preg_replace('/class\s+SystemConfig\s+extends\s+[a-z_][a-z0-9_]*(\s|$|\{)/i', 'class SystemConfig extends SitemapAppSystemConfig\1', $system_config_code);

		return array($system_config_code, $parent_class);
	}

	private function prependPatchedClassCode($system_config_code, $parent_class)
	{
		$class_code = $this->getPatchedClassCode($parent_class);

		$original_class_code_position = strpos($system_config_code, 'class SystemConfig extends SitemapAppSystemConfig');

		return substr_replace(
			$system_config_code,
			$class_code,
			$original_class_code_position,
			0
		);
	}

	private function getPatchedClassCode($parent_class)
	{
		return '
class SitemapAppSystemConfig extends ' . $parent_class . '
{
	private static $sitemap_app_is_enabled = null;

	public function getRequestUrl($without_root = true, $without_params = false)
	{
		if (self::$sitemap_app_is_enabled === null)
		{
			$apps_config = $this->getConfigFile(\'apps\');

			self::$sitemap_app_is_enabled = array_key_exists(\'sitemap\', $apps_config) && $apps_config[\'sitemap\'];
		}

		if (!self::$sitemap_app_is_enabled)
		{
			return parent::getRequestUrl($without_root, $without_params);
		}


		$request_url = parent::getRequestUrl($without_root, $without_params);

		if (preg_match(\'/^sitemap-?([a-z0-9_]+)?(-([a-z0-9_]+))?(-([a-z0-9_]+))?.xml$/i\', $request_url, $m))
		{
			$app_id = isset($m[1]) ? $m[1] : \'webasyst\';

			if ($app_id != \'sitemap\')
			{
				$page = null;
				$sub_sitemap = null;
				if (array_key_exists(5, $m))
				{
					$sub_sitemap = $m[3];
					$page = $m[5];
				}
				elseif (array_key_exists(3, $m))
				{
					if (wa_is_int($m[3]))
					{
						$page = $m[3];
					}
					else
					{
						$sub_sitemap = $m[3];
					}
				}

				waRequest::setParam(\'sitemap_app_id\', $app_id);
				waRequest::setParam(\'sitemap_page\', $page);
				waRequest::setParam(\'sitemap_sub_sitemap\', $sub_sitemap);


				$request_url = \'sitemap-sitemap.xml\';

				$original_request_url_without_get = parent::getRequestUrl(false, true);
				$_SERVER[\'REQUEST_URI\'] = $this->getRootUrl() . $request_url . substr(
					$_SERVER[\'REQUEST_URI\'],
					strlen($original_request_url_without_get)
				);
			}
		}

		return $request_url;
	}
}


';
	}

	private function overwriteSystemConfig($system_config_code)
	{
		try
		{
			waFiles::copy($this->system_config_path, $this->system_config_backup_path);
		}
		catch (Exception $e)
		{
			throw new waException("can't create backup of current SystemConfig [{$this->system_config_path}]: " . $e->getMessage());
		}

		$system_config_temp_path = $this->system_config_path . '.temp';
		$write_result = waFiles::write($system_config_temp_path, $system_config_code);

		if ($write_result === false || $write_result === 0)
		{
			waFiles::delete($this->system_config_backup_path);

			throw new waException("can't create temporary SystemConfig file [{$system_config_temp_path}]");
		}

		$replace_result = waFiles::move($system_config_temp_path, $this->system_config_path);
		if ($replace_result === false || filesize($this->system_config_path) === 0)
		{
			waFiles::delete($system_config_temp_path);
			waFiles::move($this->system_config_backup_path, $this->system_config_path);

			throw new waException("can't replace original SystemConfig [{$this->system_config_path}] with new one [{$system_config_temp_path}]");
		}
	}

	private function restoreOriginalSystemConfig()
	{
		if (!file_exists($this->system_config_backup_path))
		{
			throw new waException("backup file [{$this->system_config_backup_path}] doesn't exists");
		}

		try
		{
			waFiles::copy($this->system_config_backup_path, $this->system_config_path);
		}
		catch (Exception $e)
		{
			throw new waException("can't restore SystemConfig [{$this->system_config_path}] from backup file [{$this->system_config_backup_path}]: " . $e->getMessage());
		}
	}

	/**
	 * @return bool|string
	 */
	private function getSystemConfigCode()
	{
		if (function_exists('opcache_invalidate'))
		{
			@opcache_invalidate($this->system_config_path, true);
		}
		$system_config_code = file_get_contents($this->system_config_path);

		return $system_config_code;
	}
}