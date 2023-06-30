<?php

abstract class sitemapSettingsAccess extends sitemapPropertyAccess
{
	public function __construct($entity_array = null)
	{
		parent::__construct($entity_array);

		foreach ($this->getFields() as $field)
		{
			if (!array_key_exists($field, $this->entity_array))
			{
				$this->entity_array[$field] = null;
			}
		}
	}

	protected abstract function getFields();
}