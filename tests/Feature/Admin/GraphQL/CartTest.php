<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Checkout\Facades\Cart as CartFacade;

/**
 * GraphQL coverage for the Admin draft-cart endpoints (Wave 2).
 *
 * Operations:
 *   - adminCart(id: ID!)                            query
 *   - createAdminCartAddItem(input)                 mutation
 *   - createAdminCartUpdateItems(input)             mutation
 *   - createAdminCartRemoveItem(input)              mutation
 *   - createAdminCartSaveAddress(input)             mutation
 *   - createAdminCartApplyCoupon(input)             mutation
 *   - createAdminCartRemoveCoupon(input)            mutation
 *
 * NOTE: API Platform GraphQL returns the resource IRI for `id` and the raw
 * integer for `_id`. Project-wide, scalar camelCase fields (customerId,
 * isActive, grandTotal, ...) currently serialise to `null` over GraphQL — the
 * existing AdminOrderDetail GraphQL tests follow the same convention. REST
 * responses include those fields fully populated.
 */
class CartTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Bootstrap a draft cart with one item. Always returns a cart id. */
    protected function bootstrapDraftCart(): int
    {
        return $this->bootstrapAdminDraftCart();
    }

    public function test_query_requires_authentication(): void
    {
        $resp = $this->adminGraphQL('query { adminCart(id: "/api/admin/carts/1") { id } }');
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_query_returns_cart(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $query = <<<'GQL'
            query AdminCart($id: ID!) {
              adminCart(id: $id) { id _id }
            }
        GQL;

        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/carts/'.$cartId], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();

        $data = $resp->json('data.adminCart');
        expect($data)->not->toBeNull();
        expect((string) $data['id'])->toContain((string) $cartId);
        expect($data['_id'])->toBe($cartId);
    }

    public function test_query_returns_error_for_unknown_cart(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL('query { adminCart(id: "/api/admin/carts/999999999") { id } }', [], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_add_item_mutation_requires_product(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation AddItem($input: addItemAdminCartInput!) {
              addItemAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId],
        ], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_add_item_blocks_booking_products(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $booking = \Webkul\Product\Models\Product::query()->where('type', 'booking')->first();

        if (! $booking) {
            $this->markTestSkipped('No booking product fixture in test DB.');
        }

        $mutation = <<<'GQL'
            mutation AddItem($input: addItemAdminCartInput!) {
              addItemAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'id'        => '/api/admin/carts/'.$cartId,
                'cartId'    => (string) $cartId,
                'productId' => $booking->id,
                'quantity'  => 1,
            ],
        ], $admin);

        $errors = $resp->json('errors');
        expect($errors)->not->toBeNull();
        $messages = collect($errors)->pluck('message')->implode(' ');
        expect($messages)->toContain('Booking');
    }

    public function test_remove_coupon_mutation(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation RemoveCoupon($input: removeCouponAdminCartInput!) {
              removeCouponAdminCart(input: $input) { adminCart { id _id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId],
        ], $admin);
        $resp->assertOk();
        $cart = $this->adminGet($admin, '/api/admin/carts/'.$cartId)->json();
        expect($cart['id'])->toBe($cartId);
    }

    public function test_apply_unknown_coupon_returns_error(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation ApplyCoupon($input: applyCouponAdminCartInput!) {
              applyCouponAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId, 'code' => 'NO_SUCH_'.uniqid()],
        ], $admin);

        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_query_unknown_cart_id_zero_returns_error(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL('query { adminCart(id: "/api/admin/carts/0") { id } }', [], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_update_items_mutation_empty_qty_errors(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation Upd($input: updateItemsAdminCartInput!) {
              updateItemsAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId],
        ], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_remove_item_mutation_missing_cart_item_id_errors(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation Rm($input: removeItemAdminCartInput!) {
              removeItemAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId],
        ], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_save_address_mutation_missing_billing_errors(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation Sa($input: saveAddressAdminCartInput!) {
              saveAddressAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId],
        ], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_apply_coupon_empty_code_errors(): void
    {
        $admin = $this->createAdmin();
        $cartId = $this->bootstrapDraftCart();

        $mutation = <<<'GQL'
            mutation Apply($input: applyCouponAdminCartInput!) {
              applyCouponAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cartId, 'cartId' => (string) $cartId, 'code' => ''],
        ], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_query_requires_auth_strictly(): void
    {
        $resp = $this->adminGraphQL('query { adminCart(id: "/api/admin/carts/999999999") { id } }');
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_add_item_mutation_unauth_errors(): void
    {
        $mutation = <<<'GQL'
            mutation AddItem($input: addItemAdminCartInput!) {
              addItemAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/1', 'cartId' => '1', 'productId' => 1],
        ]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_apply_unknown_coupon_to_empty_cart_errors(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->findOrCreateCustomer();

        $cart = CartFacade::createCart(['customer' => $customer, 'is_active' => false]);

        $mutation = <<<'GQL'
            mutation Apply($input: applyCouponAdminCartInput!) {
              applyCouponAdminCart(input: $input) { adminCart { id } }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/carts/'.$cart->id, 'cartId' => (string) $cart->id, 'code' => 'NO_SUCH_'.uniqid()],
        ], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }
}
