<?php

class mem
{
	private static $obj;
	private static $debug = true;

	public static function connect()
	{
		self::$obj = memcache_connect('127.0.0.1', 11211);
	}

	/**
	 * Возвращает значение ключа $key, если не истекло значение $ttl для него
	 * Если истекло - возвращает $result и устанавливает новое значение $ttl для
	 * борьбы с одновременным перестроением кеша - пока будет создаваться новый кеш
	 * все остальным посетителям будет отдаваться старый.
	 *
	 * @param $key
	 * @param $ttl
	 * @param bool $result
	 * @return bool|void
	 */
	public static function get_with_ttl($key, $ttl, $result = false)
	{
		if (!self::$obj) {
			self::connect();
		}
		$time_update = memcache_get(self::$obj, 'key_ttl:' . $key);
		if ($time_update < 1) {
			mem::set('key_ttl:' . $key, time(), 0, 0);
			return $result;
		}

		if ($time_update < time() - $ttl) {
			mem::set('key_ttl:' . $key, time(), 0, 0);
			return $result;
		}

		$result = mem::get($key);
		return $result;
	}

	public static function set_with_ttl($key, $var)
	{
		return mem::set($key, $var, 0, 86400); // day
	}

	public static function set_with_lock($key, $var, $compress, $expire)
	{
		if (!self::$obj) {
			self::connect();
		}
		memcache_set(self::$obj, $key, $var, $compress, $expire);
		memcache_delete(self::$obj, 'lock:' . $key);
	}

	/**
	 * @param $key
	 */
	public static function get_with_lock($key)
	{
		if (!self::$obj) {
			self::connect();
		}

		while (memcache_get(self::$obj, 'lock:' . $key) == 1) {
			usleep(100000); // 0.1 секунды
		}

		$result = @memcache_get(self::$obj, $key);

		if ($result == false) {
			memcache_set(self::$obj, 'lock:' . $key, 1, 0, 7);
		}

		return $result;
	}

	public static function get($key)
	{
		if (!self::$obj) {
			self::connect();
		}
		return @memcache_get(self::$obj, $key);
	}

	public static function set($key, $var, $compress, $expire)
	{
		if (!self::$obj) {
			self::connect();
		}
		return @memcache_set(self::$obj, $key, $var, 0, $expire);
	}

	public static function del($key)
	{
		if (!self::$obj) {
			self::connect();
		}
		@memcache_delete(self::$obj, $key, 0);
	}

	public static function flush()
	{
		if (!self::$obj) {
			self::connect();
		}
		@memcache_flush(self::$obj);
	}

	public static function lock($key)
	{
		$lock = 'lock_' . $key;
		while (!memcache_add(self::$obj, $lock, 1, false, 30)) {
			usleep(100);
		}
		return true;
	}

	public static function unlock($key)
	{
		$lock = 'lock_' . $key;
		memcache_delete(self::$obj, $lock);
	}

	public static function stats()
	{
		//echo '<!-- memcache reads:' . self::$mem_reads . 'sets:' . self::$mem_sets . '-->';
	}
}