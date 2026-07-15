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
use Webkul\BagistoApi\Admin\Dto\AdminTransactionCreateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminTransactionCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminTransactionCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminTransactionExportProvider;
use Webkul\BagistoApi\Admin\State\AdminTransactionItemProvider;

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
                tags: ['Admin Sales: Transactions'],
                summary: 'List order transactions (datagrid parity)',
                description: 'Paginated transactions listing mirroring the admin Sales → Transactions datagrid. Every transaction column plus the raw gateway `data` blob and the linked order summary is populated on each row. Returns a `{ data, meta }` envelope. Requires `sales.transactions.view` permission.',
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
                                    'data' => [self::SAMPLE],
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
            openapi: new Model\Operation(
                tags: ['Admin Sales: Transactions'],
                summary: 'Get a transaction by id',
                description: 'Returns a single payment transaction — every column, the raw gateway `data` blob, and a slim summary of the linked order. Requires `sales.transactions.view` permission.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Transaction row ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The transaction.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => self::SAMPLE],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Unknown transaction id.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.transactions.view.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/transactions/export',
            provider: AdminTransactionExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Transactions'],
                summary: 'Export transactions as CSV',
                description: 'Downloads the transactions datagrid as a CSV file (text/csv attachment) — the same data the admin Export button produces. Honours the same filters as the listing. Binary download, not JSON. Only ?format=csv is supported.',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format. Currently only csv.', false, schema: ['type' => 'string', 'enum' => ['csv'], 'default' => 'csv']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'CSV file downloaded (text/csv attachment).', content: new \ArrayObject(['text/csv' => ['schema' => ['type' => 'string', 'format' => 'binary']]])),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks the view permission.'),
                    '422' => new Model\Response(description: 'Unsupported format (only csv).'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/transactions',
            input: AdminTransactionCreateInput::class,
            processor: AdminTransactionCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Transactions'],
                summary: 'Record a payment (create transaction)',
                description: 'Records a manual payment against an invoice — the admin Sales → Transactions "Record Payment" action. Inserts a paid `order_transactions` row; when the cumulative payments reach the invoice grand total it marks the invoice paid and advances the order to completed (if a shipment exists) or processing. Returns the created transaction. Requires `sales.transactions.view` permission. Errors: 422 (missing/non-numeric fields), 400 (unknown invoice, invoice already paid, amount ≤ 0, or amount would exceed the invoice grand total).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['invoiceId', 'paymentMethod', 'amount'],
                                'properties' => [
                                    'invoiceId' => ['type' => 'integer', 'example' => 12],
                                    'paymentMethod' => ['type' => 'string', 'example' => 'cashondelivery'],
                                    'amount' => ['type' => 'number', 'example' => 99.99],
                                ],
                            ],
                            'example' => [
                                'invoiceId' => 12,
                                'paymentMethod' => 'cashondelivery',
                                'amount' => 99.99,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created transaction.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => self::SAMPLE],
                        ]),
                    ),
                    '400' => new Model\Response(description: 'Unknown invoice, invoice already paid, amount ≤ 0, or amount exceeds the invoice grand total.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.transactions.view.'),
                    '422' => new Model\Response(description: 'Missing or non-numeric invoiceId / paymentMethod / amount.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminTransactionItemProvider::class,
            description: 'Get an order transaction by id.',
        ),
        new QueryCollection(
            provider: AdminTransactionCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin order transactions listing (cursor pagination).',
            extraArgs: [
                'id' => ['type' => 'String'],
                'transaction_id' => ['type' => 'String'],
                'invoice_id' => ['type' => 'Int'],
                'order_id' => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminTransactionCreateInput::class,
            processor: AdminTransactionCreateProcessor::class,
            description: 'Record a payment against an invoice (create transaction).',
        ),
    ],
)]
class AdminTransaction
{
    use AcceptsCamelCaseWrites;

    private const SAMPLE = [
        'id' => 4,
        'transactionId' => 'pi_3PqXyz9aBcD',
        'invoiceId' => 12,
        'orderId' => 8,
        'orderIncrementId' => '00000000008',
        'amount' => 99.99,
        'formattedAmount' => '$99.99',
        'status' => 'paid',
        'type' => 'capture',
        'paymentMethod' => 'cashondelivery',
        'paymentTitle' => 'Cash On Delivery',
        'data' => ['gateway' => 'offline', 'captured' => true],
        'createdAt' => '2026-05-20 12:35:00',
        'updatedAt' => '2026-05-20 12:35:00',
        'order' => [
            'id' => 8,
            'incrementId' => '00000000008',
            'status' => 'processing',
            'customerName' => 'John Doe',
            'customerEmail' => 'john.doe@example.com',
            'grandTotal' => 99.99,
            'orderCurrencyCode' => 'USD',
        ],
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    public ?string $transaction_id = null;

    public ?int $invoice_id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?float $amount = null;

    public ?string $formatted_amount = null;

    public ?string $status = null;

    public ?string $type = null;

    public ?string $payment_method = null;

    public ?string $payment_title = null;

    public ?array $data = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public ?array $order = null;
}
