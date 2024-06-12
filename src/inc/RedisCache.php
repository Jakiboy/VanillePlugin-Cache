<?php
/**
 * @author    : Jakiboy
 * @package   : VanillePlugin
 * @version   : 1.0.2
 * @copyright : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link      : https://jakiboy.github.io/VanillePlugin/
 * @license   : MIT
 *
 * This file if a part of VanillePlugin Framework.
 */

declare(strict_types=1);

namespace VanilleCache\inc;

use VanilleCache\int\CacheInterface;
use Phpfastcache\{
	CacheManager,
	Drivers\Redis\Config
};

/**
 * Wrapper class for RedisCache.
 * @see https://www.phpfastcache.com
 */
final class RedisCache extends ProxyCache implements CacheInterface
{
	/**
	 * @inheritdoc
	 */
    public function __construct(array $config = [])
    {
		// Reset instance
		CacheManager::clearInstances();

		// Init path
		$config = $this->mergeArray([
			'host'       => '127.0.0.1',
			'port'       => 6379,
			'password'   => '',
			'database'   => 0,
			'defaultTtl' => $this->getExpireIn()
		], $config);

		if ( isset($config['path']) ) {
			unset($config['path']);
		}

		// Init instance
		try {
			$this->instance = CacheManager::getInstance('Redis', new Config($config));

		} catch (\Phpfastcache\Exceptions\PhpfastcacheDriverConnectException $e) {

			$this->clearLastError();
			if ( $this->hasDebug() ) {
				$this->error('Redis cache failed');
				$this->debug($e->getMessage());
			}

		} catch (\Phpfastcache\Exceptions\PhpfastcacheDriverCheckException $e) {

			$this->clearLastError();
			if ( $this->hasDebug() ) {
				$this->error('Redis cache driver failed');
				$this->debug($e->getMessage());
			}
		}

		// Set backup instance
		if ( !$this->instance ) {
			$this->instance = CacheManager::getInstance('Files');
		}

		// Reset configuration
		$this->resetConfig();
    }
}
