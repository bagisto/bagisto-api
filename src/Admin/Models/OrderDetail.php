<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\OrderDetailAddress;
use Webkul\BagistoApi\Admin\Dto\OrderDetailCustomer;
use Webkul\BagistoApi\Admin\Dto\OrderDetailInvoice;
use Webkul\BagistoApi\Admin\Dto\OrderDetailItem;
use Webkul\BagistoApi\Admin\Dto\OrderDetailShipment;
use Webkul\BagistoApi\Admin\State\OrderDetailProvider;

/**
 * Admin Order detail — the full order-view payload.
 *
 * REST  : GET /api/admin/orders/{id}
 * GraphQL: adminOrderDetail(id: ...) query
 *
 * Everything the order-view screen needs is embedded inline (customer,
 * addresses, items with type-specific data, invoices, shipments) — measured
 * at ~20ms fully eager-loaded, so no sub-resource round trips are needed.
 *
 * All property names are camelCase (consistent with the nested plain DTOs,
 * independent of the API Platform name converter).
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminOrderDetail',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/orders/{id}',
            provider: OrderDetailProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Orders'],
                summary: 'Get order detail',
                description: 'Full order-view payload — flat order fields plus embedded customer, billing/shipping addresses, items (with product-type-specific data), invoices, and shipments.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The full order detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'                  => 2392,
                                    'incrementId'         => '2392',
                                    'status'              => 'processing',
                                    'statusLabel'         => 'Processing',
                                    'channelName'         => 'bagisto store',
                                    'isGuest'             => false,
                                    'isGift'              => false,
                                    'customerEmail'       => 'admin@example.com',
                                    'customerFirstName'   => 'Test',
                                    'customerLastName'    => 'User',
                                    'shippingMethod'      => 'free_free',
                                    'shippingTitle'       => 'Free Shipping - Free Shipping',
                                    'paymentTitle'        => 'Money Transfer',
                                    'couponCode'          => null,
                                    'totalItemCount'      => 1,
                                    'totalQtyOrdered'     => 1,
                                    'orderCurrencyCode'   => 'USD',
                                    'grandTotal'          => 4000,
                                    'baseGrandTotal'      => 4000,
                                    'formattedGrandTotal' => '$4,000.00',
                                    'subTotal'            => 4000,
                                    'formattedSubTotal'   => '$4,000.00',
                                    'createdAt'           => '2026-05-19 13:13:29',
                                    'customer'            => [
                                        'id'        => 19,
                                        'email'     => 'admin@example.com',
                                        'name'      => 'Test User',
                                        'firstName' => 'Test',
                                        'lastName'  => 'User',
                                        'phone'     => '145234234',
                                        'status'    => 1,
                                        'group'     => ['id' => 2, 'code' => 'general', 'name' => 'General'],
                                    ],
                                    'billingAddress' => [
                                        'id'        => 4943, 'addressType' => 'order_billing',
                                        'firstName' => 'John', 'lastName' => 'Doe',
                                        'address'   => '123 Main St', 'city' => 'New York',
                                        'state'     => 'NY', 'country' => 'US', 'postcode' => '10001',
                                        'phone'     => '1234567890',
                                    ],
                                    'shippingAddress' => [
                                        'id'        => 4942, 'addressType' => 'order_shipping',
                                        'firstName' => 'John', 'lastName' => 'Doe',
                                        'address'   => '123 Main St', 'city' => 'New York',
                                        'state'     => 'NY', 'country' => 'US', 'postcode' => '10001',
                                        'phone'     => '1234567890',
                                    ],
                                    'items' => [
                                        [
                                            'id'                => 2694,
                                            'sku'               => 'test65',
                                            'type'              => 'simple',
                                            'name'              => 'Classic Watch Hand',
                                            'productId'         => 2358,
                                            'qtyOrdered'        => 1,
                                            'qtyInvoiced'       => 1,
                                            'price'             => 4000,
                                            'formattedPrice'    => '$4,000.00',
                                            'total'             => 4000,
                                            'formattedTotal'    => '$4,000.00',
                                            'taxAmount'         => 0,
                                            'discountAmount'    => 0,
                                            'additional'        => ['quantity' => 1],
                                            'child'             => null,
                                            'children'          => [],
                                            'downloadableLinks' => [],
                                        ],
                                    ],
                                    'invoices'  => [],
                                    'shipments' => [],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Order not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: OrderDetailProvider::class,
            description: 'Full detail of a single order by ID. Nested collections (items, invoices, shipments) are returned as GraphQL connections.',
        ),
    ]
)]
class OrderDetail
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $incrementId = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $statusLabel = null;

    #[ApiProperty(writable: false)]
    public ?string $channelName = null;

    #[ApiProperty(writable: false)]
    public ?bool $isGuest = null;

    #[ApiProperty(writable: false)]
    public ?bool $isGift = null;

    #[ApiProperty(writable: false)]
    public ?string $customerEmail = null;

    #[ApiProperty(writable: false)]
    public ?string $customerFirstName = null;

    #[ApiProperty(writable: false)]
    public ?string $customerLastName = null;

    #[ApiProperty(writable: false)]
    public ?string $shippingMethod = null;

    #[ApiProperty(writable: false)]
    public ?string $shippingTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $shippingDescription = null;

    #[ApiProperty(writable: false)]
    public ?string $paymentTitle = null;

    #[ApiProperty(writable: false)]
    public ?string $couponCode = null;

    #[ApiProperty(writable: false)]
    public ?int $totalItemCount = null;

    #[ApiProperty(writable: false)]
    public ?int $totalQtyOrdered = null;

    #[ApiProperty(writable: false)]
    public ?string $baseCurrencyCode = null;

    #[ApiProperty(writable: false)]
    public ?string $channelCurrencyCode = null;

    #[ApiProperty(writable: false)]
    public ?string $orderCurrencyCode = null;

    #[ApiProperty(writable: false)]
    public ?float $grandTotal = null;

    #[ApiProperty(writable: false)]
    public ?float $baseGrandTotal = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedGrandTotal = null;

    #[ApiProperty(writable: false)]
    public ?float $grandTotalInvoiced = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedGrandTotalInvoiced = null;

    #[ApiProperty(writable: false)]
    public ?float $grandTotalRefunded = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedGrandTotalRefunded = null;

    #[ApiProperty(writable: false)]
    public ?float $subTotal = null;

    #[ApiProperty(writable: false)]
    public ?float $baseSubTotal = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedSubTotal = null;

    #[ApiProperty(writable: false)]
    public ?float $taxAmount = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedTaxAmount = null;

    #[ApiProperty(writable: false)]
    public ?float $discountAmount = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedDiscountAmount = null;

    #[ApiProperty(writable: false)]
    public ?float $shippingAmount = null;

    #[ApiProperty(writable: false)]
    public ?string $formattedShippingAmount = null;

    #[ApiProperty(writable: false)]
    public ?string $createdAt = null;

    #[ApiProperty(writable: false)]
    public ?string $updatedAt = null;

    #[ApiProperty(writable: false)]
    public ?OrderDetailCustomer $customer = null;

    #[ApiProperty(writable: false)]
    public ?OrderDetailAddress $billingAddress = null;

    #[ApiProperty(writable: false)]
    public ?OrderDetailAddress $shippingAddress = null;

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $items = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $invoices = [];

    /** @var array<int, array<string, mixed>> */
    #[ApiProperty(writable: false)]
    public array $shipments = [];
}
