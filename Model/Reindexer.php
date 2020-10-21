<?php

namespace Elgentos\ApiCacheIndexManagement\Model;
use Elgentos\ApiCacheIndexManagement\Api\ReindexInterface;
use Exception;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Processor;
use Throwable;

/**
 * Class Reindexer
 * @package Elgentos\ApiCacheIndexManagement\Model
 */
class Reindexer implements ReindexInterface
{
    /**
     * @var ConfigInterface
     */
    public $indexerConfig;
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
     * @param Processor $processor
     * @param ConfigInterface $indexerConfig
     * @param CollectionFactory $productCollectionFactory
     * @param Http $request
     */
    public function __construct(
        Processor $processor,
        ConfigInterface $indexerConfig,
        CollectionFactory $productCollectionFactory,
        Http $request
    ) {
        $this->processor = $processor;
        $this->indexerConfig = $indexerConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
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
    private function reindexById(string $indexName, $ids)
    {
        try {
            if (is_string($ids)) {
                $ids = array_map(function ($id) {
                    return intval(trim($id));
                }, explode(',', $ids));
            }

            /** @var Indexer $indexer */
            $indexer = $this->indexerConfig->getIndexer($indexName);
            if (!is_array($ids)) {
                $indexer->reindexAll();
            } else {
                $indexer->reindexList($ids);
            }

            $response = [
                'code' => '200',
                'message' => 'Indexers are reindexed successfully',
            ];
        } catch (Exception $e) {
            $response = [
                'code' => '500',
                'message' => 'Could not reindex ' . $indexName . '; ' . $e->getMessage(),
            ];
        } finally {
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
