<?php

namespace Elgentos\ApiCacheIndexManagement\Model;

use Elgentos\ApiCacheIndexManagement\Api\CacheInterface;
use Exception;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Cache\TypeListInterface;
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
     * @var TypeListInterface
     */
    protected $cacheTypeList;
    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\CacheInterface $appCache
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        \Magento\Framework\App\CacheInterface $appCache,
        CollectionFactory $productCollectionFactory
    ) {
        $this->_cacheTypeList = $cacheTypeList;
        $this->appCache = $appCache;
        $this->productCollectionFactory = $productCollectionFactory;
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
     * @param string $type
     * @param string $ids
     * @return string[]
     */
    private function flushById(string $type, string $ids = '')
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

    /**
     * @param string $categoryIds
     * @return string[]
     * @api
     */
    public function flushCategoriesById(string $categoryIds = '')
    {
        return $this->flushById('category', $categoryIds);
    }

    /**
     * @param string $productIds
     * @return string[]
     * @api
     */
    public function flushProductsById(string $productIds = '')
    {
        return $this->flushById('product', $productIds);
    }

    /**
     * @param string $skus
     * @return string[]
     * @api
     */
    public function flushProductsBySku(string $skus = '')
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

        return $this->flushProductsById(implode(',', $productIds));
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
}
