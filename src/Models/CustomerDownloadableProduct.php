<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CustomerDownloadableProductProvider;

/**
 * Customer Downloadable Product API Resource
 *
 * Returns downloadable link purchases belonging to the authenticated customer.
 * This is a read-only, customer-scoped resource.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerDownloadableProduct',
    uriTemplate: '/customer-downloadable-products',
    operations: [
        new GetCollection(
            uriTemplate: '/customer-downloadable-products',
            provider: CustomerDownloadableProductProvider::class,
        ),
        new Get(
            uriTemplate: '/customer-downloadable-products/{id}',
            provider: CustomerDownloadableProductProvider::class,
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
        ),
        new QueryCollection(
            provider: CustomerDownloadableProductProvider::class,
            paginationType: 'cursor',
            args: [
                'status' => ['type' => 'String', 'description' => 'Filter by status (available, expired, pending)'],
                'first'  => ['type' => 'Int', 'description' => 'Number of items to return from the start'],
                'last'   => ['type' => 'Int', 'description' => 'Number of items to return from the end'],
                'after'  => ['type' => 'String', 'description' => 'Cursor to start pagination after'],
                'before' => ['type' => 'String', 'description' => 'Cursor to start pagination before'],
            ],
        ),
    ],
)]
class CustomerDownloadableProduct extends \Webkul\Sales\Models\DownloadableLinkPurchased
{
    /** @var string */
    protected $table = 'downloadable_link_purchased';

    /** @var array */
    protected $casts = [
        'id'                => 'int',
        'download_bought'   => 'int',
        'download_used'     => 'int',
        'download_canceled' => 'int',
        'customer_id'       => 'int',
        'order_id'          => 'int',
        'order_item_id'     => 'int',
    ];

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Remaining downloads accessor
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
     * Download URL for the purchased product file.
     */
    #[ApiProperty(writable: false, readable: true, description: 'URL to download the purchased file (requires Authorization header)')]
    public function getDownloadUrlAttribute(): ?string
    {
        return url('/api/shop/customer-downloadable-products/'.$this->id.'/download');
    }

    /**
     * Customer relationship
     */
    #[ApiProperty(writable: false, description: 'Customer who purchased the downloadable product')]
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Order relationship
     */
    #[ApiProperty(writable: false, description: 'Associated order')]
    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id');
    }
}
