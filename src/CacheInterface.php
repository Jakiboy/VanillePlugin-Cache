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

use VanillePlugin\int\PluginNameSpaceInterface;

interface CacheInterface
{
    /**
     * __construct
     * @param PluginNameSpaceInterface $plugin
     */
    function __construct(PluginNameSpaceInterface $plugin);

    /**
     * __destruct
     */
    function __destruct();

    /**
     * Get cache by key.
     *
     * @param string $key
     * @return mixed
     */
    function get($key);

    /**
     * Set cache by tags.
     * 
     * @param mixed $value
     * @param mixed $tags
     * @return bool
     */
    function set($value, $tags = null);

    /**
     * Update cache by key.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    function update($key, $value);

    /**
     * Delete cache by key.
     *
     * @param string $key
     * @return bool
     */
    function delete($key);

    /**
     * Delete cache by tags.
     *
     * @param mixed $tags
     * @return bool
     */
    function deleteByTag($tags);

    /**
     * Check cache.
     *
     * @param void
     * @return bool
     */
    function isCached();

    /**
     * flush cache.
     *
     * @param void
     * @return void
     */
    function flush();

    /**
     * Set global cache expiration.
     * 
     * @param int $ttl
     * @return void
     */
    static function expireIn($ttl = 30);
}
