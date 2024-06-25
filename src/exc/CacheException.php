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

namespace VanilleCache\exc;

class CacheException extends \Exception
{
	public static function invalidCacheDriver($driver)
	{
		return "Invalid cache driver : {$driver}";
	}

    public static function invalidCacheInstance()
    {
        return 'Invalid cache instance : Must implements CacheInterface';
    }
}
