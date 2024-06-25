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

namespace VanilleCache\inc;

use Phpfastcache\Proxy\PhpfastcacheAbstractProxy;

/**
 * Wrapper class for AbstractCache.
 * @see https://www.phpfastcache.com
 */
class ProxyCache extends PhpfastcacheAbstractProxy
{
	use \VanillePlugin\VanillePluginConfig,
		\VanillePlugin\tr\TraitThrowable,
		\VanillePlugin\tr\TraitLoggable;

	/**
	 * Instance cache driver.
	 *
	 * @access public
	 * @param string $driver
	 * @param mixed $config
	 */
	public function __construct(string $driver = 'File', $config = null)
	{
		parent::__construct($driver, $config);
	}

	/**
	 * Get cache.
	 *
	 * @access public
	 * @param string $key
	 * @param bool $status
	 * @return mixed
	 */
	public function get(string $key, ?bool &$status = null)
	{
		$data   = $this->getItem($key)->get();
		$status = $this->has($key);
		return $data;
	}

	/**
	 * Check cache status.
	 *
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key) : bool
	{
		return $this->getItem($key)->isHit();
	}

	/**
	 * Set cache value.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 * @param mixed $tag
	 * @return bool
	 */
	public function set(string $key, $value, ?int $ttl = null, $tag = null) : bool
	{
		$item = $this->getItem($key);
		$item->set($value);

		if ( $ttl ) {
			$item->expiresAfter($ttl);
		}

		if ( $this->isType('null', $tag) ) {
			$tag = $this->getNamespace();
		}

		if ( $this->isType('array', $tag) ) {
			$item->addTags($tag);

		} else {
			$item->addTag((string)$tag);
		}

		return $this->instance->save($item);
	}

	/**
	 * Delete cache by key.
	 *
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function delete(string $key) : bool
	{
		return $this->instance->deleteItem($key);
	}

	/**
	 * Delete caches by keys.
	 *
	 * @access public
	 * @param array $keys
	 * @return bool
	 */
	public function deleteMany(array $keys) : bool
	{
		return $this->instance->deleteItems($keys);
	}

	/**
	 * Delete cache by tag.
	 *
	 * @access public
	 * @param string $tag
	 * @return bool
	 */
	public function deleteByTag(string $tag) : bool
	{
		return $this->instance->deleteItemsByTag($tag);
	}

	/**
	 * Delete cache by tags.
	 *
	 * @access public
	 * @param array $tags
	 * @return bool
	 */
	public function deleteByTags(array $tags) : bool
	{
		return $this->instance->deleteItemsByTags($tags);
	}

	/**
	 * Purge any cache.
	 *
	 * @access public
	 * @return bool
	 */
	public function purge() : bool
	{
		return $this->instance->clear();
	}

	/**
	 * Get cache item.
	 *
	 * @access protected
	 * @param string $key
	 * @return object
	 */
	protected function getItem(string $key)
	{
		return $this->instance->getItem($key);
	}
}
