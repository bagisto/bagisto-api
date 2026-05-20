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
use Webkul\BagistoApi\Admin\Dto\AdminCartUpdateItemsInput;
use Webkul\BagistoApi\Admin\State\AdminCartAddItemProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartApplyCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartProvider;
use Webkul\BagistoApi\Admin\State\AdminCartRemoveCouponProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartRemoveItemProcessor;
use Webkul\BagistoApi\Admin\State\AdminCartSaveAddressProcessor;
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
                tags: ['Admin Carts'],
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
                tags: ['Admin Carts'],
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
                tags: ['Admin Carts'],
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
                tags: ['Admin Carts'],
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
                tags: ['Admin Carts'],
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
                tags: ['Admin Carts'],
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
                tags: ['Admin Carts'],
                summary: 'Remove the applied coupon',
                description: 'Removes the currently applied coupon (if any) from the draft cart and recollects totals.',
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
    ],
)]
class AdminCart
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    public ?int $customerId = null;

    public ?bool $isGuest = null;

    public ?bool $isActive = null;

    public ?int $itemsCount = null;

    public ?int $itemsQty = null;

    public ?float $subTotal = null;

    public ?string $formattedSubTotal = null;

    public ?float $grandTotal = null;

    public ?string $formattedGrandTotal = null;

    public ?float $shippingAmount = null;

    public ?string $formattedShippingAmount = null;

    public ?float $taxTotal = null;

    public ?string $formattedTaxTotal = null;

    public ?float $discountAmount = null;

    public ?string $formattedDiscountAmount = null;

    public ?string $couponCode = null;

    public ?string $shippingMethod = null;

    public ?string $paymentMethod = null;

    public ?string $paymentMethodTitle = null;

    public ?bool $haveStockableItems = null;

    /** @var array<int, array> */
    public array $items = [];

    public ?array $billingAddress = null;

    public ?array $shippingAddress = null;

    public ?bool $success = null;

    public ?string $message = null;
}
