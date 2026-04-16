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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Dto\CreateCompareItemInput;
use Webkul\BagistoApi\Dto\DeleteCompareItemInput;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
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
        new Get,
        new GetCollection(provider: CompareItemProvider::class),
        new Post(processor: CompareItemProcessor::class),
        new Delete,
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
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
