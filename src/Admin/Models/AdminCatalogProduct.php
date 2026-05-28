<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDetailProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductUpdateProcessor;

/**
 * Admin Catalog → Products datagrid listing.
 *
 * REST   : GET /api/admin/catalog/products
 * GraphQL: adminCatalogProducts (added in a later task)
 *
 * 1:1 parity with Webkul\Admin\DataGrids\Catalog\ProductDataGrid — same
 * columns, same filters, same sort columns, same dual DB / Elasticsearch
 * branch (gated by the same two core config flags the datagrid uses).
 *
 * This is distinct from src/Admin/Models/AdminProduct.php (the slim picker
 * used by the Create-Order modal). The two endpoints coexist and serve
 * different surfaces.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCatalogProduct',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/catalog/products/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminCatalogProductDetailProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Catalog product detail (type-aware)',
                description: 'Returns a single catalog product with all detail-level fields populated. Type-specific blocks (superAttributes/variants, bundleOptions, linkedProducts, downloadableLinks/downloadableSamples) are populated only for the matching product type; all others are null.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 42]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single catalog product with all detail fields inlined.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'                   => 42,
                                    'sku'                  => 'SP-001',
                                    'name'                 => 'Classic Watch',
                                    'type'                 => 'simple',
                                    'status'               => 1,
                                    'price'                => '99.9900',
                                    'formattedPrice'       => '$99.99',
                                    'quantity'             => 42,
                                    'baseImageUrl'         => 'http://localhost:8000/storage/product/42/image.webp',
                                    'imagesCount'          => 3,
                                    'categoryId'           => 5,
                                    'categoryName'         => 'Accessories',
                                    'channel'              => 'default',
                                    'locale'               => 'en',
                                    'attributeFamilyId'    => 1,
                                    'attributeFamilyName'  => 'Default',
                                    'urlKey'               => 'classic-watch',
                                    'visibleIndividually'  => true,
                                    'shortDescription'     => 'A premium timepiece.',
                                    'description'          => 'Full HTML description.',
                                    'metaTitle'            => null,
                                    'metaDescription'      => null,
                                    'metaKeywords'         => null,
                                    'weight'               => 0.5,
                                    'taxCategoryId'        => null,
                                    'manageStock'          => true,
                                    'inStock'              => true,
                                    'featured'             => false,
                                    'new'                  => true,
                                    'createdAt'            => '2026-01-12T08:15:00+00:00',
                                    'updatedAt'            => '2026-04-30T14:20:09+00:00',
                                    'translations'         => [
                                        ['locale' => 'en', 'name' => 'Classic Watch', 'description' => 'Full HTML description.', 'shortDescription' => 'A premium timepiece.', 'urlKey' => 'classic-watch', 'metaTitle' => null, 'metaDescription' => null, 'metaKeywords' => null],
                                    ],
                                    'images'               => [
                                        ['id' => 1, 'path' => 'product/42/img1.webp', 'url' => 'http://localhost/storage/product/42/img1.webp', 'sortOrder' => 0],
                                    ],
                                    'categories'           => [
                                        ['id' => 5, 'name' => 'Accessories', 'slug' => 'accessories'],
                                    ],
                                    'inventories'          => [
                                        ['sourceId' => 1, 'sourceCode' => 'default', 'qty' => 42],
                                    ],
                                    'customerGroupPrices'  => [],
                                    'superAttributes'      => null,
                                    'variants'             => null,
                                    'bundleOptions'        => null,
                                    'linkedProducts'       => null,
                                    'downloadableLinks'    => null,
                                    'downloadableSamples'  => null,
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Product not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type'   => '/errors/404',
                                    'title'  => 'An error occurred',
                                    'status' => 404,
                                    'detail' => 'Product not found',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Put(
            uriTemplate: '/catalog/products/{id}',
            requirements: ['id' => '\d+'],
            input: AdminCatalogProductUpdateInput::class,
            provider: AdminCatalogProductDetailProvider::class,
            processor: AdminCatalogProductUpdateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Update a catalog product (any type)',
                description: 'Updates a catalog product. Free-shape pass-through payload that the core ProductRepository::update understands. Locale-keyed translations may be supplied either at the top level (`{ "en": { "name": "..." } }`) or under a `translations` key. Sub-resource fields (images, inventories, customer_group_prices, videos) are silently stripped — those have dedicated endpoints (Phases 5.11/5.12/5.13/videos) — and the response carries a `_warnings` array noting which fields were dropped. Returns the full AdminCatalogProduct detail payload (same shape as GET /catalog/products/{id}). Fires catalog.product.update.before / catalog.product.update.after.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 42]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'sku'                  => ['type' => 'string', 'example' => 'sp-001'],
                                    'url_key'              => ['type' => 'string', 'example' => 'classic-watch'],
                                    'status'               => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'visible_individually' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'guest_checkout'       => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'new'                  => ['type' => 'integer', 'enum' => [0, 1], 'example' => 0],
                                    'featured'             => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'price'                => ['type' => 'string', 'example' => '99.99'],
                                    'special_price'        => ['type' => 'string', 'example' => '79.99'],
                                    'special_price_from'   => ['type' => 'string', 'format' => 'date', 'example' => '2026-06-01'],
                                    'special_price_to'     => ['type' => 'string', 'format' => 'date', 'example' => '2026-06-30'],
                                    'weight'               => ['type' => 'string', 'example' => '0.5'],
                                    'tax_category_id'      => ['type' => 'integer', 'example' => 1],
                                    'categories'           => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [3, 5]],
                                    'channels'             => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'translations'         => ['type' => 'object', 'example' => ['en' => ['name' => 'Classic Watch', 'description' => 'Full HTML.', 'short_description' => 'A timepiece.']]],
                                ],
                            ],
                            'example' => [
                                'sku'          => 'sp-001',
                                'status'       => 1,
                                'price'        => '99.99',
                                'categories'   => [3, 5],
                                'channels'     => [1],
                                'translations' => [
                                    'en' => [
                                        'name'              => 'Classic Watch',
                                        'description'       => 'A premium timepiece.',
                                        'short_description' => 'Timeless style.',
                                        'url_key'           => 'classic-watch',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Product updated. Returns the full AdminCatalogProduct payload, plus _warnings (array of strings) if any sub-resource fields were stripped from the payload.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'             => 42,
                                    'sku'            => 'sp-001',
                                    'name'           => 'Classic Watch',
                                    'type'           => 'simple',
                                    'status'         => 1,
                                    'price'          => '99.9900',
                                    'formattedPrice' => '$99.99',
                                    '_warnings'      => ['Images must be managed via POST /api/admin/catalog/products/{id}/images.'],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.edit.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                    '422' => new Model\Response(description: 'Validation failure (sku duplicate, url_key duplicate, invalid boolean field, special_price ≥ price, invalid date range).'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/catalog/products/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminCatalogProductDetailProvider::class,
            processor: AdminCatalogProductDeleteProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Delete a catalog product',
                description: 'Deletes a catalog product. For configurable products, all variants cascade. No "refuse if in non-completed order" guard (mirrors Bagisto admin behaviour). Fires catalog.product.delete.before / catalog.product.delete.after. Returns 204 on success.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 42]),
                ],
                responses: [
                    '204' => new Model\Response(description: 'Product deleted.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.delete.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/catalog/products',
            input: AdminCatalogProductCreateInput::class,
            processor: AdminCatalogProductCreateProcessor::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'Create a catalog product (step 1 — all 7 types)',
                description: 'Mirrors the Bagisto admin Create-Product wizard step 1: only sku + attribute_family_id + type are submitted (plus super_attributes when type=configurable). Name, description, price, inventories, etc. are added in the step-2 update endpoint (Phase 5.9). Accepts type ∈ {simple, virtual, downloadable, grouped, bundle, configurable, booking}. For type=configurable, super_attributes is required and must be a non-empty map of attribute code (or id) → option_ids — the core repository generates the full Cartesian-product of variants. For booking, the 5 sub-types (default/appointment/event/rental/table) are configured during step-2 update. Returns the full AdminCatalogProduct detail payload — most fields will be null because only sku/type/family are populated yet. Fires catalog.product.create.before and catalog.product.create.after.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['sku', 'attribute_family_id'],
                                'properties' => [
                                    'sku'                 => ['type' => 'string', 'example' => 'sp-001'],
                                    'attribute_family_id' => ['type' => 'integer', 'example' => 1],
                                    'type'                => ['type' => 'string', 'enum' => ['simple', 'virtual', 'downloadable', 'grouped', 'bundle', 'configurable', 'booking'], 'example' => 'simple'],
                                    'super_attributes'    => [
                                        'type'        => 'object',
                                        'description' => 'Required when type=configurable. Map of attribute code (or id) to non-empty list of option_ids.',
                                        'example'     => ['color' => [1, 2], 'size' => [4, 5]],
                                    ],
                                ],
                            ],
                            'examples' => [
                                'simple' => [
                                    'summary' => 'Simple product',
                                    'value'   => ['sku' => 'sp-001', 'attribute_family_id' => 1, 'type' => 'simple'],
                                ],
                                'configurable' => [
                                    'summary' => 'Configurable product (with super_attributes)',
                                    'value'   => [
                                        'sku'                 => 'cf-001',
                                        'attribute_family_id' => 1,
                                        'type'                => 'configurable',
                                        'super_attributes'    => ['color' => [1, 2], 'size' => [4, 5]],
                                    ],
                                ],
                                'bundle'       => ['summary' => 'Bundle product', 'value' => ['sku' => 'bn-001', 'attribute_family_id' => 1, 'type' => 'bundle']],
                                'grouped'      => ['summary' => 'Grouped product', 'value' => ['sku' => 'gp-001', 'attribute_family_id' => 1, 'type' => 'grouped']],
                                'virtual'      => ['summary' => 'Virtual product', 'value' => ['sku' => 'vr-001', 'attribute_family_id' => 1, 'type' => 'virtual']],
                                'downloadable' => ['summary' => 'Downloadable product', 'value' => ['sku' => 'dl-001', 'attribute_family_id' => 1, 'type' => 'downloadable']],
                                'booking'      => ['summary' => 'Booking product (sub-type set in step 2)', 'value' => ['sku' => 'bk-001', 'attribute_family_id' => 1, 'type' => 'booking']],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Product created. Returns the full AdminCatalogProduct payload (most fields null at this point — step 2 update populates them).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'                  => 43,
                                    'sku'                 => 'sp-001',
                                    'type'                => 'simple',
                                    'attributeFamilyId'   => 1,
                                    'attributeFamilyName' => 'Default',
                                    'name'                => null,
                                    'status'              => null,
                                    'price'               => null,
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks catalog.products.create.'),
                    '422' => new Model\Response(description: 'Validation failed (missing sku / family / unsupported type / duplicate sku / invalid slug / unknown family / missing or invalid super_attributes for configurable).'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/catalog/products',
            provider: AdminCatalogProductCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog'],
                summary: 'List catalog products (datagrid parity)',
                description: 'Paginated, filterable, sortable product list mirroring the admin Catalog → Products datagrid. Routes via Elasticsearch when the admin panel is configured to. Returns a `{ data, meta }` envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('product_id', 'query', 'Filter by product ID — single integer or comma-separated list (e.g. "1,2,3").', false, schema: ['type' => 'string', 'example' => '142']),
                    new Model\Parameter('sku', 'query', 'Partial SKU match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'SP-001']),
                    new Model\Parameter('name', 'query', 'Partial product name match (SQL LIKE).', false, schema: ['type' => 'string', 'example' => 'Classic Watch']),
                    new Model\Parameter('type', 'query', 'Filter by product type.', false, schema: ['type' => 'string', 'enum' => ['simple', 'configurable', 'bundle', 'grouped', 'downloadable', 'virtual', 'booking'], 'example' => 'simple']),
                    new Model\Parameter('status', 'query', 'Filter by status (0 = disabled, 1 = enabled).', false, schema: ['type' => 'integer', 'enum' => [0, 1], 'example' => 1]),
                    new Model\Parameter('attribute_family', 'query', 'Filter by attribute family ID.', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('channel', 'query', 'Channel code for value resolution (e.g. "default").', false, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('locale', 'query', 'Locale code for value resolution (e.g. "en").', false, schema: ['type' => 'string', 'example' => 'en']),
                    new Model\Parameter('price_from', 'query', 'Minimum price filter (inclusive).', false, schema: ['type' => 'number', 'format' => 'float', 'example' => 10.00]),
                    new Model\Parameter('price_to', 'query', 'Maximum price filter (inclusive).', false, schema: ['type' => 'number', 'format' => 'float', 'example' => 500.00]),
                    new Model\Parameter('price', 'query', 'Price range shorthand — "min,max" (e.g. "10,500"). Overridden by price_from / price_to when both are present.', false, schema: ['type' => 'string', 'example' => '10,500']),
                    new Model\Parameter('sort', 'query', 'Column to sort by.', false, schema: ['type' => 'string', 'enum' => ['name', 'sku', 'attribute_family', 'price', 'quantity', 'product_id', 'status', 'type', 'channel'], 'example' => 'product_id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of catalog product rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'                  => 142,
                                            'sku'                 => 'SP-001',
                                            'name'                => 'Classic Watch',
                                            'type'                => 'simple',
                                            'status'              => 1,
                                            'price'               => '99.9900',
                                            'formattedPrice'      => '$99.99',
                                            'quantity'            => 42,
                                            'baseImageUrl'        => 'http://localhost:8000/cache/medium/product/142/image.webp',
                                            'imagesCount'         => 3,
                                            'categoryId'          => 5,
                                            'categoryName'        => 'Accessories',
                                            'channel'             => 'Default',
                                            'locale'              => 'en',
                                            'attributeFamilyId'   => 1,
                                            'attributeFamilyName' => 'Default',
                                            'urlKey'              => 'classic-watch',
                                            'visibleIndividually' => true,
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 62,
                                        'total'       => 616,
                                        'from'        => 1,
                                        'to'          => 10,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminCatalogProductDetailProvider::class,
            description: 'Admin catalog product detail by id (type-aware payload).',
        ),
        new Mutation(
            name: 'create',
            input: AdminCatalogProductCreateInput::class,
            processor: AdminCatalogProductCreateProcessor::class,
            description: 'Admin catalog product step-1 create (all 7 types). For configurable, pass superAttributes as a map of attribute code (or id) to non-empty list of option_ids. Becomes createAdminCatalogProduct in GraphQL.',
        ),
        new Mutation(
            name: 'update',
            input: AdminCatalogProductUpdateInput::class,
            processor: AdminCatalogProductUpdateProcessor::class,
            description: 'Admin catalog product update. Pass the resource IRI as id. Free-shape payload: send only the fields you want to change. Sub-resource fields (images / inventories / customerGroupPrices / videos) are stripped — use the dedicated endpoints. Becomes updateAdminCatalogProduct in GraphQL.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminCatalogProductUpdateInput::class,
            processor: AdminCatalogProductDeleteProcessor::class,
            description: 'Admin catalog product delete. Pass the resource IRI as id. Configurable variants cascade. Becomes deleteAdminCatalogProduct in GraphQL.',
        ),
        new QueryCollection(
            provider: AdminCatalogProductCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin catalog products datagrid listing (cursor pagination). Args: first, after, product_id, sku, name, type, status, attribute_family, channel, locale, price_from, price_to, sort, order.',
            extraArgs: [
                'product_id'       => ['type' => 'String'],
                'sku'              => ['type' => 'String'],
                'name'             => ['type' => 'String'],
                'type'             => ['type' => 'String'],
                'status'           => ['type' => 'Int'],
                'attribute_family' => ['type' => 'String'],
                'channel'          => ['type' => 'String'],
                'locale'           => ['type' => 'String'],
                'price_from'       => ['type' => 'Float'],
                'price_to'         => ['type' => 'Float'],
                'sort'             => ['type' => 'String'],
                'order'            => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminCatalogProduct
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?string $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedPrice = null;

    #[ApiProperty(writable: false)]
    public ?int $quantity = null;

    #[ApiProperty(writable: false)]
    public ?string $baseImageUrl = null;

    #[ApiProperty(writable: false)]
    public ?int $imagesCount = null;

    #[ApiProperty(writable: false)]
    public ?int $categoryId = null;

    #[ApiProperty(writable: false)]
    public ?string $categoryName = null;

    #[ApiProperty(writable: false)]
    public ?string $channel = null;

    #[ApiProperty(writable: false)]
    public ?string $locale = null;

    #[ApiProperty(writable: false)]
    public ?int $attributeFamilyId = null;

    #[ApiProperty(writable: false)]
    public ?string $attributeFamilyName = null;

    #[ApiProperty(writable: false)]
    public ?string $urlKey = null;

    #[ApiProperty(writable: false)]
    public ?bool $visibleIndividually = null;

    // ---- Detail-only fields (null on listing rows) ----

    #[ApiProperty(writable: false)]
    public ?string $shortDescription = null;

    #[ApiProperty(writable: false)]
    public ?string $description = null;

    #[ApiProperty(writable: false)]
    public ?string $metaTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $metaDescription = null;

    #[ApiProperty(writable: false)]
    public ?string $metaKeywords = null;

    #[ApiProperty(writable: false)]
    public ?float $weight = null;

    #[ApiProperty(writable: false)]
    public ?int $taxCategoryId = null;

    #[ApiProperty(writable: false)]
    public ?bool $manageStock = null;

    #[ApiProperty(writable: false)]
    public ?bool $inStock = null;

    #[ApiProperty(writable: false)]
    public ?bool $featured = null;

    #[ApiProperty(writable: false)]
    public ?bool $new = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;

    /** @var array<int, mixed>|null  Per-locale translation rows */
    #[ApiProperty(writable: false)]
    public ?array $translations = null;

    /** @var array<int, mixed>|null  Product image rows */
    #[ApiProperty(writable: false)]
    public ?array $images = null;

    /** @var array<int, mixed>|null  Category references */
    #[ApiProperty(writable: false)]
    public ?array $categories = null;

    /** @var array<int, mixed>|null  Per-source inventory rows */
    #[ApiProperty(writable: false)]
    public ?array $inventories = null;

    /** @var array<int, mixed>|null  Customer-group price rows */
    #[ApiProperty(writable: false)]
    public ?array $customerGroupPrices = null;

    // ---- Type-specific blocks (null unless applicable) ----

    /** @var array<int, mixed>|null  configurable only */
    #[ApiProperty(writable: false)]
    public ?array $superAttributes = null;

    /** @var array<int, mixed>|null  configurable only */
    #[ApiProperty(writable: false)]
    public ?array $variants = null;

    /** @var array<int, mixed>|null  bundle only */
    #[ApiProperty(writable: false)]
    public ?array $bundleOptions = null;

    /** @var array<int, mixed>|null  grouped only */
    #[ApiProperty(writable: false)]
    public ?array $linkedProducts = null;

    /** @var array<int, mixed>|null  downloadable only */
    #[ApiProperty(writable: false)]
    public ?array $downloadableLinks = null;

    /** @var array<int, mixed>|null  downloadable only */
    #[ApiProperty(writable: false)]
    public ?array $downloadableSamples = null;

    // ---- Phase 1.2 extension blocks (null on listing rows) ----

    /** @var array<string, mixed>|null  booking only — all sub-type fields + slots/tickets */
    #[ApiProperty(writable: false)]
    public ?array $bookingProduct = null;

    /** @var array<int, mixed>|null  customizable options (any type) */
    #[ApiProperty(writable: false)]
    public ?array $customizableOptions = null;

    /** @var array<int, mixed>|null  product video rows */
    #[ApiProperty(writable: false)]
    public ?array $videos = null;

    /** @var array<int, mixed>|null  channels the product is assigned to */
    #[ApiProperty(writable: false)]
    public ?array $channels = null;

    /** @var array<int, mixed>|null  related products (slim refs) */
    #[ApiProperty(writable: false)]
    public ?array $relatedProducts = null;

    /** @var array<int, mixed>|null  up-sell products (slim refs) */
    #[ApiProperty(writable: false)]
    public ?array $upSells = null;

    /** @var array<int, mixed>|null  cross-sell products (slim refs) */
    #[ApiProperty(writable: false)]
    public ?array $crossSells = null;

    /**
     * Phase 5.9 — Update warnings. Populated by AdminCatalogProductUpdateProcessor
     * when sub-resource fields (images / inventories / customer_group_prices /
     * videos) are stripped from the update payload. Null on all other operations.
     *
     * @var string[]|null
     */
    #[ApiProperty(writable: false)]
    public ?array $_warnings = null;
}
