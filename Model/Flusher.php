<?php

namespace Elgentos\ApiCacheIndexManagement\Model;

use Elgentos\ApiCacheIndexManagement\Api\CacheInterface;
use Exception;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Model\Context;

/**
 * Class Flusher
 * @package Elgentos\ApiCacheIndexManagement\Model
 */
class Flusher implements CacheInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    public $appCache;
    /**
     * @var CollectionFactory
     */
    public $productCollectionFactory;
    /**
     * @var Http
     */
    public $request;
    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @param Context $context
     * @param TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\CacheInterface $appCache
     * @param CollectionFactory $productCollectionFactory
     * @param Http $request
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        \Magento\Framework\App\CacheInterface $appCache,
        CollectionFactory $productCollectionFactory,
        Http $request
    ) {
        $this->_cacheTypeList = $cacheTypeList;
        $this->appCache = $appCache;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->request = $request;
    }

    /**
     * @param string $cacheType
     * @return string[]
     * @api
     */
    public function flush(string $cacheType)
    {
        foreach ($this->_cacheTypeList->getTypes() as $key => $value) {
            $this->_cacheTypeList->cleanType($key);
        }
        return [
            'code' => '200',
            'message' => 'Cache clean successfully',
        ];
    }

    /**
     * @param string $categoryIds
     * @return string[]
     * @api
     */
    public function flushCategories()
    {
        return $this->flushById('category', $this->request->getPostValue('ids') ?: []);
    }

    /**
     * @return string[]
     * @api
     */
    public function flushProducts()
    {
        $productIds = $this->request->getPostValue('ids') ?: [];
        if ($this->request->getPostValue('skus')) {
            $productIds = $this->getProductIdsBySku($this->request->getPostValue('skus'));
        }
        return $this->flushById('product', $productIds);
    }

    /**
     * @return string[]
     * @api
     */
    public function flushAll()
    {
        foreach ($this->_cacheTypeList->getTypes() as $key => $value) {
            $this->_cacheTypeList->cleanType($key);
        }
        return [
            'code' => '200',
            'message' => 'Cache clean successfully',
        ];
    }

    /**
     * @return string[]
     */
    public function flushAllInvalidated()
    {
        $invalidatedCaches = $this->_cacheTypeList->getInvalidated();
        if ($invalidatedCaches) {
            foreach ($invalidatedCaches as $key => $value) {
                $this->_cacheTypeList->cleanType($key);
            }
            return [
                'code' => '200',
                'message' => 'Cache clean successfully',
            ];
        } else {
            return [
                'code' => '304',
                'message' => 'Already cleaned cache',
            ];
        }
    }

    /**
     * @param mixed $skus
     * @return string[]
     * @api
     */
    private function getProductIdsBySku($skus)
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

        return $productIds;
    }

    /**
     * @param string $type
     * @param mixed $ids
     * @return string[]
     */
    private function flushById(string $type, $ids)
    {
        if (is_string($ids)) {
            $ids = array_map(function ($id) {
                return intval(trim($id));
            }, explode(',', $ids));
        }

        $tags = array_map(function ($productId) use ($type) {
            return 'catalog_' . $type . '_' . $productId;
        }, $ids);

        if (count($tags)) {
            try {
                $this->appCache->clean($tags);
                return [
                    'code' => '200',
                    'message' => 'Cache clean successfully',
                ];
            } catch (Exception $e) {
                return [
                    'code' => '500',
                    'message' => 'Could not clean cache; ' . $e->getMessage(),
                ];
            }
        } else {
            return [
                'code' => '304',
                'message' => 'No tags to clean',
            ];
        }
    }
}
