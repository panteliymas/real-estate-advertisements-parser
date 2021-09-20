<?php
/**
 * Created by PhpStorm.
 * User: f4soft
 * Date: 22.12.13
 * Time: 21:45
 */

class Settings
{
	protected static $_instance;

	private function __construct()
	{

	}

	private function __clone()
	{
	}

	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function get($setting)
	{
		$value = Router::$controller->db->super_query("SELECT value FROM settings WHERE name='{$setting}'");
		return $value['value'];
	}

	public static function set($name, $value)
	{
		$controller = Router::$controller;
		$name = $controller->db->safesql($name);
		$value = $controller->db->safesql($value);
		$controller->db->query("REPLACE INTO settings (name, value) VALUES ('{$name}', '{$value}')");
	}

}