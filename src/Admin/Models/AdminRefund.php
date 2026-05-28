<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminRefundCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminRefundDetailDto;
use Webkul\BagistoApi\Admin\Dto\RefundTotalsSummary;
use Webkul\BagistoApi\Admin\State\AdminRefundCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRefundCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundPreviewProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminRefund',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/refunds',
            provider: AdminRefundCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales'],
                summary: 'List refunds (datagrid parity)',
                description: 'Paginated refunds listing mirroring the admin Sales → Refunds datagrid. Returns a `{ data, meta }` envelope. Requires `sales.refunds.view` permission.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'query', 'Filter by refund id (integer or comma-list).', false, schema: ['type' => 'string']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('state', 'query', 'Refund state.', false, schema: ['type' => 'string']),
                    new Model\Parameter('base_grand_total_from', 'query', 'Min refunded amount.', false, schema: ['type' => 'number']),
                    new Model\Parameter('base_grand_total_to', 'query', 'Max refunded amount.', false, schema: ['type' => 'number']),
                    new Model\Parameter('billed_to', 'query', 'Partial billed-to name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('created_at_from', 'query', 'Created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_to', 'query', 'Created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'order_id', 'state', 'base_grand_total', 'billed_to', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of refunds in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [[
                                        'id'                      => 3,
                                        'orderId'                 => 8,
                                        'orderIncrementId'        => '00000000008',
                                        'state'                   => 'refunded',
                                        'baseGrandTotal'          => 49.50,
                                        'formattedBaseGrandTotal' => '$49.50',
                                        'billedTo'                => 'John Doe',
                                        'createdAt'               => '2026-05-20 14:00:00',
                                    ]],
                                    'meta' => ['currentPage' => 1, 'perPage' => 10, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/refunds/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminRefundProvider::class,
            output: AdminRefundDetailDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Get refund detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Refund ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/refunds',
            input: AdminRefundCreateInput::class,
            output: AdminRefundDetailDto::class,
            processor: AdminRefundCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Create a refund for an order',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'items'             => [['orderItemId' => 42, 'quantity' => 1]],
                                'shipping'          => 0,
                                'adjustmentRefund'  => 0,
                                'adjustmentFee'     => 0,
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/refunds/preview',
            input: AdminRefundCreateInput::class,
            output: RefundTotalsSummary::class,
            processor: AdminRefundPreviewProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Preview refund totals without saving',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'items'             => [['orderItemId' => 42, 'quantity' => 1]],
                                'shipping'          => 0,
                                'adjustmentRefund'  => 0,
                                'adjustmentFee'     => 0,
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminRefundProvider::class,
            output: AdminRefundDetailDto::class,
        ),
        new QueryCollection(
            provider: AdminRefundCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin refunds datagrid listing (cursor pagination).',
            extraArgs: [
                'id'                    => ['type' => 'String'],
                'order_id'              => ['type' => 'String'],
                'state'                 => ['type' => 'String'],
                'base_grand_total_from' => ['type' => 'Float'],
                'base_grand_total_to'   => ['type' => 'Float'],
                'billed_to'             => ['type' => 'String'],
                'created_at_from'       => ['type' => 'String'],
                'created_at_to'         => ['type' => 'String'],
                'sort'                  => ['type' => 'String'],
                'order'                 => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminRefundCreateInput::class,
            output: AdminRefundDetailDto::class,
            processor: AdminRefundCreateProcessor::class,
        ),
        new Mutation(
            name: 'preview',
            input: AdminRefundCreateInput::class,
            output: RefundTotalsSummary::class,
            processor: AdminRefundPreviewProcessor::class,
            description: 'Preview refund totals without persisting.',
        ),
    ],
)]
class AdminRefund
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
