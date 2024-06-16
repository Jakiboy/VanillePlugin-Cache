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
use Phpfastcache\{
	CacheManager,
	Drivers\Files\Config
};

/**
 * Wrapper class for FileCache.
 * @see https://www.phpfastcache.com
 */
class FileCache extends ProxyCache implements CacheInterface
{
	/**
	 * @inheritdoc
	 */
    public function __construct(array $config = [])
    {
		// Reset instance
		CacheManager::clearInstances();

		// Init config
		$config = $this->mergeArray([
			'path'               => $this->getTempPath(),
			'autoTmpFallback'    => true,
			'compressData'       => true,
			'preventCacheSlams'  => true,
			'cacheSlamsTimeout'  => 5,
			'defaultChmod'       => 0777,
			'defaultTtl'         => $this->getExpireIn(),
			'securityKey'        => 'private',
			'cacheFileExtension' => 'txt'
		], $config);

		// Init instance
		try {
			$this->instance = CacheManager::getInstance('Files', new Config($config));

		} catch (\Phpfastcache\Exceptions\PhpfastcacheIOException $e) {

			$this->clearLastError();
			if ( $this->hasDebug() ) {
				$this->error('File cache failed');
				$this->debug($e->getMessage());
			}
		}

		// Reset plugin config
		$this->resetConfig();
    }
}
