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
            provider: AdminMarketingSubscriberWriteProvider::class,
            processor: AdminMarketingSubscriberProcessor::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing Subscribers'],
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
            ),
        ),
        new Delete(
            uriTemplate: '/marketing/subscribers/{id}',
            provider: AdminMarketingSubscriberWriteProvider::class,
            processor: AdminMarketingSubscriberProcessor::class,
            requirements: ['id' => '\d+'],
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Marketing Subscribers'],
                summary: 'Delete a subscription',
                parameters: [
                    new Model\Parameter('id', 'path', 'Subscriber ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/marketing/subscribers/{id}',
            provider: AdminMarketingSubscriberItemProvider::class,
            requirements: ['id' => '\d+'],
            openapi: new Model\Operation(
                tags: ['Admin Marketing Subscribers'],
                summary: 'Subscription detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Subscriber ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/marketing/subscribers',
            provider: AdminMarketingSubscriberCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Marketing Subscribers'],
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
class AdminMarketingSubscriber
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $email = null;

    #[ApiProperty(writable: false)]
    public ?int $channelId = null;

    #[ApiProperty(writable: false)]
    public ?string $channelName = null;

    #[ApiProperty(writable: false)]
    public ?int $customerId = null;

    #[ApiProperty(writable: false)]
    public ?string $customerName = null;

    #[ApiProperty(writable: false)]
    public ?bool $isSubscribed = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;
}
