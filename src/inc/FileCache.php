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
use Phpfastcache\Drivers\Files\Config;

/**
 * Wrapper class for FileCache.
 * @see https://www.phpfastcache.com
 */
final class FileCache extends ProxyCache implements CacheInterface
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

			$config = $this->mergeArray([
				'path'               => $this->getTempPath(),
				'autoTmpFallback'    => true,
				'compressData'       => true,
				'preventCacheSlams'  => true,
				'cacheSlamsTimeout'  => 3,
				'defaultChmod'       => 0777,
				'defaultTtl'         => $this->getExpireIn(),
				'securityKey'        => 'private',
				'cacheFileExtension' => 'txt'
			], $config);
	
			try {
	
				parent::__construct('Files', new Config($config));
	
			} catch (\Phpfastcache\Exceptions\PhpfastcacheIOException $e) {
	
				$this->clearLastError();
				if ( $this->hasDebug() ) {
					$this->error('File cache failed');
					$this->debug($e->getMessage());
				}
			}
	
			// Reset config
			$this->resetConfig();
			
		}
    }
}
