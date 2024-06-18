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
	 * @var object DRIVERS, Valid drivers
	 */
	private $instance;
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
		// Check driver
		if ( !$this->inArray($driver, self::DRIVERS) ) {
	        throw new CacheException(
	            CacheException::invalidCacheDriver($driver)
	        );
		}

		// Instance driver
		if ( $driver == 'Redis' ) {
			$this->instance = new RedisCache($config);

		} else {
			$this->instance = new FileCache($config);
		}

		// Check instance
		if ( !$this->hasObject('interface', $this->instance, 'cache') ) {
	        throw new CacheException(
	            CacheException::invalidCacheInstance()
	        );
		}
	}

	/**
	 * Get cache.
	 * 
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		return $this->instance->get(
			$this->formatKey($key)
		);
	}

	/**
	 * Set cache key.
	 * 
	 * @access public
	 * @param string $key
	 * @return object
	 */
	public function setKey(string $key) : self
	{
		$this->get($key);
		return $this;
	}

	/**
	 * Check cache.
	 *
	 * @access public
	 * @return bool
	 */
	public function isCached() : bool
	{
		return $this->instance->isCached();
	}

	/**
	 * Set cache value.
	 * 
	 * @access public
	 * @param mixed $value
	 * @param mixed $tag
	 * @param int $ttl
	 * @return bool
	 */
	public function set($value, $tag = null, ?int $ttl = null) : bool
	{
		return $this->instance->set($value, $tag, $ttl);
	}

	/**
	 * Delete cache by key(s).
	 * 
	 * @access public
	 * @param mixed $key
	 * @return bool
	 */
	public function delete($key) : bool
	{
		if ( $this->isType('array', $key) ) {
			$this->instance->deleteMany($key);
		}

		return $this->instance->delete(
			$this->formatKey((string)$key)
		);
	}

	/**
	 * Delete cache by tag(s).
	 * 
	 * @access public
	 * @param mixed $tag
	 * @return bool
	 */
	public function deleteByTag($tag) : bool
	{
		if ( $this->isType('array', $tag) ) {
			return $this->instance->deleteByTags($tag);
		}
		
		return $this->instance->deleteByTag(
			$this->formatKey((string)$tag)
		);
	}

	/**
	 * Purge any cache.
	 * 
	 * @access public
	 * @return bool
	 */
	public function purge() : bool
	{
		return $this->instance->purge();
	}

	/**
	 * Reset instance.
	 *
	 * @access public
	 * @return void
	 */
	public function reset()
	{
		$this->instance->reset();
	}
}
