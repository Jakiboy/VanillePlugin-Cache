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

use VanilleCache\int\CacheInterface;
use Phpfastcache\Drivers\Redis\Config;

/**
 * Wrapper class for RedisCache.
 * @see https://www.phpfastcache.com
 */
final class RedisCache extends ProxyCache implements CacheInterface
{
	/**
	 * @access private
	 * @var bool $initialized
	 */
	private static $initialized = false;

	/**
	 * @inheritdoc
	 */
    public function __construct(array $config = [])
    {
		if ( !static::$initialized ) {

			unset($config['path']);
			$config = $this->mergeArray([
				'host'       => '127.0.0.1',
				'port'       => 6379,
				'password'   => '',
				'database'   => 0,
				'defaultTtl' => $this->getExpireIn()
			], $config);
	
			try {
				parent::__construct('Redis', new Config($config));
	
			} catch (
				\Phpfastcache\Exceptions\PhpfastcacheDriverConnectException |
				\Phpfastcache\Exceptions\PhpfastcacheDriverCheckException $e
			) {
	
				$this->clearLastError();
				if ( $this->hasDebug() ) {
					$this->error('Redis cache failed');
					$this->debug($e->getMessage());
				}
			}

			if ( !$this->instance ) {
				$this->instance = new FileCache();
			}
	
			// Reset config
			$this->resetConfig();
			
		}
    }
}
