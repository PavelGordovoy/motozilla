<?php

abstract class sitemapApplicationStructureStorage
{
	const ELEMENT_PLUGIN = 'plugin';

	const DEFAULT_DOMAIN_ID = 0;
	const DEFAULT_ROUTE_URL = '*';

	private static $plugins_structure_settings = null;

	protected $structure_model;

	/** @return sitemapApplicationStructureElementSettings[] */
	abstract protected function getDefaultStructureElements();
	/** @return string */
	abstract protected function getAppId();

	public function __construct()
	{
		$this->structure_model = new sitemapApplicationStructureModel();
	}

	/**
	 * @param $domain_id
	 * @param $route_url
	 * @return sitemapApplicationStructure
	 */
	public function get($domain_id, $route_url)
	{
		$structure = new sitemapApplicationStructure();
		$structure->app_id = $this->getAppId();
		$structure->domain_id = $domain_id;
		$structure->route_url = $route_url;

		$structure_elements = $this->getStructureElements($domain_id, $route_url);

		$structure_elements = array_filter($structure_elements, array($this, 'isNotHiddenPlugin'));
		$structure_elements = $this->filterSubstructureElements($structure_elements);

		$structure->elements = $structure_elements;

		return $structure;
	}

	/**
	 * @param $domain_id
	 * @param $route_url
	 * @return sitemapApplicationStructure[]
	 */
	public function getSubstructures($domain_id, $route_url)
	{
		$specifications = $this->getSubstructuresSpecification();

		$substructures = array();

		foreach ($specifications as $substructure_id => $specification)
		{
			$structure = new sitemapApplicationStructure();
			$structure->app_id = $this->getAppId();
			$structure->domain_id = $domain_id;
			$structure->route_url = $route_url;

			$structure_elements = array();
			foreach ($this->getStructureElements($domain_id, $route_url) as $element)
			{
				$element_id = $element->element_id;
				$plugin_id = $element->plugin_id;
				if (
					($element_id == self::ELEMENT_PLUGIN && array_key_exists($element_id, $specification) && array_key_exists($plugin_id, $specification[$element_id]))
					|| ($element_id != self::ELEMENT_PLUGIN && array_key_exists($element_id, $specification))
				)
				{
					$structure_elements[] = $element;
				}
			}
			unset($element);

			$structure->elements = $structure_elements;

			$substructures[$substructure_id] = $structure;
		}

		return $substructures;
	}

	/**
	 * @param $domain_id
	 * @param $route_url
	 * @param $substructure_id
	 * @return sitemapApplicationStructure|null
	 */
	public function getSubstructure($domain_id, $route_url, $substructure_id)
	{
		$substructures = $this->getSubstructures($domain_id, $route_url);

		return array_key_exists($substructure_id, $substructures)
			? $substructures[$substructure_id]
			: null;
	}

	public function storeElements($domain_id, $route_url, $elements_assoc)
	{
		$app_id = $this->getAppId();

		$this->structure_model->deleteByField(array(
			'app_id' => $app_id,
			'domain_id' => $domain_id,
			'route_url' => $route_url,
		));

		$sort = 1;
		foreach ($elements_assoc as $element_assoc)
		{
			$result = $this->structure_model->insert(array(
				'app_id' => $app_id,
				'domain_id' => $domain_id,
				'route_url' => $route_url,
				'element_id' => $element_assoc['element_id'],
				'plugin_id' => $element_assoc['plugin_id'],
				'change_frequency' => $element_assoc['change_frequency'],
				'priority' => $element_assoc['priority'] === '' ? null : $element_assoc['priority'],
				'is_shown' => $element_assoc['is_shown'] ? sitemapApplicationStructureModel::IS_SHOWN_SHOWN : sitemapApplicationStructureModel::IS_SHOWN_HIDDEN,
				'sort' => $sort++,
			), waModel::INSERT_ON_DUPLICATE_KEY_UPDATE);

			if (!$result)
			{
				$a = 1;
			}
		}
	}

	protected function getSubstructuresSpecification()
	{
		return array();
	}

	/**
	 * @param sitemapApplicationStructureElementSettings[] $elements
	 * @return sitemapApplicationStructureElementSettings[]
	 */
	protected function filterSubstructureElements($elements)
	{
		$substructures_specification = $this->getSubstructuresSpecification();
		if (count($substructures_specification) == 0)
		{
			return $elements;
		}

		$elements_filtered = array();
		foreach ($elements as $element)
		{
			$element_id = $element->element_id;
			$plugin_id = $element->plugin_id;

			foreach ($substructures_specification as $substructure_id => $specification)
			{
				if (
					($element_id != self::ELEMENT_PLUGIN && array_key_exists($element_id, $specification))
					|| ($element_id == self::ELEMENT_PLUGIN && array_key_exists($element_id, $specification) && array_key_exists($plugin_id, $specification[$element_id]))
				)
				{
					continue 2;
				}
			}

			$elements_filtered[] = $element;
		}
		unset($element);

		return $elements_filtered;
	}

	/**
	 * @param $domain_id
	 * @param $route_url
	 * @return sitemapApplicationStructureElementSettings[]
	 */
	protected function getStructureElements($domain_id, $route_url)
	{
		$default_structure = $this->getDefaultStructure();

		$structure_element_rows = $this->fetchElements($this->getAppId(), $domain_id, $route_url);
		$general_structure_element_rows = $domain_id == self::DEFAULT_DOMAIN_ID && $route_url == self::DEFAULT_ROUTE_URL
			? array()
			: $this->fetchElements($this->getAppId(), self::DEFAULT_DOMAIN_ID, self::DEFAULT_ROUTE_URL);

		$structure_elements = $this->structureElementRawRowsToAccessible($structure_element_rows);
		$general_structure_elements = $this->structureElementRawRowsToAccessible($general_structure_element_rows);

		if (count($general_structure_elements) > 0)
		{
			$structure_elements = $this->completeStructure($structure_elements, $general_structure_elements);
		}

		$structure_elements = $this->completeStructure($structure_elements, $default_structure->elements);

		return $structure_elements;
	}

	protected function fetchElements($app_id, $domain_id, $route_url)
	{
		return $this->structure_model
			->select('element_id, plugin_id, change_frequency, priority, is_shown')
			->where('app_id = :app_id', array('app_id' => $app_id))
			->where('domain_id = :domain_id', array('domain_id' => $domain_id))
			->where('route_url = :route_url', array('route_url' => $route_url))
			->order('sort ASC')
			->fetchAll();
	}

	/**
	 * @return sitemapApplicationStructure
	 */
	protected function getDefaultStructure()
	{
		$structure = new sitemapApplicationStructure();
		$structure->app_id = $this->getAppId();
		$structure->domain_id = self::DEFAULT_DOMAIN_ID;
		$structure->route_url = self::DEFAULT_ROUTE_URL;
		$structure->elements = array();

		$elements = $this->getDefaultStructureElements();

		$structure->elements = $elements;

		return $structure;
	}

	/**
	 * @return sitemapApplicationStructureElementSettings[]
	 */
	protected function collectPluginElements()
	{
		$plugin_elements = array();

		$app_id = $this->getAppId();
		try
		{
			$system = wa($app_id);
		}
		catch (Exception $e)
		{
			$system = null;
		}

		if (!$system)
		{
			return array();
		}

		$app_config = $system->getConfig();
		if (!$app_config->getInfo('plugins'))
		{
			return array();
		}

		$plugins = $app_config->getPlugins();

		foreach ($plugins as $plugin_id => $plugin_config)
		{
			if (
				array_key_exists('app_id', $plugin_config)
				&& $plugin_config['app_id'] == $app_id
				&& array_key_exists('handlers', $plugin_config)
				&& is_array($plugin_config['handlers'])
				&& array_key_exists('sitemap', $plugin_config['handlers'])
			)
			{
				$plugin = new sitemapApplicationStructureElementSettings();
				$plugin->element_id = self::ELEMENT_PLUGIN;
				$plugin->plugin_id = $plugin_id;
				//$plugin->lastmod_mode = 1;
				$plugin->change_frequency = null;
				$plugin->priority = null;
				$plugin->is_shown = true;

				$plugin_elements[$plugin_id] = $plugin;
			}
		}
		unset($plugin);

		return $plugin_elements;
	}

	/**
	 * @param $plugin_id
	 * @return sitemapPluginStructureSettings|null
	 */
	protected function getPluginStructureSettings($plugin_id)
	{
		if (!is_array(self::$plugins_structure_settings))
		{
			self::$plugins_structure_settings = wa()->event(array($this->getAppId(), 'app_sitemap_structure'));

			if (!is_array(self::$plugins_structure_settings))
			{
				self::$plugins_structure_settings = array();
			}
		}

		$plugin_param_key = $plugin_id . '-plugin';

		return array_key_exists($plugin_param_key, self::$plugins_structure_settings)
			? new sitemapPluginStructureSettings(self::$plugins_structure_settings[$plugin_param_key])
			: null;
	}

	/**
	 * @param array $structure_element_rows
	 * @return sitemapApplicationStructureElementSettings[]
	 */
	protected function structureElementRawRowsToAccessible($structure_element_rows)
	{
		$structure_elements = array();
		foreach ($structure_element_rows as $structure_element_row)
		{
			$structure_element = new sitemapApplicationStructureElementSettings($structure_element_row);
			$structure_element->is_shown = $structure_element->is_shown == sitemapApplicationStructureModel::IS_SHOWN_SHOWN;

			$structure_elements[] = $structure_element;
		}
		unset($structure_element);

		return $structure_elements;
	}

	/**
	 * @param sitemapApplicationStructureElementSettings[] $structure_elements
	 * @param sitemapApplicationStructureElementSettings[] $default_structure_elements
	 * @return array
	 */
	private function completeStructure($structure_elements, $default_structure_elements)
	{
		$excluded_element_ids = $excluded_plugin_ids = array();

		foreach ($structure_elements as $structure_element)
		{
			$element_id = $structure_element->element_id;
			$plugin_id = $structure_element->plugin_id;

			$excluded_element_ids[$element_id] = $element_id;

			if ($element_id == self::ELEMENT_PLUGIN)
			{
				$excluded_plugin_ids[$plugin_id] = $plugin_id;
			}
		}

		foreach ($default_structure_elements as $default_element)
		{
			if (
				($default_element->element_id == self::ELEMENT_PLUGIN && !array_key_exists($default_element->plugin_id, $excluded_plugin_ids))
				|| ($default_element->element_id != self::ELEMENT_PLUGIN && !array_key_exists($default_element->element_id, $excluded_element_ids))
			)
			{
				$structure_elements[] = $default_element;
			}
		}
		unset($default_element);

		return $structure_elements;
	}

	private function isNotHiddenPlugin(sitemapApplicationStructureElementSettings $element)
	{
		if ($element->element_id == self::ELEMENT_PLUGIN)
		{
			$plugin_structure_settings = $this->getPluginStructureSettings($element->plugin_id);
			if ($plugin_structure_settings && !$plugin_structure_settings->isShown())
			{
				return false;
			}
		}

		return true;
	}
}