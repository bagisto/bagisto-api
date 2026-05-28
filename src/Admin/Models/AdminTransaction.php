<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminTransactionDetailDto;
use Webkul\BagistoApi\Admin\State\AdminTransactionCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminTransactionItemProvider;

/**
 * Admin Order Transaction resource (read-only).
 *
 * REST  : GET /api/admin/transactions          (datagrid listing)
 *         GET /api/admin/transactions/{id}     (detail)
 * GraphQL: adminTransactions cursor query + adminTransaction(id:) query.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminTransaction',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/transactions',
            provider: AdminTransactionCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales'],
                summary: 'List order transactions (datagrid parity)',
                description: 'Paginated transactions listing mirroring the admin Sales → Transactions datagrid. Returns a `{ data, meta }` envelope. Requires `sales.transactions.view` permission.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'query', 'Filter by transaction row id (integer or comma-list).', false, schema: ['type' => 'string']),
                    new Model\Parameter('transaction_id', 'query', 'Partial gateway transaction id.', false, schema: ['type' => 'string']),
                    new Model\Parameter('invoice_id', 'query', 'Filter by invoice id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id.', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Transaction status.', false, schema: ['type' => 'string', 'enum' => ['paid', 'pending', 'COMPLETED']]),
                    new Model\Parameter('created_at_from', 'query', 'Created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_to', 'query', 'Created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'transaction_id', 'amount', 'invoice_id', 'order_id', 'status', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated transactions in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [[
                                        'id'               => 4,
                                        'transactionId'    => 'pi_3PqXyz...',
                                        'invoiceId'        => 12,
                                        'orderId'          => 8,
                                        'orderIncrementId' => '00000000008',
                                        'amount'           => 99.99,
                                        'formattedAmount'  => '$99.99',
                                        'status'           => 'paid',
                                        'type'             => 'capture',
                                        'paymentMethod'    => 'cashondelivery',
                                        'createdAt'        => '2026-05-20 12:35:00',
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
            uriTemplate: '/transactions/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminTransactionItemProvider::class,
            output: AdminTransactionDetailDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales'],
                summary: 'Get a transaction by id',
                parameters: [
                    new Model\Parameter('id', 'path', 'Transaction row ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminTransactionItemProvider::class,
            output: AdminTransactionDetailDto::class,
            description: 'Get an order transaction by id.',
        ),
        new QueryCollection(
            provider: AdminTransactionCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin order transactions listing (cursor pagination).',
            extraArgs: [
                'id'              => ['type' => 'String'],
                'transaction_id'  => ['type' => 'String'],
                'invoice_id'      => ['type' => 'Int'],
                'order_id'        => ['type' => 'String'],
                'status'          => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to'   => ['type' => 'String'],
                'sort'            => ['type' => 'String'],
                'order'           => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminTransaction
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}
