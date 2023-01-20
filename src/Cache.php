<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : VanillePlugin
 * @subpackage : VanilleCache
 * @version    : 0.1.3
 * @copyright  : (c) 2018 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
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
use Phpfastcache\Exceptions\PhpfastcacheIOException;
use \Exception;
use \FilesystemIterator;

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
		try {

			if ( $this->adapter ) {
				$key = $this->formatKey($key);
				$this->cache = $this->adapter->getItem($key);
				return $this->cache->get();
			}
			
		} catch (Exception $e) {
			ErrorHandler::clearLastError();

		} catch (PhpfastcacheIOException $e) {
			ErrorHandler::clearLastError();

		} catch (FilesystemIterator $e) {
			ErrorHandler::clearLastError();
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
		try {

			if ( $this->adapter ) {
				$this->cache->set($value)
				->expiresAfter(self::$ttl);
				if ( $tags ) {
					if ( TypeCheck::isArray($tags) ) {
						foreach ($tags as $key => $value) {
							$tags[$key] = $this->formatKey($value);
						}
						$this->cache->addTags($tags);
					} else {
						$tags = $this->formatKey($tags);
						$this->cache->addTag($tags);
					}
				}
				return $this->adapter->save($this->cache);
			}

		} catch (Exception $e) {
			ErrorHandler::clearLastError();

		} catch (PhpfastcacheIOException $e) {
			ErrorHandler::clearLastError();

		} catch (FilesystemIterator $e) {
			ErrorHandler::clearLastError();
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
		try {

			if ( $this->adapter ) {
				$key = $this->formatKey($key);
				$this->cache = $this->adapter->getItem($key);
				$this->cache->set($value)
				->expiresAfter(self::$ttl);
				return $this->adapter->save($this->cache);
			}

		} catch (Exception $e) {
			ErrorHandler::clearLastError();

		} catch (PhpfastcacheIOException $e) {
			ErrorHandler::clearLastError();

		} catch (FilesystemIterator $e) {
			ErrorHandler::clearLastError();
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
		try {

			if ( $this->adapter ) {
				$key = $this->formatKey($key);
				return $this->adapter->deleteItem($key);
			}
			
		} catch (Exception $e) {
			ErrorHandler::clearLastError();

		} catch (PhpfastcacheIOException $e) {
			ErrorHandler::clearLastError();

		} catch (FilesystemIterator $e) {
			ErrorHandler::clearLastError();
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
		try {

			if ( $this->adapter ) {
				if ( TypeCheck::isArray($tags) ) {
					foreach ($tags as $key => $value) {
						$tags[$key] = $this->formatKey($value);
					}
					return $this->adapter->deleteItemsByTags($tags);
				} else {
					$tags = $this->formatKey($tags);
					return $this->adapter->deleteItemsByTag($tags);
				}
			}
			
		} catch (Exception $e) {
			ErrorHandler::clearLastError();

		} catch (PhpfastcacheIOException $e) {
			ErrorHandler::clearLastError();

		} catch (FilesystemIterator $e) {
			ErrorHandler::clearLastError();
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
		$dir = $this->getTempPath();
		if ( File::isDir($dir) && Stringify::contains($dir,"/{$this->getNameSpace()}/") ) {
			File::clearDir($dir);
		}

		// Secured removing: template cache on debug
		if ( $this->isDebug() ) {
			$dir = $this->getCachePath();
			if ( File::isDir($dir) && Stringify::contains($dir,"/{$this->getNameSpace()}/") ) {
				File::clearDir($dir);
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

	/**
	 * @access protected
	 * @param int|string $key
	 * @return string
	 */
	protected function formatKey($key)
	{
		return Stringify::sanitizeKey($key);
	}
}
