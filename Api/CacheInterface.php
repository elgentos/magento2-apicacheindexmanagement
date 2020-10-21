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
     * @param string $categoryIds
     * @return string[]
     * @api
     */
    public function flushCategoriesById(string $categoryIds);

    /**
     * @param string $productIds
     * @return string[]
     * @api
     */
    public function flushProductsById(string $productIds = '');

    /**
     * @param string $skus
     * @return string[]
     * @api
     */
    public function flushProductsBySku(string $skus = '');
}
