<?php
/**
 * @author     : Jakiboy
 * @package    : VanillePlugin
 * @version    : 1.0.0
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://jakiboy.github.io/VanillePlugin/
 * @license    : MIT
 *
 * This file if a part of VanillePlugin Framework.
 */

declare(strict_types=1);

namespace VanilleCache\inc;

use Phpfastcache\Proxy\PhpfastcacheAbstractProxy;
use VanilleCache\exc\CacheException;

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
	 * @access private
	 * @var object $item, Cache item
	 */
	private $item;
    
	/**
	 * Get cache.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$this->item = $this->instance->getItem($key);
		return $this->item->get();
	}

	/**
	 * Check cache.
	 *
	 * @access public
	 * @return bool
	 * @throws CacheException
	 */
	public function isCached() : bool
	{
		if ( !$this->hasObject('interface', $this->item, 'ExtendedCacheItemInterface') ) {
	        throw new CacheException(
	            CacheException::invalidCacheItem()
	        );
		}
		return $this->item->isHit();
	}

	/**
	 * Set cache value,
	 * Using tag(s).
	 *
	 * @access public
	 * @param mixed $value
	 * @param mixed $tag
	 * @param int $ttl
	 * @return bool
	 * @throws CacheException
	 */
	public function set($value, $tag = null, ?int $ttl = null) : bool
	{
		if ( !$this->hasObject('interface', $this->item, 'ExtendedCacheItemInterface') ) {
	        throw new CacheException(
	            CacheException::invalidCacheItem()
	        );
		}
		$this->item->set($value);
		if ( $ttl ) {
			$this->item->expiresAfter($ttl);
		}
		if ( $tag ) {
			if ( $this->isType('array', $tag) ) {
				$this->item->addTags($tag);

			} else {
				$this->item->addTag((string)$tag);
			}
		}
		return $this->instance->save($this->item);
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
}
