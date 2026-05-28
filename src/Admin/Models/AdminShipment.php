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
use Webkul\BagistoApi\Admin\Dto\AdminShipmentCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminShipmentDetailDto;
use Webkul\BagistoApi\Admin\State\AdminShipmentCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminShipmentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminShipmentProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminShipment',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/shipments',
            provider: AdminShipmentCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales'],
                summary: 'List shipments (datagrid parity)',
                description: 'Paginated shipments listing mirroring the admin Sales → Shipments datagrid. Returns a `{ data, meta }` envelope. Requires `sales.shipments.view` permission.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by shipment id (integer or comma-list).', false, schema: ['type' => 'string', 'example' => '7']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('total_qty', 'query', 'Exact total quantity.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('inventory_source_name', 'query', 'Partial inventory source name.', false, schema: ['type' => 'string']),
                    new Model\Parameter('shipped_to', 'query', 'Partial shipped-to name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('order_date_from', 'query', 'Order created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('order_date_to', 'query', 'Order created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_from', 'query', 'Shipment created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_to', 'query', 'Shipment created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'order_id', 'total_qty', 'inventory_source_name', 'shipped_to', 'order_date', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of shipments in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [[
                                        'id'                  => 7,
                                        'orderId'             => 8,
                                        'orderIncrementId'    => '00000000008',
                                        'totalQty'            => 2,
                                        'inventorySourceName' => 'Default',
                                        'shippedTo'           => 'John Doe',
                                        'orderDate'           => '2026-05-20 10:00:00',
                                        'createdAt'           => '2026-05-20 12:00:00',
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
            uriTemplate: '/shipments/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminShipmentProvider::class,
            output: AdminShipmentDetailDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Get shipment detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Shipment ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/shipments',
            input: AdminShipmentCreateInput::class,
            output: AdminShipmentDetailDto::class,
            processor: AdminShipmentCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Create a shipment for an order',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'source'       => 1,
                                'items'        => [
                                    ['orderItemId' => 42, 'inventorySourceId' => 1, 'quantity' => 3],
                                ],
                                'carrierTitle' => 'UPS',
                                'trackNumber'  => '1Z999AA1',
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminShipmentProvider::class,
            output: AdminShipmentDetailDto::class,
        ),
        new QueryCollection(
            provider: AdminShipmentCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin shipments datagrid listing (cursor pagination).',
            extraArgs: [
                'id'                    => ['type' => 'String'],
                'order_id'              => ['type' => 'String'],
                'total_qty'             => ['type' => 'Int'],
                'inventory_source_name' => ['type' => 'String'],
                'shipped_to'            => ['type' => 'String'],
                'order_date_from'       => ['type' => 'String'],
                'order_date_to'         => ['type' => 'String'],
                'created_at_from'       => ['type' => 'String'],
                'created_at_to'         => ['type' => 'String'],
                'sort'                  => ['type' => 'String'],
                'order'                 => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminShipmentCreateInput::class,
            output: AdminShipmentDetailDto::class,
            processor: AdminShipmentCreateProcessor::class,
        ),
    ],
)]
class AdminShipment
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
