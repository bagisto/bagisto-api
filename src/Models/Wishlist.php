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
use Webkul\BagistoApi\Dto\CreateWishlistInput;
use Webkul\BagistoApi\Dto\DeleteWishlistInput;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\WishlistProcessor;
use Webkul\BagistoApi\State\WishlistProvider;

/**
 * Wishlist Item API Resource
 *
 * Allows customers to add and manage products in their wishlist
 */
#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get,
        new GetCollection(provider: WishlistProvider::class),
        new Post(processor: WishlistProcessor::class),
        new Delete(processor: WishlistProcessor::class),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(
            provider: WishlistProvider::class,
            paginationType: 'cursor',
        ),
        new Mutation(
            name: 'create',
            input: CreateWishlistInput::class,
            output: Wishlist::class,
            processor: WishlistProcessor::class,
        ),
        new Mutation(
            name: 'toggle',
            args: [
                'productId' => [
                    'type'        => 'Int',
                    'description' => 'ID of the product to toggle in the wishlist.',
                ],
            ],
            input: CreateWishlistInput::class,
            output: Wishlist::class,
            processor: WishlistProcessor::class,
        ),
        new Mutation(
            name: 'delete',
            input: DeleteWishlistInput::class,
            output: Wishlist::class,
            processor: WishlistProcessor::class,
        ),
    ],
)]
class Wishlist extends \Webkul\Customer\Models\Wishlist
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

    /**
     * Channel relationship for API
     */
    #[ApiProperty(writable: false, description: 'Channel where item was added')]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
}
