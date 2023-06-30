<?php

abstract class sitemapPropertyAccess
{
	protected $entity_array = array();

	public function __construct($entity_array = null)
	{
		if (is_array($entity_array))
		{
			$this->entity_array = $entity_array;
		}
	}

	function __get($name)
	{
		return $this->entity_array[$name];
	}

	function __set($name, $value)
	{
		$this->entity_array[$name] = $value;
	}

	function __isset($name)
	{
		return isset($this->entity_array[$name]);
	}

	public function getAssoc()
	{
		return $this->entity_array;
	}

	public function getEntityRaw()
	{
		return $this->entity_array;
	}

	protected function getEntityFieldValue($name)
	{
		return $this->entity_array[$name];
	}
}