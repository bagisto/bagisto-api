<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

class MergeCartTest extends RestApiTestCase
{
    /**
     * The cart tokens url.
     */
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    /**
     * The add product url.
     */
    private string $addProductUrl = '/api/shop/add-product-in-cart';

    /**
     * The merge cart url.
     */
    private string $mergeCartUrl = '/api/shop/merge-carts';

    // ── Tests ─────────────────────────────────────────────────

    /**
     * Test merge guest cart into empty customer cart.
     */
    public function test_merge_guest_cart_into_empty_customer_cart(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $guest = $this->createGuestCartWithProduct($product, 2);

        $customer = $this->authedCustomer();

        $response = $this->authenticatedPost($customer, $this->mergeCartUrl, [
            'cart_id' => $guest['cartId'],
            'guest_cart_token' => $guest['token'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThanOrEqual(1);
        expect($response->json('isGuest'))->toBeFalse();
    }

    /**
     * Test merge combines quantities for same product.
     */
    public function test_merge_combines_quantities_for_same_product(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $customer = $this->authedCustomer();

        $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $guest = $this->createGuestCartWithProduct($product, 3);

        $response = $this->authenticatedPost($customer, $this->mergeCartUrl, [
            'cart_id' => $guest['cartId'],
            'guest_cart_token' => $guest['token'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();

        $items = $response->json('items') ?? [];
        $matching = collect($items)->firstWhere('productId', $product->id);
        $this->assertNotNull($matching, 'Product should exist in merged cart');
        expect((int) $matching['quantity'])->toBe(4);
    }

    /**
     * Test merge requires authentication.
     */
    public function test_merge_requires_authentication(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $guest = $this->createGuestCartWithProduct($product, 1);

        $response = $this->publicPost($this->mergeCartUrl, [
            'cart_id' => $guest['cartId'],
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test merge with invalid cart id returns error.
     */
    public function test_merge_with_invalid_cart_id_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->authedCustomer();

        $response = $this->authenticatedPost($customer, $this->mergeCartUrl, [
            'cart_id' => 999999,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Test merge without guest cart token returns not found.
     */
    public function test_merge_without_guest_cart_token_returns_not_found(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $guest = $this->createGuestCartWithProduct($product, 1);

        $customer = $this->authedCustomer();

        $response = $this->authenticatedPost($customer, $this->mergeCartUrl, [
            'cart_id' => $guest['cartId'],
        ]);

        expect($response->getStatusCode())->toBeIn([400, 403, 404]);

        $row = DB::table('cart')->where('id', $guest['cartId'])->first();
        expect((int) $row->is_active)->toBe(1);
        expect($row->customer_id)->toBeNull();
    }

    /**
     * Test guest cart is not left active after merge.
     */
    public function test_guest_cart_is_not_left_active_after_merge(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $guest = $this->createGuestCartWithProduct($product, 1);

        $customer = $this->authedCustomer();

        $response = $this->authenticatedPost($customer, $this->mergeCartUrl, [
            'cart_id' => $guest['cartId'],
            'guest_cart_token' => $guest['token'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $row = DB::table('cart')->where('id', $guest['cartId'])->first();
        $this->assertTrue(
            $row === null || (int) $row->customer_id === (int) $customer->id,
            'Guest cart must be removed or handed over to the customer after merge'
        );
    }

    /**
     * Post with token.
     */
    private function postWithToken(string $url, string $token, array $payload = []): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    /**
     * Create simple product.
     */
    private function createSimpleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'MERGE-TEST-'.uniqid(),
        ]);
        $this->upsertProductAttributeValue($product->id, 'price', 10.0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'weight', 1.0, null, 'default');
        $this->ensureInventory($product, 50);

        return $product;
    }

    /**
     * Create guest cart with product.
     */
    private function createGuestCartWithProduct(Product $product, int $qty = 1): array
    {
        $this->flushHeaders();

        $tokenResponse = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);
        $token = (string) ($tokenResponse->json('cartToken') ?? $tokenResponse->json('sessionToken'));
        $this->assertNotEmpty($token, 'Guest cart token missing from the cart-tokens response.');

        $addResponse = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product->id,
            'quantity' => $qty,
        ]);
        expect($addResponse->getStatusCode())->toBeIn([200, 201]);

        $cartId = (int) ($addResponse->json('id') ?? $addResponse->json('_id') ?? 0);
        $this->assertGreaterThan(0, $cartId, 'Guest cart ID missing from add-product response.');

        return [
            'token' => $token,
            'cartId' => $cartId,
        ];
    }

    /**
     * Authed customer.
     */
    private function authedCustomer(): Customer
    {
        return $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
    }
}
