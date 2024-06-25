<?php
/**
 * @author    : Jakiboy
 * @package   : VanillePlugin
 * @version   : 1.0.x
 * @copyright : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link      : https://jakiboy.github.io/VanillePlugin/
 * @license   : MIT
 *
 * This file if a part of VanillePlugin Framework.
 */

declare(strict_types=1);

namespace VanilleCache;

use VanilleCache\inc\{
	FileCache, RedisCache
};
use VanilleCache\exc\CacheException;

/**
 * Built-in cache factory.
 */
class Cache
{
	use \VanillePlugin\tr\TraitFormattable;

	/**
	 * @access private
	 * @var object $instance, Cache instance
	 * @var object DRIVERS, Cache drivers
	 */
	private static $instance;
	private const DRIVERS = ['File', 'Redis'];

	/**
	 * Instance cache driver.
	 *
	 * @access public
	 * @param string $driver
	 * @param array $config
	 */
	public function __construct(string $driver = 'File', array $config = [])
	{
		if ( !self::$instance ) {

			if ( !$this->inArray($driver, self::DRIVERS) ) {
				throw new CacheException(
					CacheException::invalidCacheDriver($driver)
				);
			}

			if ( $driver == 'Redis' ) {
				self::$instance = new RedisCache($config);

			} else {
				self::$instance = new FileCache($config);
			}
			
			if ( !$this->hasObject('interface', self::$instance, 'cache') ) {
				throw new CacheException(
					CacheException::invalidCacheInstance()
				);
			}
			
		}
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, ?bool &$status = null)
	{
		$key = $this->formatKey($key);
		return self::$instance->get($key, $status);
	}

	/**
	 * @inheritdoc
	 */
	public function has(string $key) : bool
	{
		return self::$instance->has($key);
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $key, $value, ?int $ttl = null, $tag = null) : bool
	{
		return self::$instance->set($key, $value, $ttl, $tag);
	}

	/**
	 * @inheritdoc
	 */
	public function delete($key) : bool
	{
		if ( $this->isType('array', $key) ) {
			self::$instance->deleteMany($key);
		}

		return self::$instance->delete(
			$this->formatKey((string)$key)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function deleteByTag($tag) : bool
	{
		if ( $this->isType('array', $tag) ) {
			return self::$instance->deleteByTags($tag);
		}
		
		return self::$instance->deleteByTag(
			$this->formatKey((string)$tag)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function purge() : bool
	{
		return self::$instance->purge();
	}
}
