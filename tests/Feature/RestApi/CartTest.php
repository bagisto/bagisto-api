<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\Product;

class CartTest extends RestApiTestCase
{
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    private string $addProductUrl = '/api/shop/add-product-in-cart';

    private string $applyCouponUrl = '/api/shop/apply-coupon';

    private string $removeCouponUrl = '/api/shop/remove-coupon';

    private string $removeItemUrl = '/api/shop/remove-cart-item';

    private string $removeItemsUrl = '/api/shop/remove-cart-items';

    private string $updateItemUrl = '/api/shop/update-cart-item';

    // ── Helpers ───────────────────────────────────────────────

    private function postWithToken(string $url, string $token, array $payload = []): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    private function getWithToken(string $url, string $token): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->getJson($url);
    }

    private function createGuestCartToken(): string
    {
        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $token = $response->json('cartToken') ?? $response->json('sessionToken');
        $this->assertNotEmpty($token, 'Guest cart token missing from response.');

        return (string) $token;
    }

    private function createSimpleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'CART-TEST-'.uniqid(),
        ]);
        $this->upsertProductAttributeValue($product->id, 'price', 10.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'weight', 1.0, null, 'default');
        $this->ensureInventory($product, 50);

        return $product;
    }

    /**
     * Creates a guest cart with one product and returns [token, cartItemId].
     */
    private function createGuestCartWithProduct(): array
    {
        $product = $this->createSimpleProduct();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product->id,
            'quantity'  => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $items = $response->json('items') ?? [];
        $cartItemId = (int) ($items[0]['id'] ?? 0);

        $this->assertGreaterThan(0, $cartItemId, 'Cart item ID missing from response.');

        return ['token' => $token, 'cartItemId' => $cartItemId];
    }

    // ── Cart Token: POST (create) ─────────────────────────────

    public function test_create_guest_cart_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('cartToken') ?? $response->json('sessionToken'))->toBeString()->not()->toBeEmpty();
    }

    public function test_guest_cart_token_response_has_expected_fields(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $data = $response->json();
        expect($data)->toHaveKey('success');
        expect($data)->toHaveKey('isGuest');
        expect($response->json('isGuest'))->toBeTrue();
    }

    public function test_create_customer_cart_returns_cart_data(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->cartTokensUrl, []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
    }

    // ── Add Product in Cart ───────────────────────────────────

    public function test_add_simple_product_to_cart_as_guest(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product->id,
            'quantity'  => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    public function test_add_product_to_cart_requires_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();

        $response = $this->publicPost($this->addProductUrl, [
            'productId' => $product->id,
            'quantity'  => 1,
        ]);

        // AuthenticationException → 500 in REST (no HttpExceptionInterface)
        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_add_product_without_product_id_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_add_product_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => 999999,
            'quantity'  => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    public function test_add_product_to_customer_cart(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity'  => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
    }

    // ── Apply Coupon ──────────────────────────────────────────

    public function test_apply_coupon_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->applyCouponUrl, [
            'couponCode' => 'TESTCODE',
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_apply_coupon_without_coupon_code_returns_error(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->applyCouponUrl, $cart['token'], []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_apply_invalid_coupon_code_returns_error_or_failure(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->applyCouponUrl, $cart['token'], [
            'couponCode' => 'INVALID_COUPON_'.uniqid(),
        ]);

        // Either HTTP error OR 201 with success=false
        if ($response->getStatusCode() === 201) {
            expect((bool) $response->json('success'))->toBeFalse();
        } else {
            expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
        }
    }

    // ── Remove Coupon ─────────────────────────────────────────

    public function test_remove_coupon_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->removeCouponUrl);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_remove_coupon_from_cart_returns_success(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->removeCouponUrl, $cart['token']);

        expect($response->getStatusCode())->toBeIn([200, 201]);
    }

    public function test_remove_coupon_response_has_cart_data(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->removeCouponUrl, $cart['token']);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $data = $response->json();
        expect($data)->toBeArray();
        expect($data)->toHaveKey('itemsCount');
    }

    // ── Remove Cart Item ──────────────────────────────────────

    public function test_remove_cart_item(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->removeItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('itemsCount'))->toBe(0);
    }

    public function test_remove_cart_item_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->removeItemUrl, [
            'cartItemId' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_remove_cart_item_without_cart_item_id_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->removeItemUrl, $token, []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_remove_nonexistent_cart_item_returns_error(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->removeItemUrl, $cart['token'], [
            'cartItemId' => 999999,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    // ── Remove Cart Items (multiple) ──────────────────────────

    public function test_remove_multiple_cart_items(): void
    {
        $this->seedRequiredData();
        $product1 = $this->createSimpleProduct();
        $product2 = $this->createSimpleProduct();
        $token = $this->createGuestCartToken();

        $resp1 = $this->postWithToken($this->addProductUrl, $token, ['productId' => $product1->id, 'quantity' => 1]);
        $resp2 = $this->postWithToken($this->addProductUrl, $token, ['productId' => $product2->id, 'quantity' => 1]);

        $id1 = (int) ($resp1->json('items.0.id') ?? 0);
        $id2 = (int) ($resp2->json('items.1.id') ?? $resp2->json('items.0.id') ?? 0);

        $response = $this->postWithToken($this->removeItemsUrl, $token, [
            'itemIds' => array_filter([$id1, $id2]),
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('itemsCount'))->toBe(0);
    }

    public function test_remove_cart_items_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->removeItemsUrl, [
            'itemIds' => [1, 2],
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_remove_cart_items_without_item_ids_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->removeItemsUrl, $token, []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    // ── Update Cart Item ──────────────────────────────────────

    public function test_update_cart_item_quantity(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->updateItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
            'quantity'   => 3,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
    }

    public function test_update_cart_item_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->updateItemUrl, [
            'cartItemId' => 1,
            'quantity'   => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_update_cart_item_without_cart_item_id_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->updateItemUrl, $token, [
            'quantity' => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_update_cart_item_with_invalid_quantity_returns_error(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->updateItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
            'quantity'   => 0,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_update_cart_item_response_has_cart_fields(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->updateItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
            'quantity'   => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $data = $response->json();
        expect($data)->toHaveKey('itemsCount');
        expect($data)->toHaveKey('grandTotal');
        expect($data)->toHaveKey('subtotal');
    }

    // ── Get Cart Details (POST /api/shop/cart) ────────────────────────────

    public function test_read_cart_returns_customer_cart_details(): void
    {
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createSimpleProduct();

        $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity'  => 2,
        ])->assertSuccessful();

        $response = $this->authenticatedPost($customer, '/api/shop/cart', []);

        $response->assertSuccessful();

        $data = $response->json();
        expect($data)->toBeArray();
        expect($data['customerId'])->toBe($customer->id);
        expect((int) $data['itemsCount'])->toBeGreaterThan(0);
        expect($data)->toHaveKey('grandTotal');
        expect($data)->toHaveKey('subtotal');
        expect($data['items'])->toBeArray();
        expect($data['items'][0]['productId'])->toBe($product->id);
        expect((int) $data['items'][0]['quantity'])->toBe(2);
    }

    public function test_read_cart_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/cart', []);

        expect(in_array($response->getStatusCode(), [401, 403, 404, 500]))->toBeTrue();
    }
}
