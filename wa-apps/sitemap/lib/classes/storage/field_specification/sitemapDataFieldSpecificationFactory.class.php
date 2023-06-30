<?php

class sitemapDataFieldSpecificationFactory
{
	public function integer($default_value = 0)
	{
		return new sitemapIntegerDataFieldSpecification($default_value);
	}

	public function string($default_value = '')
	{
		return new sitemapStringDataFieldSpecification($default_value);
	}

	public function double($default_value = 0)
	{
		return new sitemapDoubleDataFieldSpecification($default_value);
	}

	public function boolean($default_value = false)
	{
		return new sitemapBooleanDataFieldSpecification($default_value);
	}

	public function jsonArray(sitemapIDataFieldSpecification $array_element_specification, $default_value = array())
	{
		return new sitemapJsonArrayDataFieldSpecification($array_element_specification, $default_value);
	}

	public function enum(sitemapEnumOptions $enum_options, $default_value)
	{
		return new sitemapEnumDataFieldSpecification($enum_options, $default_value);
	}
}