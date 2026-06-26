<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminCartAddItemInput;
use Webkul\BagistoApi\Admin\Dto\AdminCartCouponInput;
use Webkul\BagistoApi\Admin\Dto\AdminCartRemoveItemInput;
use Webkul\BagistoApi\Admin\Dto\AdminCartSaveAddressInput;
use Webkul\BagistoApi\Admin\Dto\AdminCartSetPaymentMethodInput;
use Webkul\BagistoApi\Admin\Dto\AdminCartSetShippingMethodInput;
use Webkul\BagistoApi\Admin\Dto\AdminCartUpdateItemsInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminCartAddItemProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartApplyCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartProvider;
use Webkul\BagistoApi\Admin\State\AdminCartRemoveCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartRemoveItemProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSaveAddressProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSetPaymentMethodProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSetShippingMethodProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartUpdateItemsProcessor;

/**
 * Admin draft cart — the cart `AdminReorder` (and future Create-Order flows)
 * build for the admin to finalise on the customer's behalf.
 *
 * REST  : GET /api/admin/carts/{id}
 *         POST /api/admin/carts/{id}/items                 (add product, any type)
 *         PUT  /api/admin/carts/{id}/items                 (bulk-update qty)
 *         DELETE /api/admin/carts/{id}/items               (remove one — cartItemId in body)
 *         POST /api/admin/carts/{id}/addresses             (billing + shipping)
 *         POST /api/admin/carts/{id}/coupon                (apply coupon)
 *         DELETE /api/admin/carts/{id}/coupon              (remove applied coupon)
 *
 * GraphQL: adminCart query + createAdminCart* mutations.
 *
 * Every write op returns the AdminCart so the client never needs a follow-up
 * read. Only draft carts (`is_active = 0`) can be mutated — customer-owned
 * active carts are rejected by `AdminCartGuard`.
 *
 * Mirrors the monolith `Webkul\Admin\Http\Controllers\Sales\CartController`.
 * Place-order, shipping-method and payment-method actions are deferred to a
 * later wave.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminCart',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/carts/{id}',
            provider: AdminCartProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Get a draft cart',
                description: 'Returns the admin draft cart with items, totals, addresses, and selected shipping / payment.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Cart ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/carts/{id}/items',
            input: AdminCartAddItemInput::class,
            processor: AdminCartAddItemProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Add a product to the draft cart',
                description: 'Adds the product to the cart using the shared `Cart::addProduct` flow — supports every product type. Body keys mirror the storefront add-to-cart payload (`productId`, `quantity`, plus type-specific `selectedConfigurableOption`, `superAttribute`, `bundleOptions`, `links`, `qty[]`, etc).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'productId' => 142,
                                'quantity'  => 1,
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Put(
            uriTemplate: '/carts/{id}/items',
            input: AdminCartUpdateItemsInput::class,
            provider: AdminCartProvider::class,
            processor: AdminCartUpdateItemsProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Update cart-item quantities',
                description: 'Bulk-update line-item quantities. `qty` is a map of cart_item_id → new quantity.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                'qty' => ['12' => 3, '13' => 1],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Delete(
            uriTemplate: '/carts/{id}/items',
            status: 200,
            input: AdminCartRemoveItemInput::class,
            provider: AdminCartProvider::class,
            processor: AdminCartRemoveItemProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Remove a single cart item',
                description: 'Removes the cart item identified by `cartItemId` from the draft cart and recollects totals.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['cartItemId' => 41],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            uriTemplate: '/carts/{id}/addresses',
            input: AdminCartSaveAddressInput::class,
            processor: AdminCartSaveAddressProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Save billing & shipping addresses',
                description: 'Saves the billing (and shipping unless `billing.useForShipping` is true) addresses for the draft cart and recollects totals.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                'billing' => [
                                    'firstName'      => 'Jane', 'lastName' => 'Doe',
                                    'email'          => 'jane@example.com',
                                    'address'        => ['12 Main St'],
                                    'city'           => 'Berlin', 'country' => 'DE', 'state' => 'BE',
                                    'postcode'       => '10115', 'phone' => '+4930123456',
                                    'useForShipping' => true,
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            uriTemplate: '/carts/{id}/coupon',
            input: AdminCartCouponInput::class,
            processor: AdminCartApplyCouponProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Apply a coupon code',
                description: 'Applies a coupon code to the draft cart. Returns 404 if the coupon is unknown / inactive; 422 if the same coupon is already applied; 200 on success.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['code' => 'WELCOME10'],
                        ],
                    ]),
                ),
            ),
        ),
        new Delete(
            uriTemplate: '/carts/{id}/coupon',
            status: 200,
            input: false,
            provider: AdminCartProvider::class,
            processor: AdminCartRemoveCouponProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Remove the applied coupon',
                description: 'Removes the currently applied coupon (if any) from the draft cart and recollects totals.',
            ),
        ),
        new Post(
            uriTemplate: '/carts/{id}/shipping-methods',
            input: AdminCartSetShippingMethodInput::class,
            processor: AdminCartSetShippingMethodProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Select a shipping method for the draft cart',
                description: 'Saves the selected shipping method on the cart and recollects totals. Requires both billing AND shipping addresses to already be saved (409 if missing).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['shippingMethod' => 'flatrate_flatrate'],
                        ],
                    ]),
                ),
            ),
        ),
        new Post(
            uriTemplate: '/carts/{id}/payment-methods',
            input: AdminCartSetPaymentMethodInput::class,
            processor: AdminCartSetPaymentMethodProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Select a payment method for the draft cart',
                description: 'Saves the selected payment method on the cart and recollects totals. Requires a shipping method to already be selected (409 if missing).',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => ['method' => 'cashondelivery'],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminCartProvider::class,
            description: 'Get a draft cart by ID. Use `adminCart(id: ...)` where `id` is the resource IRI `/api/admin/carts/{id}`.',
        ),
        new Mutation(
            name: 'addItem',
            input: AdminCartAddItemInput::class,
            output: self::class,
            processor: AdminCartAddItemProcessor::class,
            description: 'Add a product to a draft cart. `cartId` is the draft cart id; other keys mirror the storefront add-to-cart shape.',
        ),
        new Mutation(
            name: 'updateItems',
            input: AdminCartUpdateItemsInput::class,
            output: self::class,
            processor: AdminCartUpdateItemsProcessor::class,
            description: 'Bulk-update quantities on a draft cart.',
        ),
        new Mutation(
            name: 'removeItem',
            input: AdminCartRemoveItemInput::class,
            output: self::class,
            processor: AdminCartRemoveItemProcessor::class,
            description: 'Remove a single line item from the draft cart.',
        ),
        new Mutation(
            name: 'saveAddress',
            input: AdminCartSaveAddressInput::class,
            output: self::class,
            processor: AdminCartSaveAddressProcessor::class,
            description: 'Save billing and shipping addresses on the draft cart.',
        ),
        new Mutation(
            name: 'applyCoupon',
            input: AdminCartCouponInput::class,
            output: self::class,
            processor: AdminCartApplyCouponProcessor::class,
            description: 'Apply a coupon code to the draft cart.',
        ),
        new Mutation(
            name: 'removeCoupon',
            input: AdminCartCouponInput::class,
            output: self::class,
            processor: AdminCartRemoveCouponProcessor::class,
            description: 'Remove the applied coupon from the draft cart.',
        ),
        new Mutation(
            name: 'setShippingMethod',
            input: AdminCartSetShippingMethodInput::class,
            output: self::class,
            processor: AdminCartSetShippingMethodProcessor::class,
            description: 'Save the selected shipping method on the draft cart. Both addresses must be saved first (409 otherwise).',
        ),
        new Mutation(
            name: 'setPaymentMethod',
            input: AdminCartSetPaymentMethodInput::class,
            output: self::class,
            processor: AdminCartSetPaymentMethodProcessor::class,
            description: 'Save the selected payment method on the draft cart. Shipping method must be selected first (409 otherwise).',
        ),
    ],
)]
class AdminCart
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    public ?int $customer_id = null;

    public ?bool $is_guest = null;

    public ?bool $is_active = null;

    public ?int $items_count = null;

    public ?int $items_qty = null;

    public ?float $sub_total = null;

    public ?string $formatted_sub_total = null;

    public ?float $grand_total = null;

    public ?string $formatted_grand_total = null;

    public ?float $shipping_amount = null;

    public ?string $formatted_shipping_amount = null;

    public ?float $tax_total = null;

    public ?string $formatted_tax_total = null;

    public ?float $discount_amount = null;

    public ?string $formatted_discount_amount = null;

    public ?string $coupon_code = null;

    public ?string $shipping_method = null;

    public ?string $payment_method = null;

    public ?string $payment_method_title = null;

    public ?bool $have_stockable_items = null;

    /** @var array<int, array> */
    public array $items = [];

    public ?array $billing_address = null;

    public ?array $shipping_address = null;

    public ?bool $success = null;

    public ?string $message = null;
}
