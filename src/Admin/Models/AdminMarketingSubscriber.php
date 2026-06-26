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
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSubscriberRestDto;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSubscriberUpdateInput;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberItemProvider;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberProcessor;
use Webkul\BagistoApi\Admin\State\AdminMarketingSubscriberWriteProvider;

/**
 * Admin Marketing → Newsletter Subscribers (Block F2d).
 *
 * REST:
 *   GET    /api/admin/marketing/subscribers
 *   GET    /api/admin/marketing/subscribers/{id}
 *   PUT    /api/admin/marketing/subscribers/{id}     (toggle is_subscribed)
 *   DELETE /api/admin/marketing/subscribers/{id}
 *
 * GraphQL: adminMarketingSubscribers, adminMarketingSubscriber,
 *          updateAdminMarketingSubscriber, deleteAdminMarketingSubscriber
 *
 * Subscriptions are created via storefront; admin only moderates.
 * Mirrors Webkul\Admin\Http\Controllers\Marketing\Communications\SubscriptionController.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMarketingSubscriber',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Put(
            uriTemplate: '/marketing/subscribers/{id}',
            input: AdminMarketingSubscriberUpdateInput::class,
            output: AdminMarketingSubscriberRestDto::class,
            provider: AdminMarketingSubscriberWriteProvider::class,
            processor: AdminMarketingSubscriberProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Toggle a newsletter subscription',
                description: 'Sets is_subscribed for the subscriber row and mirrors the flag onto the linked customer (if any).',
                parameters: [
                    new Model\Parameter('id', 'path', 'Subscriber ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['is_subscribed'],
                                'properties' => [
                                    'is_subscribed' => ['type' => 'boolean', 'example' => false],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Subscription updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'           => 26,
                                    'email'        => 'ddd@gmail.com',
                                    'isSubscribed' => false,
                                    'customerId'   => null,
                                    'customerName' => null,
                                    'channel'      => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'createdAt'    => '2025-12-30T18:32:42+05:30',
                                    'updatedAt'    => '2026-06-17T12:14:15+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Subscriber not found.'),
                    '422' => new Model\Response(description: 'Validation failed (is_subscribed required).'),
                ],
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/subscribers/{id}',
            provider: AdminMarketingSubscriberWriteProvider::class,
            processor: AdminMarketingSubscriberProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Delete a subscription',
                parameters: [
                    new Model\Parameter('id', 'path', 'Subscriber ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Subscriber deleted.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'message' => 'Subscriber deleted.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Subscriber not found.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/subscribers/{id}',
            provider: AdminMarketingSubscriberItemProvider::class,
            output: AdminMarketingSubscriberRestDto::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'Subscription detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Subscriber ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Subscriber detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'           => 26,
                                    'email'        => 'ddd@gmail.com',
                                    'isSubscribed' => true,
                                    'customerId'   => null,
                                    'customerName' => null,
                                    'channel'      => ['id' => 1, 'code' => 'default', 'name' => 'Default'],
                                    'createdAt'    => '2025-12-30T18:32:42+05:30',
                                    'updatedAt'    => '2026-06-17T12:14:15+05:30',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Subscriber not found.'),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/subscribers',
            provider: AdminMarketingSubscriberCollectionProvider::class,
            output: AdminMarketingSubscriberRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing: Communications'],
                summary: 'List newsletter subscribers',
                description: 'Paginated, filterable, sortable list. Returns { data, meta } envelope.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('email', 'query', 'Email LIKE filter', false, schema: ['type' => 'string']),
                    new Model\Parameter('channel_id', 'query', 'Filter by channel id', false, schema: ['type' => 'integer']),
                    new Model\Parameter('is_subscribed', 'query', '0 or 1', false, schema: ['type' => 'integer']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'email']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated subscriber list.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'           => 26,
                                            'email'        => 'ddd@gmail.com',
                                            'isSubscribed' => true,
                                            'customerId'   => null,
                                            'customerName' => null,
                                            'channel'      => null,
                                            'createdAt'    => '2025-12-30T18:32:42+05:30',
                                            'updatedAt'    => '2026-06-17T12:14:15+05:30',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 3,
                                        'total'       => 25,
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
        new QueryCollection(
            provider: AdminMarketingSubscriberCollectionProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'email'         => ['type' => 'String'],
                'channel_id'    => ['type' => 'Int'],
                'is_subscribed' => ['type' => 'Int'],
                'sort'          => ['type' => 'String'],
                'order'         => ['type' => 'String'],
            ],
            description: 'Admin newsletter subscribers listing (cursor pagination).',
        ),
        new Query(
            provider: AdminMarketingSubscriberItemProvider::class,
            description: 'Admin newsletter subscriber detail by id.',
        ),
        new Mutation(
            name: 'update',
            input: AdminMarketingSubscriberUpdateInput::class,
            processor: AdminMarketingSubscriberProcessor::class,
            description: 'Toggle subscription status. Becomes updateAdminMarketingSubscriber.',
        ),
        new Mutation(
            name: 'delete',
            input: AdminMarketingSubscriberUpdateInput::class,
            processor: AdminMarketingSubscriberProcessor::class,
            description: 'Delete subscription. Becomes deleteAdminMarketingSubscriber.',
        ),
    ],
)]
class AdminMarketingSubscriber extends EloquentModel
{
    /** @var string */
    protected $table = 'subscribers_list';

    /** @var array */
    protected $casts = [
        'id'            => 'int',
        'is_subscribed' => 'boolean',
        'customer_id'   => 'int',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /** @var array */
    protected $appends = ['customer_name'];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id !== null ? (int) $this->id : null;
    }

    /**
     * Channel this subscriber belongs to (GraphQL to-one object —
     * `channel { id _id code name }`). The belongsTo on `channel_id` consumes
     * that FK column, replacing the old channelId / channelName scalars.
     */
    #[ApiProperty(writable: false)]
    public function channel(): BelongsTo
    {
        return $this->belongsTo(AdminMarketingChannelRef::class, 'channel_id');
    }

    /**
     * Linked customer's full name (kept as a scalar — NOT objectified). String
     * accessor (safe over GraphQL). Prefers a pre-set value (the listing
     * forceFills it to avoid N+1) else resolves from `customer_id`.
     */
    #[ApiProperty(writable: false)]
    public function getCustomerNameAttribute(): ?string
    {
        if (array_key_exists('customer_name', $this->attributes)) {
            return $this->attributes['customer_name'] ?: null;
        }

        if (! $this->customer_id) {
            return null;
        }

        $row = DB::table('customers')
            ->where('id', $this->customer_id)
            ->select('first_name', 'last_name')
            ->first();

        if (! $row) {
            return null;
        }

        return trim((string) ($row->first_name ?? '').' '.(string) ($row->last_name ?? '')) ?: null;
    }
}
