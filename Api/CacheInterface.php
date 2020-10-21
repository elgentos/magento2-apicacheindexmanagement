<?php

namespace Elgentos\ApiCacheIndexManagement\Api;

/**
 * Interface CacheInterface
 * @package Elgentos\ApiCacheIndexManagement\Api
 */
interface CacheInterface
{
    /**
     * @return string[]
     * @api
     */
    public function flushAll();
    /**
     * @return string[]
     * @api
     */
    public function flushAllInvalidated();

    /**
     * @param string $cacheType
     * @return string[]
     * @api
     */
    public function flush(string $cacheType);

    /**
     * @return string[]
     * @api
     */
    public function flushCategories();

    /**
     * @return string[]
     * @api
     */
    public function flushProducts();
}
