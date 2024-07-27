<?php
/**
 * @author    : Jakiboy
 * @package   : VanillePlugin
 * @version   : 0.9.x
 * @copyright : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link      : https://jakiboy.github.io/VanillePlugin/
 * @license   : MIT
 *
 * This file if a part of VanillePlugin Framework.
 */

declare(strict_types=1);

namespace VanilleCache\inc;

use Phpfastcache\Proxy\PhpfastcacheAbstractProxy;
use VanillePlugin\inc\TypeCheck;

/**
 * Wrapper class for AbstractCache.
 * @see https://www.phpfastcache.com
 */
class ProxyCache extends PhpfastcacheAbstractProxy
{
	use \VanillePlugin\VanillePluginConfig;

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
	 * @param string $group
	 * @return bool
	 */
	public function set(string $key, $value, ?int $ttl = null, ?string $group = null) : bool
	{
		$item = $this->getItem($key);
		$item->set($value);

		if ( !TypeCheck::isNull($ttl) ) {
			$item->expiresAfter($ttl);
		}

		if ( !TypeCheck::isNull($group) ) {
			$item->addTag($group);
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
	protected function getItem(string $key) : object
	{
		return $this->instance->getItem($key);
	}
}
