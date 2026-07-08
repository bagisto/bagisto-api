<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Dto\CreateCompareItemInput;
use Webkul\BagistoApi\Dto\DeleteCompareItemInput;
use Webkul\BagistoApi\Resolver\CompareItemQueryResolver;
use Webkul\BagistoApi\State\CompareItemItemProvider;
use Webkul\BagistoApi\State\CompareItemProcessor;
use Webkul\BagistoApi\State\CompareItemProvider;

/**
 * Compare Item API Resource
 *
 * Allows customers to compare products
 */
#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            provider: CompareItemItemProvider::class,
            openapi: new Operation(
                tags: ['CompareItem'],
                summary: 'Get a compare item',
                description: 'Returns a single compare item owned by the authenticated customer.',
                responses: [
                    '200' => new Response(
                        description: 'The compare item.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 84,
                                    'createdAt' => '2026-07-02T12:29:41+05:30',
                                    'updatedAt' => '2026-07-02T12:29:41+05:30',
                                    'product' => '/api/shop/products/1',
                                    'customer' => '/api/shop/customers/1535',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Compare item not found or not owned by the caller.',
                    ),
                ],
            ),
        ),
        new GetCollection(
            provider: CompareItemProvider::class,
            openapi: new Operation(
                tags: ['CompareItem'],
                summary: 'List compare items',
                description: 'Returns the authenticated customer\'s compare list.',
                responses: [
                    '200' => new Response(
                        description: 'The compare items.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 84,
                                        'createdAt' => '2026-07-02T12:29:41+05:30',
                                        'updatedAt' => '2026-07-02T12:29:41+05:30',
                                        'product' => '/api/shop/products/1',
                                        'customer' => '/api/shop/customers/1535',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            processor: CompareItemProcessor::class,
            openapi: new Operation(
                tags: ['CompareItem'],
                summary: 'Add a product to compare list',
                description: 'Adds a product to the authenticated customer\'s compare list.',
                requestBody: new RequestBody(
                    description: 'Product to add to the compare list',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['productId'],
                                'properties' => [
                                    'productId' => ['type' => 'integer', 'format' => 'int64', 'example' => 1],
                                ],
                            ],
                            'example' => [
                                'product_id' => 1,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'The product was added to the compare list.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 84,
                                    'createdAt' => '2026-07-02T12:29:41+05:30',
                                    'updatedAt' => '2026-07-02T12:29:41+05:30',
                                    'product' => '/api/shop/products/1',
                                    'customer' => '/api/shop/customers/1535',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(
                        description: 'Missing/invalid product_id, or product already in compare list.',
                    ),
                ],
            ),
        ),
        new Delete(
            processor: CompareItemProcessor::class,
            openapi: new Operation(
                tags: ['CompareItem'],
                summary: 'Remove a compare item',
                description: 'Removes a compare item owned by the authenticated customer.',
                responses: [
                    '204' => new Response(
                        description: 'Compare item removed. No content.',
                    ),
                    '404' => new Response(
                        description: 'Compare item not found or not owned by the caller.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: CompareItemQueryResolver::class),
        new QueryCollection(
            provider: CompareItemProvider::class,
            paginationType: 'cursor',
        ),
        new Mutation(
            name: 'create',
            input: CreateCompareItemInput::class,
            output: CompareItem::class,
            processor: CompareItemProcessor::class,
        ),
        new Mutation(
            name: 'delete',
            input: DeleteCompareItemInput::class,
            output: CompareItem::class,
            processor: CompareItemProcessor::class,
        ),
    ],
)]
class CompareItem extends \Webkul\Customer\Models\CompareItem
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Product relationship for API
     */
    #[ApiProperty(writable: false, description: 'Associated product')]
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Customer relationship for API
     */
    #[ApiProperty(writable: false, description: 'Customer who added the item')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
