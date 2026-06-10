<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminProductProvider;

/**
 * Admin Product search/listing — slim row for the Create-Order "Add Product"
 * modal and any other admin product-picker workflow.
 *
 * REST   : GET /api/admin/products → `{ data: [AdminProduct], meta: {...} }`
 *          (envelope applied by AdminCollectionEnvelopeNormalizer).
 * GraphQL: adminProducts query → native cursor pagination.
 *
 * Differences from the shop /api/shop/products listing:
 *   - No automatic status=1 filter — admin sees disabled/draft products too
 *     (optional ?status=1 filter still works).
 *   - Returns booking products too (so they're visible); the booking guard in
 *     AdminCartAddItemProcessor blocks them at add-to-cart time.
 *   - Slim shape: id, sku, type, name, status, price, formattedPrice,
 *     baseImageUrl, isSaleable. NO heavy variants/bundleOptions/superAttributes
 *     relations — the picker just needs to identify the product.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminProduct',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/products',
            provider: AdminProductProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Products'],
                summary: 'Search/list products (admin)',
                description: 'Paginated, filterable product list for admin pickers. Returns ALL statuses (disabled/draft included) unless filtered. Supports booking products. Returns slim rows in a `{ data, meta }` envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (max 50, default 30)', false, schema: ['type' => 'integer', 'example' => 30]),
                    new Model\Parameter('query', 'query', 'Search term — matches product name OR SKU (partial match).', false, schema: ['type' => 'string']),
                    new Model\Parameter('sku', 'query', 'Exact SKU match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('type', 'query', 'Filter by product type.', false, schema: ['type' => 'string', 'enum' => ['simple', 'configurable', 'bundle', 'downloadable', 'grouped', 'virtual', 'booking']]),
                    new Model\Parameter('status', 'query', 'Filter by status (0=disabled, 1=enabled).', false, schema: ['type' => 'integer', 'enum' => [0, 1]]),
                    new Model\Parameter('categoryId', 'query', 'Filter by category ID.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('channel', 'query', 'Channel code for value resolution.', false, schema: ['type' => 'string']),
                    new Model\Parameter('locale', 'query', 'Locale code for value resolution.', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort field.', false, schema: ['type' => 'string', 'enum' => ['id', 'sku', 'created_at', 'updated_at'], 'example' => 'created_at']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of admin product rows in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'             => 142,
                                            'sku'            => 'SP-001',
                                            'type'           => 'simple',
                                            'name'           => 'Classic Watch',
                                            'status'         => 1,
                                            'price'          => 99.99,
                                            'formattedPrice' => '$99.99',
                                            'baseImageUrl'   => 'http://localhost:8000/cache/medium/product/142/image.webp',
                                            'isSaleable'     => true,
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 30,
                                        'lastPage'    => 8,
                                        'total'       => 231,
                                        'from'        => 1,
                                        'to'          => 30,
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
        new QueryCollection(
            provider: AdminProductProvider::class,
            paginationType: 'cursor',
            description: 'Admin product search (cursor pagination). Args: first, after, query, sku, type, status, categoryId, channel, locale.',
            extraArgs: [
                'query'      => ['type' => 'String'],
                'sku'        => ['type' => 'String'],
                'type'       => ['type' => 'String'],
                'status'     => ['type' => 'Int'],
                'categoryId' => ['type' => 'Int'],
                'channel'    => ['type' => 'String'],
                'locale'     => ['type' => 'String'],
            ],
        ),
    ]
)]
class AdminProduct
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $sku = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?string $name = null;

    #[ApiProperty(writable: false)]
    public ?int $status = null;

    #[ApiProperty(writable: false)]
    public ?float $price = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_price = null;

    #[ApiProperty(writable: false)]
    public ?string $base_image_url = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_saleable = null;
}
