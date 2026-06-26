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
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceRestDto;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminInvoiceExportProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoicePrintProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminInvoice',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/invoices',
            provider: AdminInvoiceCollectionProvider::class,
            output: AdminInvoiceRestDto::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Invoices'],
                summary: 'List invoices (datagrid parity)',
                description: 'Paginated invoices listing. Every invoice column + billing/shipping addresses are populated per row (line items are detail-only). Returns a `{ data, meta }` envelope. Requires `sales.invoices.view`.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by invoice id (integer or comma-separated list).', false, schema: ['type' => 'string', 'example' => '12']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id match.', false, schema: ['type' => 'string', 'example' => '00000']),
                    new Model\Parameter('state', 'query', 'Filter by invoice state.', false, schema: ['type' => 'string', 'enum' => ['pending', 'pending_payment', 'paid', 'overdue'], 'example' => 'paid']),
                    new Model\Parameter('base_grand_total_from', 'query', 'Minimum base grand total.', false, schema: ['type' => 'number', 'example' => 0]),
                    new Model\Parameter('base_grand_total_to', 'query', 'Maximum base grand total.', false, schema: ['type' => 'number', 'example' => 1000]),
                    new Model\Parameter('created_at_from', 'query', 'Created after (ISO date).', false, schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-01-01']),
                    new Model\Parameter('created_at_to', 'query', 'Created before (ISO date).', false, schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-12-31']),
                    new Model\Parameter('sort', 'query', 'Column to sort by.', false, schema: ['type' => 'string', 'enum' => ['id', 'increment_id', 'order_id', 'base_grand_total', 'state', 'created_at'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/invoices/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminInvoiceProvider::class,
            output: AdminInvoiceRestDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Invoices'],
                summary: 'Get invoice detail',
                description: 'Full single-invoice payload — every invoice column, order/customer context, billing & shipping addresses, and embedded line items. Requires `sales.invoices.view`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Invoice ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Invoice detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'                                    => 585,
                                    'incrementId'                           => '585',
                                    'orderId'                               => 41,
                                    'orderIncrementId'                      => '41',
                                    'state'                                 => 'paid',
                                    'emailSent'                             => true,
                                    'totalQty'                              => 1,
                                    'orderCurrencyCode'                     => 'USD',
                                    'baseCurrencyCode'                      => 'USD',
                                    'channelCurrencyCode'                   => 'USD',
                                    'subTotal'                              => 4000,
                                    'formattedSubTotal'                     => '$4,000.00',
                                    'baseSubTotal'                          => 4000,
                                    'formattedBaseSubTotal'                 => '$4,000.00',
                                    'subTotalInclTax'                       => 4000,
                                    'formattedSubTotalInclTax'              => '$4,000.00',
                                    'baseSubTotalInclTax'                   => 4000,
                                    'formattedBaseSubTotalInclTax'          => '$4,000.00',
                                    'grandTotal'                            => 4000,
                                    'formattedGrandTotal'                   => '$4,000.00',
                                    'baseGrandTotal'                        => 4000,
                                    'formattedBaseGrandTotal'               => '$4,000.00',
                                    'taxAmount'                             => 0,
                                    'formattedTaxAmount'                    => '$0.00',
                                    'baseTaxAmount'                         => 0,
                                    'formattedBaseTaxAmount'                => '$0.00',
                                    'discountAmount'                        => 0,
                                    'formattedDiscountAmount'               => '$0.00',
                                    'baseDiscountAmount'                    => 0,
                                    'formattedBaseDiscountAmount'           => '$0.00',
                                    'shippingAmount'                        => 0,
                                    'formattedShippingAmount'               => '$0.00',
                                    'baseShippingAmount'                    => 0,
                                    'formattedBaseShippingAmount'           => '$0.00',
                                    'shippingAmountInclTax'                 => 0,
                                    'formattedShippingAmountInclTax'        => '$0.00',
                                    'baseShippingAmountInclTax'             => 0,
                                    'formattedBaseShippingAmountInclTax'    => '$0.00',
                                    'shippingTaxAmount'                     => 0,
                                    'formattedShippingTaxAmount'            => '$0.00',
                                    'baseShippingTaxAmount'                 => 0,
                                    'formattedBaseShippingTaxAmount'        => '$0.00',
                                    'transactionId'                         => null,
                                    'reminders'                             => 0,
                                    'nextReminderAt'                        => null,
                                    'createdAt'                             => '2026-06-04 17:52:47',
                                    'updatedAt'                             => '2026-06-04 18:34:43',
                                    'orderStatus'                           => 'processing',
                                    'orderStatusLabel'                      => 'Processing',
                                    'orderDate'                             => '2024-06-13 17:28:47',
                                    'channelName'                           => 'bagisto store',
                                    'customerName'                          => 'webkul pvt ltd',
                                    'customerEmail'                         => 'abhijit.kumar018@webkul.in',
                                    'order'                                 => [
                                        'id'        => 41,
                                        'addresses' => [
                                            [
                                                'id'          => 190,
                                                'addressType' => 'order_billing',
                                                'firstName'   => 'webkul',
                                                'lastName'    => 'pvt ltd',
                                                'companyName' => 'C. Trades',
                                                'address'     => 'noida arv park 63',
                                                'city'        => 'Wyoming',
                                                'state'       => 'Uttar Pradesh',
                                                'country'     => 'IN',
                                                'postcode'    => '456464',
                                                'email'       => 'abhijit.kumar018@webkul.in',
                                                'phone'       => '09999999999',
                                            ],
                                            [
                                                'id'          => 189,
                                                'addressType' => 'order_shipping',
                                                'firstName'   => 'webkul',
                                                'lastName'    => 'pvt ltd',
                                                'companyName' => 'C. Trades',
                                                'address'     => 'noida arv park 63',
                                                'city'        => 'Wyoming',
                                                'state'       => 'Uttar Pradesh',
                                                'country'     => 'IN',
                                                'postcode'    => '456464',
                                                'email'       => 'abhijit.kumar018@webkul.in',
                                                'phone'       => '09999999999',
                                            ],
                                        ],
                                    ],
                                    'items'                                 => [[
                                        'id'                      => 860,
                                        'orderItemId'             => 50,
                                        'sku'                     => 'Head13',
                                        'name'                    => 'Bagisto Cowboy Hat',
                                        'qty'                     => 1,
                                        'price'                   => 4000,
                                        'formattedPrice'          => '$4,000.00',
                                        'basePrice'               => 4000,
                                        'basePriceInclTax'        => 4000,
                                        'total'                   => 4000,
                                        'formattedTotal'          => '$4,000.00',
                                        'baseTotal'               => 4000,
                                        'baseTotalInclTax'        => 4000,
                                        'taxAmount'               => 0,
                                        'formattedTaxAmount'      => '$0.00',
                                        'discountAmount'          => 0,
                                        'formattedDiscountAmount' => '$0.00',
                                        'productId'               => 122,
                                        'productType'             => 'simple',
                                        'baseImageUrl'            => 'http://localhost:8000/storage/product/122/P9n1dbmgM4UOBT3zUAEGCn4wpKi0GjPGhgS1jZe7.webp',
                                        'additional'              => [
                                            'locale'                       => 'en',
                                            'quantity'                     => 1,
                                            'product_id'                   => '122',
                                            'super_attribute'              => [],
                                            'selected_configurable_option' => 122,
                                        ],
                                    ]],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/invoices/{id}/print',
            requirements: ['id' => '\\d+'],
            provider: AdminInvoicePrintProvider::class,
            outputFormats: ['pdf' => ['application/pdf']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Invoices'],
                summary: 'Download an invoice as PDF',
                description: 'Returns the invoice as a PDF file (`application/pdf` attachment) — the response is a binary download, not JSON. There is no example body; the browser/client downloads `invoice-{id}.pdf`.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Invoice ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The invoice PDF file is downloaded (application/pdf attachment).',
                        content: new \ArrayObject([
                            'application/pdf' => [
                                'schema' => ['type' => 'string', 'format' => 'binary'],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/invoices',
            input: AdminInvoiceCreateInput::class,
            output: AdminInvoiceRestDto::class,
            processor: AdminInvoiceCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Create an invoice for an order',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'items' => [
                                    ['orderItemId' => 42, 'quantity' => 3],
                                    ['orderItemId' => 43, 'quantity' => 1],
                                ],
                                'can_create_transaction' => true,
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Get(
            uriTemplate: '/invoices/export',
            provider: AdminInvoiceExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Invoices'],
                summary: 'Export invoices as CSV',
                description: 'Downloads the invoices datagrid as a CSV file (text/csv attachment) — the same data the admin Export button produces. Honours the same filters as the listing. Binary download, not JSON. Only ?format=csv is supported.',
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
    ],
    graphQlOperations: [
        new Query(
            provider: AdminInvoiceProvider::class,
            description: 'Get an invoice by id.',
        ),
        new QueryCollection(
            provider: AdminInvoiceCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin invoices datagrid listing (cursor pagination).',
            extraArgs: [
                'id'                    => ['type' => 'String'],
                'order_id'              => ['type' => 'String'],
                'state'                 => ['type' => 'String'],
                'base_grand_total_from' => ['type' => 'Float'],
                'base_grand_total_to'   => ['type' => 'Float'],
                'created_at_from'       => ['type' => 'String'],
                'created_at_to'         => ['type' => 'String'],
                'date_range'            => ['type' => 'String'],
                'sort'                  => ['type' => 'String'],
                'order'                 => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminInvoiceCreateInput::class,
            processor: AdminInvoiceCreateProcessor::class,
            description: 'Create an invoice on an order.',
        ),
    ],
)]
class AdminInvoice extends EloquentModel
{
    /** @var string */
    protected $table = 'invoices';

    /** @var array */
    protected $appends = [
        'order_increment_id', 'order_status', 'order_status_label', 'order_date',
        'channel_name', 'customer_name', 'customer_email', 'transaction_id',
        'formatted_sub_total', 'formatted_base_sub_total', 'formatted_sub_total_incl_tax', 'formatted_base_sub_total_incl_tax',
        'formatted_grand_total', 'formatted_base_grand_total', 'formatted_tax_amount', 'formatted_base_tax_amount',
        'formatted_discount_amount', 'formatted_base_discount_amount', 'formatted_shipping_amount', 'formatted_base_shipping_amount',
        'formatted_shipping_amount_incl_tax', 'formatted_base_shipping_amount_incl_tax', 'formatted_shipping_tax_amount', 'formatted_base_shipping_tax_amount',
    ];

    /** @var array */
    protected $casts = [
        'id'                            => 'int',
        'order_id'                      => 'int',
        'email_sent'                    => 'boolean',
        'total_qty'                     => 'int',
        'sub_total'                     => 'float',
        'base_sub_total'                => 'float',
        'sub_total_incl_tax'            => 'float',
        'base_sub_total_incl_tax'       => 'float',
        'grand_total'                   => 'float',
        'base_grand_total'              => 'float',
        'tax_amount'                    => 'float',
        'base_tax_amount'               => 'float',
        'discount_amount'               => 'float',
        'base_discount_amount'          => 'float',
        'shipping_amount'               => 'float',
        'base_shipping_amount'          => 'float',
        'shipping_amount_incl_tax'      => 'float',
        'base_shipping_amount_incl_tax' => 'float',
        'shipping_tax_amount'           => 'float',
        'base_shipping_tax_amount'      => 'float',
        'reminders'                     => 'int',
        'created_at'                    => 'datetime',
        'updated_at'                    => 'datetime',
    ];

    /** Status code -> label (mirrors core Order::$statusLabel). */
    private const STATUS_LABELS = [
        'pending'         => 'Pending',
        'pending_payment' => 'Pending Payment',
        'processing'      => 'Processing',
        'completed'       => 'Completed',
        'canceled'        => 'Canceled',
        'closed'          => 'Closed',
        'fraud'           => 'Fraud',
    ];

    private ?object $orderRowMemo = null;

    private bool $orderRowLoaded = false;

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    // --- Nested relations (GraphQL connections) ---

    #[ApiProperty(writable: false)]
    public function items(): HasMany
    {
        return $this->hasMany(AdminInvoiceItem::class, 'invoice_id');
    }

    /**
     * The invoice's order (typed object). Billing/shipping addresses live on the
     * order, so they're reached as `order { addresses { edges { node } } }` — a
     * top-level `addresses` HasMany on the invoice can't resolve over GraphQL
     * (API Platform's connection resolver ignores the custom local key, querying
     * addresses by the invoice id instead of its order_id). The BelongsTo uses
     * the standard key (invoice.order_id → orders.id) so AdminOrderDetail's own
     * `addresses` connection resolves correctly.
     */
    #[ApiProperty(writable: false)]
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OrderDetail::class, 'order_id');
    }

    // --- Order / customer context (from the order; listing pre-sets the raw attributes) ---

    #[ApiProperty(writable: false)]
    public function getOrderIncrementIdAttribute(): ?string
    {
        return $this->attributes['order_increment_id'] ?? $this->orderRow()?->increment_id;
    }

    #[ApiProperty(writable: false)]
    public function getOrderStatusAttribute(): ?string
    {
        return $this->attributes['order_status'] ?? $this->orderRow()?->status;
    }

    #[ApiProperty(writable: false)]
    public function getOrderStatusLabelAttribute(): ?string
    {
        $status = $this->getOrderStatusAttribute();

        return $status ? (self::STATUS_LABELS[$status] ?? $status) : null;
    }

    #[ApiProperty(writable: false)]
    public function getOrderDateAttribute(): ?string
    {
        $value = $this->attributes['order_date'] ?? $this->orderRow()?->created_at;

        return $value ? (string) $value : null;
    }

    #[ApiProperty(writable: false)]
    public function getChannelNameAttribute(): ?string
    {
        return $this->attributes['channel_name'] ?? $this->orderRow()?->channel_name;
    }

    #[ApiProperty(writable: false)]
    public function getCustomerNameAttribute(): ?string
    {
        if (isset($this->attributes['customer_name'])) {
            return $this->attributes['customer_name'];
        }

        $order = $this->orderRow();

        if (! $order) {
            return null;
        }

        return trim(($order->customer_first_name ?? '').' '.($order->customer_last_name ?? '')) ?: null;
    }

    #[ApiProperty(writable: false)]
    public function getCustomerEmailAttribute(): ?string
    {
        return $this->attributes['customer_email'] ?? $this->orderRow()?->customer_email;
    }

    /**
     * `invoices.transaction_id` is only set for gateway captures; the "Create
     * Transaction" path records it in order_transactions (linked by invoice_id).
     */
    #[ApiProperty(writable: false)]
    public function getTransactionIdAttribute(): ?string
    {
        if (! empty($this->attributes['transaction_id'])) {
            return (string) $this->attributes['transaction_id'];
        }

        $linked = DB::table('order_transactions')->where('invoice_id', $this->id)->value('transaction_id');

        return $linked !== null ? (string) $linked : null;
    }

    // --- Formatted money (order currency; base_* use base currency) ---

    #[ApiProperty(writable: false)]
    public function getFormattedSubTotalAttribute(): ?string
    {
        return $this->fmt($this->sub_total);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseSubTotalAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_sub_total);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedSubTotalInclTaxAttribute(): ?string
    {
        return $this->fmt($this->sub_total_incl_tax);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseSubTotalInclTaxAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_sub_total_incl_tax);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalAttribute(): ?string
    {
        return $this->fmt($this->grand_total);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseGrandTotalAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_grand_total);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedTaxAmountAttribute(): ?string
    {
        return $this->fmt($this->tax_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseTaxAmountAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_tax_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedDiscountAmountAttribute(): ?string
    {
        return $this->fmt($this->discount_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseDiscountAmountAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_discount_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedShippingAmountAttribute(): ?string
    {
        return $this->fmt($this->shipping_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseShippingAmountAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_shipping_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedShippingAmountInclTaxAttribute(): ?string
    {
        return $this->fmt($this->shipping_amount_incl_tax);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseShippingAmountInclTaxAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_shipping_amount_incl_tax);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedShippingTaxAmountAttribute(): ?string
    {
        return $this->fmt($this->shipping_tax_amount);
    }

    #[ApiProperty(writable: false)]
    public function getFormattedBaseShippingTaxAmountAttribute(): ?string
    {
        return core()->formatBasePrice((float) $this->base_shipping_tax_amount);
    }

    private function fmt($amount): string
    {
        return core()->formatPrice((float) $amount, $this->order_currency_code);
    }

    /** Memoised lookup of the invoice's order row (order-context fields). */
    private function orderRow(): ?object
    {
        if (! $this->orderRowLoaded) {
            $this->orderRowLoaded = true;
            $orderId = $this->attributes['order_id'] ?? null;
            $this->orderRowMemo = $orderId
                ? DB::table('orders')->where('id', $orderId)->first([
                    'id', 'increment_id', 'status', 'channel_name',
                    'customer_first_name', 'customer_last_name', 'customer_email', 'created_at',
                ])
                : null;
        }

        return $this->orderRowMemo;
    }
}
