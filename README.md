# Elgentos ApiCacheIndexManagement

Extension to invalidate caches and reindex indexers through the REST API. All routes are POST routes.

Both `{ids}` and `{skus}` can be passed as comma-separated strings or arrays.

## Cache routes

```
/V1/cache/flushAll
/V1/cache/flushAllInvalidated
/V1/cache/flush/products (parameters; ids / skus)
/V1/cache/flush/categories (parameters; ids / skus)
```

Possible `cacheTypes`;

```
config
layout
block_html
collections
reflection
db_ddl
compiled_config
eav
customer_notification
full_page
config_integration
config_integration_api
config_webservice
translate
```

There could be more or less cache types, depending on your installation. See `bin/magento cache:status` for your full list.

## Index routes

```
/V1/index/reindexAll
/V1/index/reindexAllInvalidated
/V1/index/reindex/{indexName} (optional parameters; ids / skus)
```

Possible `indexNames`:

```
design_config_grid                       Design Config Grid
customer_grid                            Customer Grid
catalog_category_product                 Category Products
catalog_product_category                 Product Categories
catalogrule_rule                         Catalog Rule Product
catalog_product_attribute                Product EAV
cataloginventory_stock                   Stock
catalog_product_price                    Product Price
catalogrule_product                      Catalog Product Rule
catalogsearch_fulltext                   Catalog Search
```

There could be more or less indexes, depending on your installation. See `bin/magento indexer:info` for your full list.

Note about re-indexing using the `{skus}` parameter: it is possible to use parameter this in combination with a non-product indexer, such as `customer_grid`. Doing this will fetch the product IDs belonging to the passed SKUs, and will then reindex the *customers* with that specific entity ID. This shouldn't introduce any problems, but it's fairly useless.

## Possible response codes

| Code | Message |
| ------ | ------ |
| 200 | Cache clean successfully / Indexers are reindexed successfully |
| 304 | No tags to clean / Already cleaned cache |
| 500 | Could not reindex / could not clear cache |
