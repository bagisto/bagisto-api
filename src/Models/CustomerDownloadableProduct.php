<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerDownloadableProductProvider;
use Webkul\Sales\Models\DownloadableLinkPurchased;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerDownloadableProduct',
    uriTemplate: '/customer-downloadable-products',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-downloadable-products',
            provider: CustomerDownloadableProductProvider::class,
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'List the customer\'s downloadable products',
                description: 'The authenticated customer\'s downloadable-product purchases, newest first, with the remaining downloads and the download URL; `status` filters by exact purchase status. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            ),
        ),
        new Get(
            uriTemplate: '/customer-downloadable-products/{id}',
            provider: CustomerDownloadableProductProvider::class,
            openapi: new Operation(
                tags: ['Customer Order'],
                summary: 'Get a downloadable-product purchase',
                description: 'One downloadable-product purchase of the authenticated customer; another customer\'s purchase or an unknown ID is a 404. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One downloadable-product purchase of the authenticated customer; another customer\'s purchase is not found. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
        ),
        new QueryCollection(
            provider: CustomerDownloadableProductProvider::class,
            description: 'The authenticated customer\'s downloadable-product purchases, newest first; `status` filters by exact purchase status. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header and a customer Bearer token.',
            paginationType: 'cursor',
            args: [
                'status' => [
                    'type' => 'String',
                    'description' => 'Filter by status (available, expired, pending)',
                ],

                'first' => [
                    'type' => 'Int',
                    'description' => 'Number of items to return from the start',
                ],

                'last' => [
                    'type' => 'Int',
                    'description' => 'Number of items to return from the end',
                ],

                'after' => [
                    'type' => 'String',
                    'description' => 'Cursor to start pagination after',
                ],

                'before' => [
                    'type' => 'String',
                    'description' => 'Cursor to start pagination before',
                ],
            ],
        ),
    ],
)]
class CustomerDownloadableProduct extends DownloadableLinkPurchased
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'downloadable_link_purchased';

    /**
     * The attributes cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'download_bought' => 'int',
        'download_used' => 'int',
        'download_canceled' => 'int',
        'customer_id' => 'int',
        'order_id' => 'int',
        'order_item_id' => 'int',
    ];

    /**
     * API Platform identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Remaining downloads accessor.
     */
    #[ApiProperty(writable: false, readable: true, description: 'Number of remaining downloads')]
    public function getRemainingDownloadsAttribute(): ?int
    {
        if (! $this->download_bought) {
            return null;
        }

        return max(0, $this->download_bought - $this->download_used - $this->download_canceled);
    }

    /**
     * The number of the order the product was bought with, as the downloadable products grid shows it.
     */
    #[ApiProperty(writable: false, readable: true, description: 'Increment ID of the order the product was bought with')]
    public function getOrderNumberAttribute(): ?string
    {
        $incrementId = $this->order?->increment_id;

        return $incrementId !== null ? (string) $incrementId : null;
    }

    /**
     * Download URL for the purchased product file.
     */
    #[ApiProperty(writable: false, readable: true, description: 'URL to download the purchased file (requires Authorization header)')]
    public function getDownloadUrlAttribute(): ?string
    {
        return url('/api/shop/customer-downloadable-products/'.$this->id.'/download');
    }

    /**
     * Customer relationship.
     */
    #[ApiProperty(writable: false, description: 'Customer who purchased the downloadable product')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Order relationship.
     */
    #[ApiProperty(writable: false, description: 'Associated order')]
    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id');
    }
}
