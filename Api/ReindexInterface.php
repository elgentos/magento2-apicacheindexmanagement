<?php

namespace Elgentos\ApiCacheIndexManagement\Api;

/**
 * Interface ReindexInterface
 * @package Elgentos\ApiCacheIndexManagement\Api
 */
interface ReindexInterface
{
    /**
     * @return string[]
     * @api
     */
    public function reindexAll();

    /**
     * @return string[]
     * @api
     */
    public function reindexAllInvalidated();

    /**
     * @param string $indexName
     * @return string[]
     */
    public function reindex(string $indexName);

    /**
     * @param string $indexName
     * @param string $ids
     * @return string[]
     * @api
     */
    public function reindexById(string $indexName, string $ids = '');

    /**
     * @param string $indexName
     * @param string $skus
     * @return string[]
     */
    public function reindexBySku(string $indexName, string $skus = '');
}
