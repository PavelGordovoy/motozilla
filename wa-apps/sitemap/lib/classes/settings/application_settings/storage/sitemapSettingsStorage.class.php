<?php

abstract class sitemapSettingsStorage
{
	const DEFAULT_DOMAIN_ID = 0;
	const DEFAULT_ROUTE_URL = '*';

	/** @var sitemapIDataFieldSpecification[][] */
	private static $_access_specifications = array();

	protected $_class;

	protected $settings_model;
	protected $model;

	public function __construct()
	{
		$this->_class = get_class($this);
		$this->settings_model = new sitemapSettingsModel();
		$this->model = new waModel();

		if (!array_key_exists($this->_class, self::$_access_specifications))
		{
			self::$_access_specifications[$this->_class] = $this->accessSpecification();
		}
	}

	/**
	 * @return sitemapIDataFieldSpecification[]
	 */
	abstract protected function accessSpecification();

	abstract protected function getAppId();

	/**
	 * @param string $name
	 * @return sitemapIDataFieldSpecification|null
	 */
	protected function getFieldSpecification($name)
	{
		if (!array_key_exists($name, self::$_access_specifications[$this->_class]))
		{
			return null;
		}

		return self::$_access_specifications[$this->_class][$name];
	}

	protected function fetchRaw($domain_id, $route_url)
	{
		return $this->settings_model
			->select('name,value')
			->where('app_id = s:app_id', array('app_id' => $this->getAppId()))
			->where('domain_id = :domain_id', array('domain_id' => $domain_id))
			->where('route_url = s:route_url', array('route_url' => $route_url))
			->fetchAll('name', true);
	}

	protected function getForAccessible($domain_id, $route_url)
	{
		$stored_settings = $this->fetchRaw($domain_id, $route_url);

		return $this->prepareStorableForAccessible($stored_settings);
	}

	protected function store($domain_id, $route_url, $accessible_assoc)
	{
		$settings_storable = $this->prepareAccessibleToStorable($accessible_assoc);

		foreach (array_keys($settings_storable) as $field)
		{
			if (!array_key_exists($field, $accessible_assoc))
			{
				continue;
			}

			$value = $accessible_assoc[$field];

			if ($value === null)
			{
				$this->settings_model->deleteByField(array(
					'app_id' => $this->getAppId(),
					'domain_id' => $domain_id,
					'route_url' => $route_url,
					'name' => $field,
				));
			}
			else
			{
				$this->settings_model->replace(array(
					'app_id' => $this->getAppId(),
					'domain_id' => $domain_id,
					'route_url' => $route_url,
					'name' => $field,
					'value' => $this->getFieldSpecification($field)->toStorable($value),
				));
			}
		}
	}


	/**
	 * @return sitemapSettingsModel
	 */
	protected function getModel()
	{
		return $this->settings_model;
	}

	protected function prepareStorableForAccessible($data_raw)
	{
		$data_for_accessible = array();

		foreach (self::$_access_specifications[$this->_class] as $field => $specification)
		{
			$data_for_accessible[$field] = array_key_exists($field, $data_raw)
				? $specification->toAccessible($data_raw[$field])
				: $specification->defaultValue();
		}

		return $data_for_accessible;
	}

	protected function prepareAccessibleToStorable(array $accessible_assoc)
	{
		$storable = array();

		foreach (self::$_access_specifications[$this->_class] as $field => $specification)
		{
			$storable[$field] = array_key_exists($field, $accessible_assoc)
				? $specification->toStorable($accessible_assoc[$field])
				: $specification->defaultValue();
		}

		return $storable;
	}
}