<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminNotificationReadAllInput;
use Webkul\BagistoApi\Admin\Dto\AdminNotificationReadInput;
use Webkul\BagistoApi\Admin\Resolver\AdminNotificationItemResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminNotificationSummaryResolver;
use Webkul\BagistoApi\Admin\State\AdminNotificationProcessor;
use Webkul\BagistoApi\Admin\State\AdminNotificationProvider;

/**
 * Admin notification bell: one notification per order event, with read state.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminNotification',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/notifications',
            provider: AdminNotificationProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Notifications'],
                summary: 'List notifications',
                description: 'Order notifications, newest first, as shown in the admin notification bell and Notifications page. `meta.statusCounts` gives the number of notifications per order status and `meta.totalUnread` the unread total; both ignore the filters.',
                parameters: [
                    new Model\Parameter('status', 'query', 'Order status to filter by (pending, processing, completed, canceled, closed, fraud, pending_payment). `All` or omitted means every status.', schema: ['type' => 'string']),
                    new Model\Parameter('read', 'query', '0 for unread only, 1 for read only.', schema: ['type' => 'integer', 'enum' => [0, 1]]),
                    new Model\Parameter('page', 'query', 'Page number.', schema: ['type' => 'integer', 'default' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50). `limit` is accepted as an alias.', schema: ['type' => 'integer', 'default' => 10]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Notifications.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'data' => [[
                                'id' => 12,
                                'orderId' => 45,
                                'type' => 'order',
                                'read' => false,
                                'orderIncrementId' => '45',
                                'orderStatus' => 'pending',
                                'orderStatusLabel' => 'Pending',
                                'createdAt' => '2026-10-15T09:12:44+00:00',
                                'updatedAt' => '2026-10-15T09:12:44+00:00',
                                'totalUnread' => null,
                                'statusCounts' => null,
                                'message' => null,
                            ]],
                            'meta' => [
                                'currentPage' => 1,
                                'perPage' => 10,
                                'lastPage' => 1,
                                'total' => 1,
                                'from' => 1,
                                'to' => 1,
                                'statusCounts' => [['status' => 'pending', 'statusCount' => 1]],
                                'totalUnread' => 1,
                            ],
                        ]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/notifications/read-all',
            processor: AdminNotificationProcessor::class,
            status: 200,
            read: false,
            deserialize: false,
            openapi: new Model\Operation(
                tags: ['Admin Notifications'],
                summary: 'Mark all notifications as read',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'All notifications marked as read.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'totalUnread' => 0,
                            'message' => 'All notifications have been marked as read',
                        ]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/notifications/{orderId}/read',
            requirements: ['orderId' => '\d+'],
            processor: AdminNotificationProcessor::class,
            status: 200,
            read: false,
            deserialize: false,
            openapi: new Model\Operation(
                tags: ['Admin Notifications'],
                summary: 'Mark an order notification as read',
                description: 'Marks the notification of the given order as read, as opening it from the bell does.',
                requestBody: new Model\RequestBody(required: false, content: new \ArrayObject([
                    'application/json' => ['schema' => ['type' => 'object'], 'example' => new \stdClass],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'The notification, now read.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'id' => 12,
                            'orderId' => 45,
                            'type' => 'order',
                            'read' => true,
                            'orderIncrementId' => '45',
                            'orderStatus' => 'pending',
                            'orderStatusLabel' => 'Pending',
                            'createdAt' => '2026-10-15T09:12:44+00:00',
                            'updatedAt' => '2026-10-16T08:01:02+00:00',
                        ]]]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '404' => new Model\Response(description: 'No notification for that order.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminNotificationProvider::class,
            paginationType: 'cursor',
            extraArgs: [
                'status' => ['type' => 'String', 'description' => 'Order status filter; `All` or omitted means every status.'],
                'read' => ['type' => 'Int', 'description' => '0 for unread only, 1 for read only.'],
            ],
            description: 'Order notifications, newest first.',
        ),
        new Query(
            resolver: AdminNotificationItemResolver::class,
            description: 'One notification by id.',
        ),
        new Query(
            name: 'summary',
            resolver: AdminNotificationSummaryResolver::class,
            args: [],
            description: 'Unread total and per-order-status notification counts for the bell badge.',
        ),
        new Mutation(
            name: 'read',
            input: AdminNotificationReadInput::class,
            processor: AdminNotificationProcessor::class,
            read: false,
            description: 'Mark the notification of an order as read. The `id` input is ignored; pass orderId.',
        ),
        new Mutation(
            name: 'readAll',
            input: AdminNotificationReadAllInput::class,
            processor: AdminNotificationProcessor::class,
            read: false,
            description: 'Mark every notification as read. The `id` input is ignored.',
        ),
    ],
)]
class AdminNotification
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?int $order_id = null;

    #[ApiProperty(writable: false)]
    public ?string $type = null;

    #[ApiProperty(writable: false)]
    public ?bool $read = null;

    #[ApiProperty(writable: false)]
    public ?string $order_increment_id = null;

    #[ApiProperty(writable: false)]
    public ?string $order_status = null;

    #[ApiProperty(writable: false)]
    public ?string $order_status_label = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    #[ApiProperty(writable: false, description: 'Unread total; set on the summary query and the read-all result only.')]
    public ?int $total_unread = null;

    #[ApiProperty(writable: false, description: 'Notification count per order status, `[{ status, statusCount }]`; summary query only.')]
    public ?array $status_counts = null;

    #[ApiProperty(writable: false)]
    public ?string $message = null;
}
