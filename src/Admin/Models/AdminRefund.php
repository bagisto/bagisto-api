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
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Dto\RefundTotalsSummary;
use Webkul\BagistoApi\Admin\State\AdminRefundCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminRefundCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminRefundExportProvider;
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
                tags: ['Admin Sales: Refunds'],
                summary: 'List refunds (datagrid parity)',
                description: 'Paginated refunds listing. Every refund column + billing/shipping addresses are populated per row (line items are detail-only). Returns a `{ data, meta }` envelope. Requires `sales.refunds.view`.',
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
            ),
        ),
        new Get(
            uriTemplate: '/refunds/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminRefundProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Refunds'],
                summary: 'Get refund detail',
                description: 'Full single-refund payload — every refund column (incl. adjustments), order/customer context, payment info, billing & shipping addresses, and embedded line items. Requires `sales.refunds.view`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Refund ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Refund detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'orderId' => 105,
                                    'orderIncrementId' => '105',
                                    'state' => 'refunded',
                                    'emailSent' => true,
                                    'totalQty' => 3,
                                    'orderCurrencyCode' => 'USD',
                                    'baseCurrencyCode' => 'USD',
                                    'channelCurrencyCode' => 'USD',
                                    'subTotal' => 4203,
                                    'formattedSubTotal' => '$4,203.00',
                                    'baseSubTotal' => 4203,
                                    'formattedBaseSubTotal' => '$4,203.00',
                                    'subTotalInclTax' => 4203,
                                    'formattedSubTotalInclTax' => '$4,203.00',
                                    'baseSubTotalInclTax' => 4203,
                                    'formattedBaseSubTotalInclTax' => '$4,203.00',
                                    'grandTotal' => 4233,
                                    'formattedGrandTotal' => '$4,233.00',
                                    'baseGrandTotal' => 4233,
                                    'formattedBaseGrandTotal' => '$4,233.00',
                                    'taxAmount' => 0,
                                    'formattedTaxAmount' => '$0.00',
                                    'baseTaxAmount' => 0,
                                    'formattedBaseTaxAmount' => '$0.00',
                                    'discountAmount' => 0,
                                    'formattedDiscountAmount' => '$0.00',
                                    'baseDiscountAmount' => 0,
                                    'formattedBaseDiscountAmount' => '$0.00',
                                    'shippingAmount' => 30,
                                    'formattedShippingAmount' => '$30.00',
                                    'baseShippingAmount' => 30,
                                    'formattedBaseShippingAmount' => '$30.00',
                                    'shippingAmountInclTax' => 30,
                                    'formattedShippingAmountInclTax' => '$30.00',
                                    'baseShippingAmountInclTax' => 30,
                                    'formattedBaseShippingAmountInclTax' => '$30.00',
                                    'shippingTaxAmount' => 0,
                                    'formattedShippingTaxAmount' => '$0.00',
                                    'baseShippingTaxAmount' => 0,
                                    'formattedBaseShippingTaxAmount' => '$0.00',
                                    'adjustmentRefund' => 0,
                                    'formattedAdjustmentRefund' => '$0.00',
                                    'baseAdjustmentRefund' => 0,
                                    'formattedBaseAdjustmentRefund' => '$0.00',
                                    'adjustmentFee' => 0,
                                    'formattedAdjustmentFee' => '$0.00',
                                    'baseAdjustmentFee' => 0,
                                    'formattedBaseAdjustmentFee' => '$0.00',
                                    'createdAt' => '2026-05-20 14:00:00',
                                    'updatedAt' => '2026-05-20 14:00:02',
                                    'billedTo' => 'John Doe',
                                    'orderStatus' => 'closed',
                                    'orderStatusLabel' => 'Closed',
                                    'orderDate' => '2026-05-19 16:47:17',
                                    'channelName' => 'bagisto store',
                                    'customerName' => 'John Doe',
                                    'customerEmail' => 'john.doe@example.com',
                                    'paymentMethod' => 'cashondelivery',
                                    'paymentTitle' => 'Cash On Delivery',
                                    'shippingMethod' => 'flatrate_flatrate',
                                    'shippingTitle' => 'Flat Rate - Flat Rate',
                                    'billingAddress' => [
                                        'id' => 493, 'addressType' => 'order_billing', 'firstName' => 'John', 'lastName' => 'Doe',
                                        'companyName' => 'Acme Trades', 'address' => '21 Market Street', 'city' => 'Los Angeles',
                                        'state' => 'CA', 'country' => 'US', 'postcode' => '90001', 'email' => 'john.doe@example.com', 'phone' => '5551234567',
                                    ],
                                    'shippingAddress' => [
                                        'id' => 492, 'addressType' => 'order_shipping', 'firstName' => 'John', 'lastName' => 'Doe',
                                        'companyName' => 'Acme Trades', 'address' => '21 Market Street', 'city' => 'Los Angeles',
                                        'state' => 'CA', 'country' => 'US', 'postcode' => '90001', 'email' => 'john.doe@example.com', 'phone' => '5551234567',
                                    ],
                                    'items' => [[
                                        'id' => 1, 'orderItemId' => 119, 'sku' => 'Nike-Shoes', 'name' => 'Nike Shoes', 'qty' => 1,
                                        'price' => 123, 'formattedPrice' => '$123.00', 'basePrice' => 123, 'basePriceInclTax' => 123,
                                        'total' => 123, 'formattedTotal' => '$123.00', 'baseTotal' => 123, 'baseTotalInclTax' => 123,
                                        'taxAmount' => 0, 'formattedTaxAmount' => '$0.00', 'discountAmount' => 0, 'formattedDiscountAmount' => '$0.00',
                                        'productId' => 114, 'productType' => 'simple', 'baseImageUrl' => 'https://example.com/storage/product/114/nike-shoes.webp',
                                        'additional' => ['locale' => 'en', 'quantity' => 1, 'product_id' => '114'],
                                    ]],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/refunds',
            input: AdminRefundCreateInput::class,
            processor: AdminRefundCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Create a refund for an order',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => [
                                'items' => [['orderItemId' => 42, 'quantity' => 1]],
                                'shipping' => 0,
                                'adjustmentRefund' => 0,
                                'adjustmentFee' => 0,
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
                tags: ['Admin Sales: Orders'],
                summary: 'Preview refund totals without saving',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => [
                                'items' => [['orderItemId' => 42, 'quantity' => 1]],
                                'shipping' => 0,
                                'adjustmentRefund' => 0,
                                'adjustmentFee' => 0,
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Get(
            uriTemplate: '/refunds/export',
            provider: AdminRefundExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Refunds'],
                summary: 'Export refunds as CSV',
                description: 'Downloads the refunds datagrid as a CSV file (`text/csv` attachment) — the same data the admin Refunds "Export" button produces (ID, Order ID, Refunded Amount, Billed To, Refund Date). Honours the same filters as the listing (`id`, `order_id`, `state`, `base_grand_total_from`/`_to`, `billed_to`, `created_at_from`/`_to`). The response is a binary download, not JSON. Requires `sales.refunds.view`.',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format. Currently only `csv` is supported.', false, schema: ['type' => 'string', 'enum' => ['csv'], 'default' => 'csv']),
                    new Model\Parameter('id', 'query', 'Filter by refund id (integer or comma-list).', false, schema: ['type' => 'string']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('state', 'query', 'Refund state.', false, schema: ['type' => 'string']),
                    new Model\Parameter('base_grand_total_from', 'query', 'Min refunded amount.', false, schema: ['type' => 'number']),
                    new Model\Parameter('base_grand_total_to', 'query', 'Max refunded amount.', false, schema: ['type' => 'number']),
                    new Model\Parameter('billed_to', 'query', 'Partial billed-to name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('created_at_from', 'query', 'Created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_to', 'query', 'Created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The refunds CSV file is downloaded (text/csv attachment).',
                        content: new \ArrayObject([
                            'text/csv' => [
                                'schema' => ['type' => 'string', 'format' => 'binary'],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.refunds.view.'),
                    '422' => new Model\Response(description: 'Unsupported format (only csv).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminRefundProvider::class,
            description: 'Get a refund by id.',
        ),
        new QueryCollection(
            provider: AdminRefundCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin refunds datagrid listing (cursor pagination).',
            extraArgs: [
                'id' => ['type' => 'String'],
                'order_id' => ['type' => 'String'],
                'state' => ['type' => 'String'],
                'base_grand_total_from' => ['type' => 'Float'],
                'base_grand_total_to' => ['type' => 'Float'],
                'billed_to' => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminRefundCreateInput::class,
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
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?string $state = null;

    public ?bool $email_sent = null;

    public ?int $total_qty = null;

    public ?string $order_currency_code = null;

    public ?string $base_currency_code = null;

    public ?string $channel_currency_code = null;

    public ?float $sub_total = null;

    public ?string $formatted_sub_total = null;

    public ?float $base_sub_total = null;

    public ?string $formatted_base_sub_total = null;

    public ?float $sub_total_incl_tax = null;

    public ?string $formatted_sub_total_incl_tax = null;

    public ?float $base_sub_total_incl_tax = null;

    public ?string $formatted_base_sub_total_incl_tax = null;

    public ?float $grand_total = null;

    public ?string $formatted_grand_total = null;

    public ?float $base_grand_total = null;

    public ?string $formatted_base_grand_total = null;

    public ?float $tax_amount = null;

    public ?string $formatted_tax_amount = null;

    public ?float $base_tax_amount = null;

    public ?string $formatted_base_tax_amount = null;

    public ?float $discount_amount = null;

    public ?string $formatted_discount_amount = null;

    public ?float $base_discount_amount = null;

    public ?string $formatted_base_discount_amount = null;

    public ?float $shipping_amount = null;

    public ?string $formatted_shipping_amount = null;

    public ?float $base_shipping_amount = null;

    public ?string $formatted_base_shipping_amount = null;

    public ?float $shipping_amount_incl_tax = null;

    public ?string $formatted_shipping_amount_incl_tax = null;

    public ?float $base_shipping_amount_incl_tax = null;

    public ?string $formatted_base_shipping_amount_incl_tax = null;

    public ?float $shipping_tax_amount = null;

    public ?string $formatted_shipping_tax_amount = null;

    public ?float $base_shipping_tax_amount = null;

    public ?string $formatted_base_shipping_tax_amount = null;

    public ?float $adjustment_refund = null;

    public ?string $formatted_adjustment_refund = null;

    public ?float $base_adjustment_refund = null;

    public ?string $formatted_base_adjustment_refund = null;

    public ?float $adjustment_fee = null;

    public ?string $formatted_adjustment_fee = null;

    public ?float $base_adjustment_fee = null;

    public ?string $formatted_base_adjustment_fee = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public ?string $billed_to = null;

    public ?string $order_status = null;

    public ?string $order_status_label = null;

    public ?string $order_date = null;

    public ?string $channel_name = null;

    public ?string $customer_name = null;

    public ?string $customer_email = null;

    public ?string $payment_method = null;

    public ?string $payment_title = null;

    public ?string $shipping_method = null;

    public ?string $shipping_title = null;

    public ?array $billing_address = null;

    public ?array $shipping_address = null;

    public array $items = [];
}
