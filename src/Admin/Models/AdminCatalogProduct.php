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
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDeleteProcessor;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductDetailProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductExportProvider;
use Webkul\BagistoApi\Admin\State\AdminCatalogProductUpdateProcessor;
use Webkul\Product\Models\Product;

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
            output: AdminCatalogProductRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Catalog product detail (type-aware)',
                description: 'Returns a single catalog product with all detail-level fields populated. `channels` lists every available channel, each flagged `assigned` for this product. `attributes` mirrors the admin edit screen field-for-field — every attribute the product\'s family defines (code, adminName, type, value, options, per-channel/locale flags), with empty fields present (value null). Type-specific blocks (superAttributes/variants, bundleOptions, linkedProducts, downloadableLinks/downloadableSamples) are populated only for the matching product type; all others are null.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 42]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Single catalog product with all detail fields inlined.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 42,
                                    'sku' => 'SP-001',
                                    'name' => 'Classic Watch',
                                    'type' => 'simple',
                                    'status' => 1,
                                    'price' => '99.9900',
                                    'formattedPrice' => '$99.99',
                                    'quantity' => 42,
                                    'baseImageUrl' => 'http://localhost:8000/storage/product/42/image.webp',
                                    'imagesCount' => 3,
                                    'categoryId' => 5,
                                    'categoryName' => 'Accessories',
                                    'channel' => 'default',
                                    'locale' => 'en',
                                    'attributeFamilyId' => 1,
                                    'attributeFamilyName' => 'Default',
                                    'urlKey' => 'classic-watch',
                                    'visibleIndividually' => true,
                                    'shortDescription' => 'A premium timepiece.',
                                    'description' => 'Full HTML description.',
                                    'metaTitle' => null,
                                    'metaDescription' => null,
                                    'metaKeywords' => null,
                                    'weight' => 0.5,
                                    'taxCategoryId' => null,
                                    'manageStock' => true,
                                    'inStock' => true,
                                    'featured' => false,
                                    'new' => true,
                                    'createdAt' => '2026-01-12T08:15:00+00:00',
                                    'updatedAt' => '2026-04-30T14:20:09+00:00',
                                    'translations' => [
                                        ['locale' => 'en', 'name' => 'Classic Watch', 'description' => 'Full HTML description.', 'shortDescription' => 'A premium timepiece.', 'urlKey' => 'classic-watch', 'metaTitle' => null, 'metaDescription' => null, 'metaKeywords' => null],
                                    ],
                                    'images' => [
                                        ['id' => 1, 'path' => 'product/42/img1.webp', 'url' => 'http://localhost/storage/product/42/img1.webp', 'sortOrder' => 0],
                                    ],
                                    'categories' => [
                                        ['id' => 5, 'name' => 'Accessories', 'slug' => 'accessories'],
                                    ],
                                    'inventories' => [
                                        ['sourceId' => 1, 'sourceCode' => 'default', 'qty' => 42],
                                    ],
                                    'customerGroupPrices' => [],
                                    'superAttributes' => null,
                                    'variants' => null,
                                    'bundleOptions' => null,
                                    'linkedProducts' => null,
                                    'downloadableLinks' => null,
                                    'downloadableSamples' => null,
                                    'channels' => [
                                        ['id' => 1, 'code' => 'default', 'name' => 'Default Channel', 'assigned' => true],
                                        ['id' => 2, 'code' => 'mobile', 'name' => 'Mobile Channel', 'assigned' => false],
                                    ],
                                    'attributes' => [
                                        ['id' => 1, 'code' => 'sku', 'adminName' => 'SKU', 'type' => 'text', 'isRequired' => true, 'valuePerChannel' => false, 'valuePerLocale' => false, 'groupCode' => 'general', 'groupName' => 'General', 'value' => 'SP-001', 'options' => null],
                                        ['id' => 23, 'code' => 'color', 'adminName' => 'Color', 'type' => 'select', 'isRequired' => false, 'valuePerChannel' => false, 'valuePerLocale' => false, 'groupCode' => 'general', 'groupName' => 'General', 'value' => null, 'options' => [['id' => 1, 'adminName' => 'Red', 'swatchValue' => '#ff0000', 'sortOrder' => 1]]],
                                        ['id' => 25, 'code' => 'meta_title', 'adminName' => 'Meta Title', 'type' => 'textarea', 'isRequired' => false, 'valuePerChannel' => true, 'valuePerLocale' => true, 'groupCode' => 'meta_description', 'groupName' => 'Meta Description', 'value' => null, 'options' => null],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Product not found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'type' => '/errors/404',
                                    'title' => 'An error occurred',
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
            output: AdminCatalogProductRestDto::class,
            deserialize: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Update a catalog product (any type)',
                description: 'Partial update (PATCH-style): send only the fields you want to change — every attribute on the product\'s family is editable by its code (sku, name, url_key, short_description, description, meta_title, color, size, brand, price, weight, status, …). Translatable fields are written to the requested locale; pass `?locale=fr&channel=default` to target a specific locale/channel (defaults to the store default). Omitted fields keep their current value. Type-structure keys are accepted for the matching type: `variants` (configurable), `bundle_options` (bundle), `links` (grouped), `downloadable_links`/`downloadable_samples` (downloadable), `booking` (booking). `categories`/`channels`/`related_products`/`up_sells`/`cross_sells` replace the current set when sent (omit to keep). Sub-resources `images`/`videos`/`inventories`/`customer_group_prices` have dedicated endpoints and are ignored here (noted in `_warnings`). Returns the full product detail (same shape as GET /catalog/products/{id}).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Product ID.', true, schema: ['type' => 'integer', 'example' => 42]),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'description' => 'Every field on the product edit form is editable by its attribute code. Send only what you change. Pick a product type from the Examples dropdown to see the full edit-form body for that type.',
                                'properties' => [
                                    'sku' => ['type' => 'string', 'example' => 'sp-001'],
                                    'name' => ['type' => 'string', 'example' => 'Arctic Beanie'],
                                    'product_number' => ['type' => 'string', 'example' => 'PN-1001'],
                                    'url_key' => ['type' => 'string', 'example' => 'arctic-beanie'],
                                    'status' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'visible_individually' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'guest_checkout' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'new' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'featured' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'manage_stock' => ['type' => 'integer', 'enum' => [0, 1], 'example' => 1],
                                    'price' => ['type' => 'string', 'example' => '99.99'],
                                    'cost' => ['type' => 'string', 'example' => '40.00'],
                                    'special_price' => ['type' => 'string', 'example' => '79.99'],
                                    'special_price_from' => ['type' => 'string', 'format' => 'date', 'example' => '2026-08-01'],
                                    'special_price_to' => ['type' => 'string', 'format' => 'date', 'example' => '2026-08-31'],
                                    'GST' => ['type' => 'string', 'example' => '5.00'],
                                    'weight' => ['type' => 'string', 'example' => '0.5'],
                                    'length' => ['type' => 'string', 'example' => '10'],
                                    'width' => ['type' => 'string', 'example' => '5'],
                                    'height' => ['type' => 'string', 'example' => '3'],
                                    'tax_category_id' => ['type' => 'integer', 'example' => 2],
                                    'color' => ['type' => 'integer', 'description' => 'Attribute option id.', 'example' => 1],
                                    'size' => ['type' => 'integer', 'description' => 'Attribute option id.', 'example' => 6],
                                    'brand' => ['type' => 'integer', 'description' => 'Attribute option id.', 'example' => 10],
                                    'short_description' => ['type' => 'string', 'example' => 'Warm knit beanie.'],
                                    'description' => ['type' => 'string', 'example' => 'Full HTML description.'],
                                    'meta_title' => ['type' => 'string', 'example' => 'Arctic Beanie'],
                                    'meta_keywords' => ['type' => 'string', 'example' => 'beanie, winter'],
                                    'meta_description' => ['type' => 'string', 'example' => 'Buy the Arctic Beanie.'],
                                    'categories' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1, 8]],
                                    'channels' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                    'up_sells' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [2]],
                                    'cross_sells' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [3]],
                                    'related_products' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [2]],
                                    'customizable_options' => ['type' => 'object', 'description' => 'Custom options (simple & virtual only). Keyed option_*; each option may carry a prices map keyed price_*.'],
                                    'super_attributes' => ['type' => 'object', 'description' => 'Configurable only. Map of attribute code to option-id list.'],
                                    'variants' => ['type' => 'object', 'description' => 'Configurable only. Per-variant fields keyed by variant product id.'],
                                    'bundle_options' => ['type' => 'object', 'description' => 'Bundle only. Option groups keyed option_*; products keyed product_*.'],
                                    'links' => ['type' => 'object', 'description' => 'Grouped only. Associated products keyed link_*.'],
                                    'downloadable_links' => ['type' => 'object', 'description' => 'Downloadable only. Links keyed link_*.'],
                                    'downloadable_samples' => ['type' => 'object', 'description' => 'Downloadable only. Samples keyed sample_*.'],
                                    'booking' => ['type' => 'object', 'description' => 'Booking only. type ∈ default/appointment/event/rental/table.'],
                                    'translations' => ['type' => 'object', 'description' => 'Optional locale-keyed override. Top-level codes already write to the request locale (?locale=).', 'example' => ['fr' => ['name' => 'Bonnet Arctique']]],
                                ],
                            ],
                            'examples' => [
                                'simple' => [
                                    'summary' => 'Simple — every edit-form field',
                                    'value' => [
                                        'sku' => 'sp-001', 'name' => 'Arctic Beanie', 'product_number' => 'PN-1001', 'url_key' => 'arctic-beanie',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1, 'new' => 1, 'featured' => 1, 'manage_stock' => 1,
                                        'tax_category_id' => 2, 'color' => 1, 'size' => 6, 'brand' => 10,
                                        'price' => '99.99', 'cost' => '40.00', 'special_price' => '79.99', 'special_price_from' => '2026-08-01', 'special_price_to' => '2026-08-31', 'GST' => '5.00',
                                        'short_description' => 'Warm knit beanie.', 'description' => 'Full HTML description.',
                                        'length' => '10', 'width' => '5', 'height' => '3', 'weight' => '0.5',
                                        'meta_title' => 'Arctic Beanie', 'meta_keywords' => 'beanie, winter', 'meta_description' => 'Buy the Arctic Beanie.',
                                        'categories' => [1, 8], 'channels' => [1], 'up_sells' => [2], 'cross_sells' => [3], 'related_products' => [2],
                                        'customizable_options' => [
                                            'option_1' => ['en' => ['label' => 'Engraving text'], 'type' => 'text', 'is_required' => '1', 'max_characters' => '30', 'sort_order' => '1', 'price' => '5.00'],
                                            'option_2' => ['en' => ['label' => 'Gift wrap'], 'type' => 'checkbox', 'is_required' => '0', 'sort_order' => '2', 'prices' => [
                                                'price_1' => ['en' => ['label' => 'Standard'], 'price' => '3.00', 'sort_order' => '1'],
                                                'price_2' => ['en' => ['label' => 'Premium'], 'price' => '6.00', 'sort_order' => '2'],
                                            ]],
                                        ],
                                    ],
                                ],
                                'virtual' => [
                                    'summary' => 'Virtual — same fields as simple (no shipping needed; dimensions optional)',
                                    'value' => [
                                        'sku' => 'vr-001', 'name' => 'Online Gift Wrap', 'url_key' => 'online-gift-wrap',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1, 'manage_stock' => 1,
                                        'tax_category_id' => 2, 'price' => '9.99', 'cost' => '3.00', 'GST' => '5.00',
                                        'short_description' => 'Virtual add-on.', 'description' => 'No shipping required.',
                                        'meta_title' => 'Gift Wrap', 'categories' => [1], 'channels' => [1],
                                        'customizable_options' => [
                                            'option_1' => ['en' => ['label' => 'Message'], 'type' => 'textarea', 'is_required' => '0', 'sort_order' => '1', 'price' => '0.00'],
                                        ],
                                    ],
                                ],
                                'downloadable' => [
                                    'summary' => 'Downloadable — common fields + links & samples',
                                    'value' => [
                                        'sku' => 'dl-001', 'name' => 'E-Book Bundle', 'url_key' => 'ebook-bundle',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1, 'manage_stock' => 1,
                                        'tax_category_id' => 2, 'price' => '15.00', 'cost' => '4.00', 'GST' => '5.00',
                                        'short_description' => 'Downloadable e-book.', 'description' => 'Instant download.',
                                        'meta_title' => 'E-Book', 'categories' => [1], 'channels' => [1], 'up_sells' => [2], 'cross_sells' => [3], 'related_products' => [2],
                                        'downloadable_links' => [
                                            'link_1' => ['en' => ['title' => 'Chapter 1 PDF'], 'price' => '5.00', 'downloads' => '3', 'sort_order' => '1', 'type' => 'url', 'url' => 'https://example.com/ch1.pdf', 'sample_type' => 'url', 'sample_url' => 'https://example.com/sample.pdf'],
                                        ],
                                        'downloadable_samples' => [
                                            'sample_1' => ['title' => 'Preview', 'sort_order' => '1', 'type' => 'url', 'url' => 'https://example.com/preview.pdf'],
                                        ],
                                    ],
                                ],
                                'grouped' => [
                                    'summary' => 'Grouped — common fields + linked products (no own price)',
                                    'value' => [
                                        'sku' => 'gr-001', 'name' => 'Starter Pack', 'url_key' => 'starter-pack',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1,
                                        'tax_category_id' => 2, 'GST' => '5.00', 'weight' => '0.5',
                                        'short_description' => 'A bundle of essentials.', 'description' => 'Buy the set.',
                                        'meta_title' => 'Starter Pack', 'categories' => [1, 8], 'channels' => [1], 'up_sells' => [2], 'cross_sells' => [3], 'related_products' => [2],
                                        'links' => [
                                            'link_1' => ['associated_product_id' => 1, 'qty' => '2', 'sort_order' => '1'],
                                            'link_2' => ['associated_product_id' => 2, 'qty' => '1', 'sort_order' => '2'],
                                        ],
                                    ],
                                ],
                                'bundle' => [
                                    'summary' => 'Bundle — common fields + option groups (dynamic price, no special_price)',
                                    'value' => [
                                        'sku' => 'bn-001', 'name' => 'Build Your Kit', 'url_key' => 'build-your-kit',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1, 'price' => '0',
                                        'tax_category_id' => 2, 'GST' => '5.00', 'weight' => '0.5',
                                        'short_description' => 'Pick your parts.', 'description' => 'Custom kit.',
                                        'meta_title' => 'Build Your Kit', 'categories' => [1, 8], 'channels' => [1],
                                        'bundle_options' => [
                                            'option_1' => ['en' => ['label' => 'Choose accessory'], 'type' => 'radio', 'is_required' => '1', 'sort_order' => '1', 'products' => [
                                                'product_1' => ['product_id' => 1, 'qty' => '1', 'is_default' => '1', 'sort_order' => '1'],
                                                'product_2' => ['product_id' => 2, 'qty' => '1', 'is_default' => '0', 'sort_order' => '2'],
                                            ]],
                                        ],
                                    ],
                                ],
                                'configurable' => [
                                    'summary' => 'Configurable — common fields + per-variant fields (color/size set per variant)',
                                    'value' => [
                                        'sku' => 'cf-001', 'name' => 'Wool Beanie', 'url_key' => 'wool-beanie',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1,
                                        'tax_category_id' => 2, 'GST' => '5.00',
                                        'short_description' => 'Choose colour & size.', 'description' => 'Configurable beanie.',
                                        'meta_title' => 'Wool Beanie', 'categories' => [1, 8], 'channels' => [1],
                                        'variants' => [
                                            '2872' => ['sku' => 'BEANIE-RED-S', 'name' => 'Red / Small', 'price' => '29.99', 'cost' => '8.00', 'weight' => '0.3', 'status' => '1', 'color' => 1, 'size' => 6],
                                            '2873' => ['sku' => 'BEANIE-BLUE-S', 'name' => 'Blue / Small', 'price' => '29.99', 'cost' => '8.00', 'weight' => '0.3', 'status' => '1', 'color' => 2, 'size' => 6],
                                        ],
                                    ],
                                ],
                                'booking' => [
                                    'summary' => 'Booking — common fields + booking block (type: default/appointment/event/rental/table)',
                                    'value' => [
                                        'sku' => 'bk-001', 'name' => 'Studio Session', 'url_key' => 'studio-session',
                                        'status' => 1, 'visible_individually' => 1, 'guest_checkout' => 1,
                                        'tax_category_id' => 2, 'GST' => '5.00', 'price' => '99.99', 'weight' => '0.5',
                                        'short_description' => 'Book a slot.', 'description' => 'Recurring weekly slots.',
                                        'meta_title' => 'Studio Session', 'categories' => [1], 'channels' => [1],
                                        'booking' => ['type' => 'default', 'qty' => '1', 'location' => 'Studio A', 'available_every_week' => '1', 'booking_type' => 'many', 'duration' => '60', 'break_time' => '10', 'slots' => [['from' => '09:00', 'to' => '17:00']]],
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
                                    'id' => 42,
                                    'sku' => 'sp-001',
                                    'name' => 'Classic Watch',
                                    'type' => 'simple',
                                    'status' => 1,
                                    'price' => '99.9900',
                                    'formattedPrice' => '$99.99',
                                    'warnings' => ['Images must be managed via POST /api/admin/catalog/products/{id}/images.'],
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
                tags: ['Admin Catalog: Products'],
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
            output: AdminCatalogProductRestDto::class,
            status: 201,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Create a catalog product (step 1 — all 7 types)',
                description: 'Mirrors the Bagisto admin Create-Product wizard step 1: only sku + attribute_family_id + type are submitted (plus super_attributes when type=configurable). Name, description, price, inventories, etc. are added in the step-2 update endpoint (Phase 5.9). Accepts type ∈ {simple, virtual, downloadable, grouped, bundle, configurable, booking}. For type=configurable, super_attributes is required and must be a non-empty map of attribute code (or id) → option_ids — the core repository generates the full Cartesian-product of variants. For booking, the 5 sub-types (default/appointment/event/rental/table) are configured during step-2 update. Returns the full AdminCatalogProduct detail payload — most fields will be null because only sku/type/family are populated yet. Fires catalog.product.create.before and catalog.product.create.after.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['sku', 'attribute_family_id'],
                                'properties' => [
                                    'sku' => ['type' => 'string', 'example' => 'sp-001'],
                                    'attribute_family_id' => ['type' => 'integer', 'example' => 1],
                                    'type' => ['type' => 'string', 'enum' => ['simple', 'virtual', 'downloadable', 'grouped', 'bundle', 'configurable', 'booking'], 'example' => 'simple'],
                                    'super_attributes' => [
                                        'type' => 'object',
                                        'description' => 'Required when type=configurable. Map of attribute code (or id) to non-empty list of option_ids.',
                                        'example' => ['color' => [1, 2], 'size' => [6, 7]],
                                    ],
                                ],
                            ],
                            'examples' => [
                                'simple' => [
                                    'summary' => 'Simple product',
                                    'value' => ['sku' => 'sp-001', 'attribute_family_id' => 1, 'type' => 'simple'],
                                ],
                                'configurable' => [
                                    'summary' => 'Configurable product (with super_attributes)',
                                    'value' => [
                                        'sku' => 'cf-001',
                                        'attribute_family_id' => 1,
                                        'type' => 'configurable',
                                        'super_attributes' => ['color' => [1, 2], 'size' => [6, 7]],
                                    ],
                                ],
                                'bundle' => ['summary' => 'Bundle product', 'value' => ['sku' => 'bn-001', 'attribute_family_id' => 1, 'type' => 'bundle']],
                                'grouped' => ['summary' => 'Grouped product', 'value' => ['sku' => 'gp-001', 'attribute_family_id' => 1, 'type' => 'grouped']],
                                'virtual' => ['summary' => 'Virtual product', 'value' => ['sku' => 'vr-001', 'attribute_family_id' => 1, 'type' => 'virtual']],
                                'downloadable' => ['summary' => 'Downloadable product', 'value' => ['sku' => 'dl-001', 'attribute_family_id' => 1, 'type' => 'downloadable']],
                                'booking' => ['summary' => 'Booking product (sub-type set in step 2)', 'value' => ['sku' => 'bk-001', 'attribute_family_id' => 1, 'type' => 'booking']],
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
                                    'id' => 43,
                                    'sku' => 'sp-001',
                                    'type' => 'simple',
                                    'attributeFamilyId' => 1,
                                    'attributeFamilyName' => 'Default',
                                    'name' => null,
                                    'status' => null,
                                    'price' => null,
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
            output: AdminCatalogProductRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
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
                                            'id' => 142,
                                            'sku' => 'SP-001',
                                            'name' => 'Classic Watch',
                                            'type' => 'simple',
                                            'status' => 1,
                                            'price' => '99.9900',
                                            'formattedPrice' => '$99.99',
                                            'quantity' => 42,
                                            'baseImageUrl' => 'http://localhost:8000/cache/medium/product/142/image.webp',
                                            'imagesCount' => 3,
                                            'categoryId' => 5,
                                            'categoryName' => 'Accessories',
                                            'channel' => 'Default',
                                            'locale' => 'en',
                                            'attributeFamilyId' => 1,
                                            'attributeFamilyName' => 'Default',
                                            'urlKey' => 'classic-watch',
                                            'visibleIndividually' => true,
                                            'shortDescription' => '<p>A timeless classic watch.</p>',
                                            'description' => '<p>Full HTML product description.</p>',
                                            'metaTitle' => 'Classic Watch',
                                            'metaDescription' => 'Buy the Classic Watch',
                                            'metaKeywords' => 'watch, classic',
                                            'weight' => 0.25,
                                            'featured' => false,
                                            'new' => true,
                                            'createdAt' => '2026-05-20 10:00:00',
                                            'updatedAt' => '2026-05-22 14:30:00',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 62,
                                        'total' => 616,
                                        'from' => 1,
                                        'to' => 10,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/catalog/products/export',
            provider: AdminCatalogProductExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Catalog: Products'],
                summary: 'Export products as CSV',
                description: 'Downloads the products datagrid as a CSV file (text/csv attachment) — the same data the admin Catalog → Products Export button produces. Honours the same filters as the listing and exports every matching row, not just the current page. Binary download, not JSON. Only ?format=csv is supported.',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format. Currently only csv.', false, schema: ['type' => 'string', 'enum' => ['csv'], 'default' => 'csv']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'CSV file downloaded (text/csv attachment).', content: new \ArrayObject(['text/csv' => ['schema' => ['type' => 'string', 'format' => 'binary']]])),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks the view permission.'),
                    '422' => new Model\Response(description: 'Unsupported format (only csv).'),
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
                'product_id' => ['type' => 'String'],
                'sku' => ['type' => 'String'],
                'name' => ['type' => 'String'],
                'type' => ['type' => 'String'],
                'status' => ['type' => 'Int'],
                'attribute_family' => ['type' => 'String'],
                'channel' => ['type' => 'String'],
                'locale' => ['type' => 'String'],
                'price_from' => ['type' => 'Float'],
                'price_to' => ['type' => 'Float'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminCatalogProduct extends EloquentModel
{
    protected $table = 'products';

    protected $appends = [
        'name', 'status', 'price', 'formatted_price', 'special_price', 'formatted_special_price',
        'special_price_from', 'special_price_to', 'quantity', 'base_image_url', 'images_count',
        'category_id', 'category_name', 'channel', 'locale', 'attribute_family_name', 'url_key',
        'visible_individually', 'short_description', 'description', 'meta_title', 'meta_description',
        'meta_keywords', 'weight', 'tax_category_id', 'manage_stock', 'in_stock', 'featured', 'new',
    ];

    protected $casts = [
        'id' => 'int',
        'attribute_family_id' => 'int',
    ];

    private ?object $flatRow = null;

    private bool $flatLoaded = false;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function images(): HasMany
    {
        return $this->hasMany(AdminProductDetailImage::class, 'product_id')->orderBy('position');
    }

    #[ApiProperty(writable: false)]
    public function videos(): HasMany
    {
        return $this->hasMany(AdminProductDetailVideo::class, 'product_id')->orderBy('position');
    }

    #[ApiProperty(writable: false)]
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(AdminProductDetailCategory::class, 'product_categories', 'product_id', 'category_id');
    }

    #[ApiProperty(writable: false)]
    public function inventories(): HasMany
    {
        return $this->hasMany(AdminProductDetailInventory::class, 'product_id');
    }

    #[ApiProperty(writable: false)]
    public function customer_group_prices(): HasMany
    {
        return $this->hasMany(AdminProductDetailCgp::class, 'product_id')->orderBy('qty');
    }

    #[ApiProperty(writable: false)]
    public function translations(): HasMany
    {
        return $this->hasMany(AdminProductDetailTranslation::class, 'product_id');
    }

    #[ApiProperty(writable: false)]
    public function super_attributes(): BelongsToMany
    {
        return $this->belongsToMany(AdminProductDetailSuperAttribute::class, 'product_super_attributes', 'product_id', 'attribute_id');
    }

    #[ApiProperty(writable: false)]
    public function variants(): HasMany
    {
        return $this->hasMany(AdminProductDetailVariant::class, 'parent_id');
    }

    #[ApiProperty(writable: false)]
    public function bundle_options(): HasMany
    {
        return $this->hasMany(AdminProductDetailBundleOption::class, 'product_id')->orderBy('sort_order');
    }

    #[ApiProperty(writable: false)]
    public function linked_products(): HasMany
    {
        return $this->hasMany(AdminProductDetailGroupedProduct::class, 'product_id')->orderBy('sort_order');
    }

    #[ApiProperty(writable: false)]
    public function downloadable_links(): HasMany
    {
        return $this->hasMany(AdminProductDetailDownloadableLink::class, 'product_id')->orderBy('sort_order');
    }

    #[ApiProperty(writable: false)]
    public function downloadable_samples(): HasMany
    {
        return $this->hasMany(AdminProductDetailDownloadableSample::class, 'product_id')->orderBy('sort_order');
    }

    #[ApiProperty(writable: false)]
    public function customizable_options(): HasMany
    {
        return $this->hasMany(AdminProductDetailCustomizableOption::class, 'product_id')->orderBy('sort_order');
    }

    #[ApiProperty(writable: false)]
    public function attribute_values(): HasMany
    {
        return $this->hasMany(AdminProductDetailAttributeValue::class, 'product_id');
    }

    #[ApiProperty(writable: false)]
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(AdminProductDetailChannel::class, 'product_channels', 'product_id', 'channel_id');
    }

    #[ApiProperty(writable: false)]
    public function related_products(): BelongsToMany
    {
        return $this->belongsToMany(AdminProductDetailProductRef::class, 'product_relations', 'parent_id', 'child_id');
    }

    #[ApiProperty(writable: false)]
    public function up_sells(): BelongsToMany
    {
        return $this->belongsToMany(AdminProductDetailProductRef::class, 'product_up_sells', 'parent_id', 'child_id');
    }

    #[ApiProperty(writable: false)]
    public function cross_sells(): BelongsToMany
    {
        return $this->belongsToMany(AdminProductDetailProductRef::class, 'product_cross_sells', 'parent_id', 'child_id');
    }

    #[ApiProperty(writable: false)]
    public function getNameAttribute(): ?string
    {
        return $this->scalar('name', fn () => $this->flat()->name ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getStatusAttribute(): ?int
    {
        return $this->scalar('status', fn () => isset($this->flat()->status) ? (int) $this->flat()->status : null);
    }

    #[ApiProperty(writable: false)]
    public function getPriceAttribute(): ?string
    {
        return $this->scalar('price', fn () => isset($this->flat()->price) ? (string) $this->flat()->price : null);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedPriceAttribute(): ?string
    {
        return $this->scalar('formatted_price', fn () => isset($this->flat()->price) ? core()->formatBasePrice((float) $this->flat()->price) : null);
    }

    #[ApiProperty(writable: false)]
    public function getSpecialPriceAttribute(): ?string
    {
        return $this->scalar('special_price', fn () => isset($this->flat()->special_price) ? (string) $this->flat()->special_price : null);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedSpecialPriceAttribute(): ?string
    {
        return $this->scalar('formatted_special_price', fn () => isset($this->flat()->special_price) ? core()->formatBasePrice((float) $this->flat()->special_price) : null);
    }

    #[ApiProperty(writable: false)]
    public function getSpecialPriceFromAttribute(): ?string
    {
        return $this->scalar('special_price_from', fn () => $this->flat()->special_price_from ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getSpecialPriceToAttribute(): ?string
    {
        return $this->scalar('special_price_to', fn () => $this->flat()->special_price_to ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getUrlKeyAttribute(): ?string
    {
        return $this->scalar('url_key', fn () => $this->flat()->url_key ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getShortDescriptionAttribute(): ?string
    {
        return $this->scalar('short_description', fn () => $this->flat()->short_description ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getDescriptionAttribute(): ?string
    {
        return $this->scalar('description', fn () => $this->flat()->description ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getMetaTitleAttribute(): ?string
    {
        return $this->scalar('meta_title', fn () => $this->flat()->meta_title ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getMetaDescriptionAttribute(): ?string
    {
        return $this->scalar('meta_description', fn () => $this->flat()->meta_description ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getMetaKeywordsAttribute(): ?string
    {
        return $this->scalar('meta_keywords', fn () => $this->flat()->meta_keywords ?? null);
    }

    #[ApiProperty(writable: false)]
    public function getWeightAttribute(): ?float
    {
        return $this->scalar('weight', fn () => isset($this->flat()->weight) ? (float) $this->flat()->weight : null);
    }

    #[ApiProperty(writable: false)]
    public function getVisibleIndividuallyAttribute(): ?bool
    {
        return $this->scalar('visible_individually', fn () => isset($this->flat()->visible_individually) ? (bool) $this->flat()->visible_individually : null);
    }

    #[ApiProperty(writable: false)]
    public function getFeaturedAttribute(): ?bool
    {
        return $this->scalar('featured', fn () => isset($this->flat()->featured) ? (bool) $this->flat()->featured : null);
    }

    #[ApiProperty(writable: false)]
    public function getNewAttribute(): ?bool
    {
        return $this->scalar('new', fn () => isset($this->flat()->new) ? (bool) $this->flat()->new : null);
    }

    #[ApiProperty(writable: false)]
    public function getQuantityAttribute(): ?int
    {
        return $this->scalar('quantity', fn () => (int) DB::table('product_inventories')->where('product_id', $this->id)->sum('qty'));
    }

    #[ApiProperty(writable: false)]
    public function getImagesCountAttribute(): ?int
    {
        return $this->scalar('images_count', fn () => (int) DB::table('product_images')->where('product_id', $this->id)->count());
    }

    #[ApiProperty(writable: false)]
    public function getBaseImageUrlAttribute(): ?string
    {
        return $this->scalar('base_image_url', function () {
            $path = DB::table('product_images')->where('product_id', $this->id)->orderBy('position')->value('path');

            return $path ? Storage::url($path) : null;
        });
    }

    #[ApiProperty(writable: false)]
    public function getCategoryIdAttribute(): ?int
    {
        return $this->scalar('category_id', function () {
            $cid = DB::table('product_categories')->where('product_id', $this->id)->value('category_id');

            return $cid !== null ? (int) $cid : null;
        });
    }

    #[ApiProperty(writable: false)]
    public function getCategoryNameAttribute(): ?string
    {
        return $this->scalar('category_name', function () {
            $cid = DB::table('product_categories')->where('product_id', $this->id)->value('category_id');

            return $cid ? DB::table('category_translations')->where('category_id', $cid)
                ->orderByRaw('locale = ? desc', [app()->getLocale()])->value('name') : null;
        });
    }

    #[ApiProperty(writable: false)]
    public function getChannelAttribute(): ?string
    {
        return $this->scalar('channel', fn () => core()->getCurrentChannelCode());
    }

    #[ApiProperty(writable: false)]
    public function getLocaleAttribute(): ?string
    {
        return $this->scalar('locale', fn () => app()->getLocale());
    }

    #[ApiProperty(writable: false)]
    public function getAttributeFamilyNameAttribute(): ?string
    {
        return $this->scalar('attribute_family_name', fn () => $this->attribute_family_id
            ? DB::table('attribute_families')->where('id', $this->attribute_family_id)->value('name') : null);
    }

    #[ApiProperty(writable: false)]
    public function getTaxCategoryIdAttribute(): ?int
    {
        return $this->scalar('tax_category_id', function () {
            $v = DB::table('product_attribute_values')
                ->join('attributes', 'attributes.id', '=', 'product_attribute_values.attribute_id')
                ->where('product_attribute_values.product_id', $this->id)
                ->where('attributes.code', 'tax_category_id')
                ->value('product_attribute_values.integer_value');

            return $v !== null ? (int) $v : null;
        });
    }

    #[ApiProperty(writable: false)]
    public function getManageStockAttribute(): ?bool
    {
        return $this->scalar('manage_stock', function () {
            $v = DB::table('product_attribute_values')
                ->join('attributes', 'attributes.id', '=', 'product_attribute_values.attribute_id')
                ->where('product_attribute_values.product_id', $this->id)
                ->where('attributes.code', 'manage_stock')
                ->value('product_attribute_values.boolean_value');

            return $v !== null ? (bool) $v : null;
        });
    }

    #[ApiProperty(writable: false)]
    public function getInStockAttribute(): ?bool
    {
        return $this->scalar('in_stock', function () {
            try {
                return (bool) Product::find($this->id)?->getTypeInstance()?->isSaleable();
            } catch (\Throwable) {
                return null;
            }
        });
    }

    #[ApiProperty(writable: false)]
    public function getAttributesAttribute(): ?string
    {
        return null;
    }

    #[ApiProperty(writable: false)]
    public function getBookingProductAttribute(): ?string
    {
        return null;
    }

    #[ApiProperty(writable: false)]
    public function getWarningsAttribute(): ?string
    {
        return null;
    }

    private function scalar(string $key, callable $compute): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $compute();
    }

    private function flat(): object
    {
        if (! $this->flatLoaded) {
            $this->flatRow = DB::table('product_flat')->where('product_id', $this->id)
                ->orderByRaw('locale = ? desc', [app()->getLocale()])
                ->first() ?? (object) [];
            $this->flatLoaded = true;
        }

        return $this->flatRow;
    }
}
