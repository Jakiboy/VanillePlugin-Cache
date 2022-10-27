<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : VanillePlugin
 * @subpackage : VanilleCache
 * @version    : 0.1.0
 * @copyright  : (c) 2018 - 2022 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link       : https://jakiboy.github.io/VanillePlugin/
 * @license    : MIT
 *
 * This file if a part of VanillePlugin Framework.
 */

declare(strict_types=1);

namespace VanilleCache;

use VanillePlugin\VanillePluginConfig;
use VanillePlugin\int\PluginNameSpaceInterface;
use VanillePlugin\inc\File;
use VanillePlugin\inc\Stringify;
use VanillePlugin\inc\TypeCheck;
use VanillePlugin\inc\Exception as ErrorHandler;
use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Config;
use \Exception;

/**
 * Wrapper Class for External FileCache,
 * Includes Third-Party & Template Cache Helper.
 * 
 * @see https://jakiboy.github.io/VanillePlugin/
 * @see https://www.phpfastcache.com/
 * @deprecated
 */
class Cache implements CacheInterface
{
	use VanillePluginConfig;

	/**
	 * @access private
	 * @var object $cache, cache object
	 * @var object $adapter, adapter object
	 * @var int $ttl, cache TTL
	 */
	private $cache = false;
	private $adapter = false;
	private static $ttl = false;

	/**
	 * @param PluginNameSpaceInterface $plugin
	 */
	public function __construct(PluginNameSpaceInterface $plugin)
	{
		// Init plugin config
		$this->initConfig($plugin);

		// Set default ttl
		if ( !self::$ttl ) {
			self::expireIn($this->getExpireIn());
		}

		// Set adapter default config
		CacheManager::setDefaultConfig(new Config([
			'path'               => $this->getTempPath(),
			'autoTmpFallback'    => true,
			'compressData'       => true,
			'preventCacheSlams'  => true,
			'cacheSlamsTimeout'  => 10,
			'defaultChmod'       => 0755,
			'securityKey'        => 'private',
			'cacheFileExtension' => 'db'
		]));

		// Init adapter
		$this->reset();
		try {
			$this->adapter = CacheManager::getInstance('Files');
		} catch (Exception $e) {
			ErrorHandler::clearLastError();
		}
	}

	/**
	 * Clear adapter instances.
	 */
	public function __destruct()
	{
		$this->reset();
	}

	/**
	 * Get cache by key.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if ( $this->adapter ) {
			$key = Stringify::formatKey($key);
			$this->cache = $this->adapter->getItem($key);
			return $this->cache->get();
		}
		return false;
	}

	/**
	 * Set cache by tags.
	 *
	 * @access public
	 * @param mixed $value
	 * @param mixed $tags
	 * @return bool
	 */
	public function set($value, $tags = null)
	{
		if ( $this->adapter ) {
			$this->cache->set($value)
			->expiresAfter(self::$ttl);
			if ( $tags ) {
				if ( TypeCheck::isArray($tags) ) {
					foreach ($tags as $key => $value) {
						$tags[$key] = Stringify::formatKey($value);
					}
					$this->cache->addTags($tags);
				} else {
					$tags = Stringify::formatKey($tags);
					$this->cache->addTag($tags);
				}
			}
			return $this->adapter->save($this->cache);
		}
		return false;
	}

	/**
	 * Update cache by key.
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	public function update($key, $value)
	{
		if ( $this->adapter ) {
			$key = Stringify::formatKey($key);
			$this->cache = $this->adapter->getItem($key);
			$this->cache->set($value)
			->expiresAfter(self::$ttl);
			return $this->adapter->save($this->cache);
		}
		return false;
	}

	/**
	 * Delete cache by key.
	 *
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function delete($key)
	{
		if ( $this->adapter ) {
			$key = Stringify::formatKey($key);
			return $this->adapter->deleteItem($key);
		}
		return false;
	}

	/**
	 * Delete cache by tags.
	 *
	 * @access public
	 * @param mixed $tags
	 * @return bool
	 */
	public function deleteByTag($tags)
	{
		if ( $this->adapter ) {
			if ( TypeCheck::isArray($tags) ) {
				foreach ($tags as $key => $value) {
					$tags[$key] = Stringify::formatKey($value);
				}
				return $this->adapter->deleteItemsByTags($tags);
			} else {
				$tags = Stringify::formatKey($tags);
				return $this->adapter->deleteItemsByTag($tags);
			}
		}
		return false;
	}

	/**
	 * Check cache.
	 *
	 * @access public
	 * @param void
	 * @return bool
	 */
	public function isCached()
	{
		if ( $this->cache ) {
			return $this->cache->isHit();
		}
		return false;
	}

	/**
	 * Get cache TTL.
	 *
	 * @access public
	 * @param void
	 * @return mixed
	 */
	public function getTTL()
	{
		if ( $this->cache ) {
			return $this->cache->getTtl();
		}
		return false;
	}

	/**
	 * Get cache tags.
	 *
	 * @access public
	 * @param void
	 * @return mixed
	 */
	public function getTags()
	{
		if ( $this->cache ) {
			return $this->cache->getTags();
		}
		return false;
	}

	/**
	 * flush cache.
	 *
	 * @access public
	 * @param void
	 * @return void
	 */
	public function flush()
	{
		// Secured removing: filecache
		if ( File::isDir($this->getTempPath()) ) {
			if ( Stringify::contains($this->getTempPath(),$this->getRoot()) ) {
				File::clearDir($this->getTempPath());
			}
		}

		// Secured removing: template cache on debug
		if ( $this->isDebug() ) {
			if ( File::isDir($this->getCachePath()) ) {
				if ( Stringify::contains($this->getCachePath(),$this->getRoot()) ) {
					File::clearDir($this->getCachePath());
				}
			}
		}
	}

	/**
	 * Set global cache expiration.
	 * 
	 * @access public
	 * @param int $ttl
	 * @return void
	 */
	public static function expireIn($ttl = 30)
	{
		self::$ttl = (int)$ttl;
	}

	/**
	 * Reset adapter instance.
	 *
	 * @access protected
	 * @param void
	 * @return void
	 */
	protected function reset()
	{
		CacheManager::clearInstances();
	}
}