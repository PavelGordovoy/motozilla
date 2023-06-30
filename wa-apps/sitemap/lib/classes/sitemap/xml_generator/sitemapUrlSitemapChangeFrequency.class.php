<?php

/**
 * Class sitemapUrlSitemapChangeFrequency
 *
 * @property-read string $always
 * @property-read string $hourly
 * @property-read string $daily
 * @property-read string $weekly
 * @property-read string $monthly
 * @property-read string $yearly
 * @property-read string $never
 */
class sitemapUrlSitemapChangeFrequency
{
	const ALWAYS = 'always';
	const HOURLY = 'hourly';
	const DAILY = 'daily';
	const WEEKLY = 'weekly';
	const MONTHLY = 'monthly';
	const YEARLY = 'yearly';
	const NEVER = 'never';

	public $always = self::ALWAYS;
	public $hourly = self::HOURLY;
	public $daily = self::DAILY;
	public $weekly = self::WEEKLY;
	public $monthly = self::MONTHLY;
	public $yearly = self::YEARLY;
	public $never = self::NEVER;

	//public function __get($name)
	//{
	//	$constant_name = 'sitemapUrlSitemapChangeFrequency::' . strtoupper($name);
	//
	//	if (!defined($constant_name))
	//	{
	//		throw new sitemapException("unknown constant [{$constant_name}]");
	//	}
	//
	//	return constant($constant_name);
	//}
	public function __set($name, $value)
	{
		throw new sitemapException('fields of class [sitemapUrlSitemapChangeFrequency] are read-only');
	}
}