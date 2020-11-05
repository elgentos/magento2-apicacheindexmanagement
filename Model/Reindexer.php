<?php

namespace Elgentos\ApiCacheIndexManagement\Model;
use Elgentos\ApiCacheIndexManagement\Api\ReindexInterface;
use Exception;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Processor;
use Throwable;
use \Magento\Framework\Indexer\IndexerInterfaceFactory;

/**
 * Class Reindexer
 * @package Elgentos\ApiCacheIndexManagement\Model
 */
class Reindexer implements ReindexInterface
{
    /**
     * @var CollectionFactory
     */
    public $productCollectionFactory;
    /**
     * @var Http
     */
    public $request;
    /**
     * @var Processor
     */
    protected $processor;
    /**
     * @var IndexerInterfaceFactory
     */
    protected $indexerFactory;

    /**
     * @param Processor $processor
     * @param CollectionFactory $productCollectionFactory
     * @param Http $request
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(
        Processor $processor,
        CollectionFactory $productCollectionFactory,
        Http $request,
        IndexerInterfaceFactory $indexerFactory
    ) {
        $this->processor = $processor;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @param string $indexName
     * @return string[]
     * @throws Throwable
     */
    public function reindex(string $indexName)
    {
        if ($this->request->getPostValue('ids')) {
            return $this->reindexById($indexName, $this->request->getPostValue('ids'));
        } elseif ($this->request->getPostValue('skus')) {
            return $this->reindexBySku($indexName, $this->request->getPostValue('skus'));
        } else {
            return $this->reindexById($indexName);
        }
    }

    /**
     * @param string $indexName
     * @param mixed $ids
     * @return string[]
     * @throws Throwable
     */
    private function reindexById(string $indexName, $ids = null)
    {
        try {
            if (is_string($ids)) {
                $ids = array_map(function ($id) {
                    return intval(trim($id));
                }, explode(',', $ids));
            }

            /** @var Indexer $indexer */
            $indexer = $this->indexerFactory->create()->load($indexName);
            if (!is_array($ids)) {
                $indexer->reindexAll();
            } else {
                $indexer->reindexList($ids);
            }

            $response = [
                'code' => '200',
                'message' => 'Indexers are reindexed successfully',
            ];
            return $response;
        } catch (Exception $e) {
            $response = [
                'code' => '500',
                'message' => 'Could not reindex ' . $indexName . '; ' . $e->getMessage(),
            ];
            return $response;
        }
    }

    /**
     * @param string $indexName
     * @param mixed $skus
     * @return string[]
     * @throws Throwable
     */
    private function reindexBySku(string $indexName, $skus)
    {
        if (is_string($skus)) {
            $skus = array_map(function ($id) {
                return trim($id);
            }, explode(',', $skus));
        }

        $productIds = [];
        if (count($skus)) {
            $productCollection = $this->productCollectionFactory->create();
            $productIds = $productCollection->addFieldToFilter('sku', ['in' => $skus])->getColumnValues('entity_id');
        }

        return $this->reindexById($indexName, implode(',', $productIds));
    }

    /**
     * @return string[]
     */
    public function reindexAll()
    {
        try {
            $this->processor->reindexAll();
            $response = [
                'code' => '200',
                'message' => 'Indexers are reindexed successfully',
            ];
        } catch (Exception $e) {
            $response = [
                'code' => '500',
                'message' => 'Could not reindex; ' . $e->getMessage(),
            ];
        } finally {
            return $response;
        }
    }

    /**
     * @return string[]
     */
    public function reindexAllInvalidated()
    {
        try {
            $this->processor->reindexAllInvalid();
            $response = [
                'code' => '200',
                'message' => 'Indexers are reindexed successfully',
            ];
        } catch (Exception $e) {
            $response = [
                'code' => '500',
                'message' => 'Could not reindex; ' . $e->getMessage(),
            ];
        } finally {
            return $response;
        }
    }
}
