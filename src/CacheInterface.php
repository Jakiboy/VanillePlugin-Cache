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

interface CacheInterface
{
    /**
     * @param PluginNameSpaceInterface $plugin
     */
    function __construct(PluginNameSpaceInterface $plugin);
    
    function __destruct();

    /**
     * @param string $key
     * @return mixed
     */
    function get($key);

    /**
     * @param mixed $data
     * @return void
     */
    function set($data);

    /**
     * @param string $key
     * @param mixed $data
     * @return void
     */
    function update($key, $data);

    /**
     * @param string $key
     * @return void
     */
    function delete($key);

    /**
     * @param string $tag
     * @return void
     */
    function deleteByTag($tag);

    /**
     * @param void
     * @return bool
     */
    function isCached();

    /**
     * @param void
     * @return void
     */
    public function flush();

    /**
     * @param int $ttl 30
     * @return void
     */
    static function expireIn($ttl = 30);
}
