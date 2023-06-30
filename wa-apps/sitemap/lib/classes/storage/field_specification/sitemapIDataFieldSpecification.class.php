<?php

interface sitemapIDataFieldSpecification
{
	public function toAccessible($raw_value);

	public function toStorable($value);

	public function defaultValue();
}