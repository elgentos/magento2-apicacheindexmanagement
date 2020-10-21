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
}
