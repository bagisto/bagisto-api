<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\Product;

class CartTest extends RestApiTestCase
{
    /**
     * The endpoint that creates a cart and returns its token.
     */
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    /**
     * The endpoint that adds a product to the cart.
     */
    private string $addProductUrl = '/api/shop/add-product-in-cart';

    /**
     * The endpoint that applies a coupon to the cart.
     */
    private string $applyCouponUrl = '/api/shop/apply-coupon';

    /**
     * The endpoint that removes the coupon from the cart.
     */
    private string $removeCouponUrl = '/api/shop/remove-coupon';

    /**
     * The endpoint that removes one cart item.
     */
    private string $removeItemUrl = '/api/shop/remove-cart-item';

    /**
     * The endpoint that removes several cart items.
     */
    private string $removeItemsUrl = '/api/shop/remove-cart-items';

    /**
     * The endpoint that updates a cart item's quantity.
     */
    private string $updateItemUrl = '/api/shop/update-cart-item';

    /**
     * Creating a guest cart returns a successful response with a cart token.
     */
    public function test_create_guest_cart_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('cartToken') ?? $response->json('sessionToken'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * The guest cart token response carries the success and guest flags.
     */
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

    /**
     * Creating a cart as a customer returns a non-guest cart.
     */
    public function test_create_customer_cart_returns_cart_data(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->cartTokensUrl, []);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
    }

    /**
     * A guest can add a simple product to their cart.
     */
    public function test_add_simple_product_to_cart_as_guest(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    /**
     * Adding a product without a cart token is rejected.
     */
    public function test_add_product_to_cart_requires_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createSimpleProduct();

        $response = $this->publicPost($this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Adding a product without a product id returns an error.
     */
    public function test_add_product_without_product_id_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Adding a product that does not exist returns an error.
     */
    public function test_add_product_with_nonexistent_product_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => 999999,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * A customer can add a product to their cart.
     */
    public function test_add_product_to_customer_cart(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $product = $this->createSimpleProduct();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
    }

    /**
     * Applying a coupon without a cart token is rejected.
     */
    public function test_apply_coupon_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->applyCouponUrl, [
            'couponCode' => 'TESTCODE',
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Applying a coupon without a code returns an error.
     */
    public function test_apply_coupon_without_coupon_code_returns_error(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->applyCouponUrl, $cart['token'], []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Applying an unknown coupon code returns an error or an unsuccessful response.
     */
    public function test_apply_invalid_coupon_code_returns_error_or_failure(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->applyCouponUrl, $cart['token'], [
            'couponCode' => 'INVALID_COUPON_'.uniqid(),
        ]);

        if ($response->getStatusCode() === 201) {
            expect((bool) $response->json('success'))->toBeFalse();
        } else {
            expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
        }
    }

    /**
     * Removing a coupon without a cart token is rejected.
     */
    public function test_remove_coupon_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->removeCouponUrl);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Removing the coupon from a cart succeeds.
     */
    public function test_remove_coupon_from_cart_returns_success(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->removeCouponUrl, $cart['token']);

        expect($response->getStatusCode())->toBeIn([200, 201]);
    }

    /**
     * The remove coupon response carries the cart data.
     */
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

    /**
     * Applying a valid coupon returns the success flag and a message.
     */
    public function test_apply_coupon_response_carries_success_and_message(): void
    {
        $this->seedRequiredData();
        $code = $this->seedActiveCoupon();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->applyCouponUrl, $cart['token'], [
            'couponCode' => $code,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('message'))->toBeString()->not->toBeEmpty();
    }

    /**
     * Reading the cart after applying a coupon returns the applied coupon code.
     */
    public function test_read_cart_returns_applied_coupon_code(): void
    {
        $this->seedRequiredData();
        $code = $this->seedActiveCoupon();
        $cart = $this->createGuestCartWithProduct();

        $apply = $this->postWithToken($this->applyCouponUrl, $cart['token'], ['couponCode' => $code]);
        expect($apply->getStatusCode())->toBeIn([200, 201]);

        $read = $this->postWithToken('/api/shop/cart', $cart['token'], []);
        expect($read->getStatusCode())->toBeIn([200, 201]);
        expect($read->json('couponCode'))->toBe($code);
    }

    /**
     * Reading the cart returns the mini cart offer text when the mini cart is on.
     */
    public function test_read_cart_returns_the_mini_cart_offer_info(): void
    {
        $this->seedRequiredData();
        $this->storeMiniCartSetting('sales.checkout.mini_cart.display_mini_cart', '1');
        $this->storeMiniCartSetting('sales.checkout.mini_cart.offer_info', 'Free shipping over 50');
        $cart = $this->createGuestCartWithProduct();

        $read = $this->postWithToken('/api/shop/cart', $cart['token'], []);

        expect($read->json('miniCartOfferInfoEnabled'))->toBeTrue();
        expect($read->json('miniCartOfferInfo'))->toBe('Free shipping over 50');
    }

    /**
     * The mini cart offer is disabled when the mini cart is off or the offer text is blank.
     */
    public function test_mini_cart_offer_info_is_disabled_when_the_mini_cart_is_off_or_text_is_empty(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $this->storeMiniCartSetting('sales.checkout.mini_cart.display_mini_cart', '0');
        $this->storeMiniCartSetting('sales.checkout.mini_cart.offer_info', 'Offer');

        expect($this->postWithToken('/api/shop/cart', $cart['token'], [])->json('miniCartOfferInfoEnabled'))->toBeFalse();

        $this->storeMiniCartSetting('sales.checkout.mini_cart.display_mini_cart', '1');
        $this->storeMiniCartSetting('sales.checkout.mini_cart.offer_info', ' ');

        $read = $this->postWithToken('/api/shop/cart', $cart['token'], []);

        expect($read->json('miniCartOfferInfoEnabled'))->toBeFalse();
        expect($read->json('miniCartOfferInfo'))->toBeNull();
    }

    /**
     * Removing an applied coupon returns the success flag and a message.
     */
    public function test_remove_coupon_response_carries_success_and_message(): void
    {
        $this->seedRequiredData();
        $code = $this->seedActiveCoupon();
        $cart = $this->createGuestCartWithProduct();

        $this->postWithToken($this->applyCouponUrl, $cart['token'], ['couponCode' => $code]);

        $response = $this->postWithToken($this->removeCouponUrl, $cart['token']);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('message'))->toBeString()->not->toBeEmpty();
    }

    /**
     * Removing the only cart item empties the cart.
     */
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

    /**
     * Removing a cart item without a cart token is rejected.
     */
    public function test_remove_cart_item_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->removeItemUrl, [
            'cartItemId' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Removing a cart item without its id returns an error.
     */
    public function test_remove_cart_item_without_cart_item_id_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->removeItemUrl, $token, []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Removing a cart item that does not exist returns an error.
     */
    public function test_remove_nonexistent_cart_item_returns_error(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->removeItemUrl, $cart['token'], [
            'cartItemId' => 999999,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 404, 422, 500]);
    }

    /**
     * Removing several cart items at once empties the cart.
     */
    public function test_remove_multiple_cart_items(): void
    {
        $this->seedRequiredData();
        $product1 = $this->createSimpleProduct();
        $product2 = $this->createSimpleProduct();
        $token = $this->createGuestCartToken();

        $resp1 = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product1->id,
            'quantity' => 1,
        ]);
        $resp2 = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product2->id,
            'quantity' => 1,
        ]);

        $id1 = (int) ($resp1->json('items.0.id') ?? 0);
        $id2 = (int) ($resp2->json('items.1.id') ?? $resp2->json('items.0.id') ?? 0);

        $response = $this->postWithToken($this->removeItemsUrl, $token, [
            'itemIds' => array_filter([$id1, $id2]),
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('itemsCount'))->toBe(0);
    }

    /**
     * Removing several cart items without a cart token is rejected.
     */
    public function test_remove_cart_items_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->removeItemsUrl, [
            'itemIds' => [1, 2],
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Removing several cart items without their ids returns an error.
     */
    public function test_remove_cart_items_without_item_ids_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->removeItemsUrl, $token, []);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Updating a cart item's quantity succeeds.
     */
    public function test_update_cart_item_quantity(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->updateItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
            'quantity' => 3,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
    }

    /**
     * Updating a cart item without a cart token is rejected.
     */
    public function test_update_cart_item_requires_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->updateItemUrl, [
            'cartItemId' => 1,
            'quantity' => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Updating a cart item without its id returns an error.
     */
    public function test_update_cart_item_without_cart_item_id_returns_error(): void
    {
        $this->seedRequiredData();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->updateItemUrl, $token, [
            'quantity' => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Updating a cart item to a zero quantity returns an error.
     */
    public function test_update_cart_item_with_invalid_quantity_returns_error(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->updateItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
            'quantity' => 0,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * The update cart item response carries the item count and totals.
     */
    public function test_update_cart_item_response_has_cart_fields(): void
    {
        $this->seedRequiredData();
        $cart = $this->createGuestCartWithProduct();

        $response = $this->postWithToken($this->updateItemUrl, $cart['token'], [
            'cartItemId' => $cart['cartItemId'],
            'quantity' => 2,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $data = $response->json();
        expect($data)->toHaveKey('itemsCount');
        expect($data)->toHaveKey('grandTotal');
        expect($data)->toHaveKey('subtotal');
    }

    /**
     * Reading the cart as a customer returns their cart with its items and totals.
     */
    public function test_read_cart_returns_customer_cart_details(): void
    {
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createSimpleProduct();

        $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 2,
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

    /**
     * Core's email-verification token stored on the customer is never accepted as a bearer token.
     */
    public function test_customer_verification_token_is_not_accepted_as_bearer(): void
    {
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $product = $this->createSimpleProduct();

        $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity' => 1,
        ])->assertSuccessful();

        $ownCart = $this->authenticatedPost($customer, '/api/shop/cart', []);

        $ownCart->assertSuccessful();

        expect($ownCart->json('customerId'))->toBe($customer->id);

        $response = $this->postWithToken('/api/shop/cart', $customer->token);

        expect($response->getStatusCode())->toBeIn([401, 403, 404])
            ->and($response->json('customerId'))->toBeNull()
            ->and($response->json('items'))->toBeNull();
    }

    /**
     * Reading the cart without authentication is rejected.
     */
    public function test_read_cart_requires_auth(): void
    {
        $response = $this->publicPost('/api/shop/cart', []);

        expect(in_array($response->getStatusCode(), [401, 403, 404, 500]))->toBeTrue();
    }

    /**
     * Reading a cart by its id without a token is rejected and leaks no cart token.
     */
    public function test_read_cart_by_id_without_token_is_rejected(): void
    {
        $this->seedRequiredData();
        $victim = $this->createGuestCartWithProduct();
        $victimCartId = (int) DB::table('cart_items')->where('id', $victim['cartItemId'])->value('cart_id');

        $this->flushHeaders();

        $response = $this->publicPost('/api/shop/cart', ['cartId' => $victimCartId]);

        expect($response->getStatusCode())->toBeIn([401, 403]);
        expect($response->json('cartToken'))->toBeNull();
    }

    /**
     * Reading the cart with another cart's id returns the caller's own cart, never the other one or its token.
     */
    public function test_read_cart_never_returns_another_cart_or_its_token(): void
    {
        $this->seedRequiredData();
        $victim = $this->createGuestCartWithProduct();
        $victimCartId = (int) DB::table('cart_items')->where('id', $victim['cartItemId'])->value('cart_id');

        $attacker = $this->createGuestCartWithProduct();

        $response = $this->postWithToken('/api/shop/cart', $attacker['token'], ['cartId' => $victimCartId]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) ($response->json('id') ?? $response->json('_id')))->not->toBe($victimCartId);
        expect($response->json('cartToken'))->not->toBe($victim['token']);
    }

    /**
     * Send a storefront POST request authorised with the given bearer token.
     */
    private function postWithToken(string $url, string $token, array $payload = []): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    /**
     * Send a storefront GET request authorised with the given bearer token.
     */
    private function getWithToken(string $url, string $token): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->getJson($url);
    }

    /**
     * Create a guest cart and return its token.
     */
    private function createGuestCartToken(): string
    {
        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $token = $response->json('cartToken') ?? $response->json('sessionToken');
        $this->assertNotEmpty($token, 'Guest cart token missing from response.');

        return (string) $token;
    }

    /**
     * Create a priced, in-stock simple product that does not manage stock.
     */
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
     * Create a guest cart holding one product and return its token and cart item id.
     */
    private function createGuestCartWithProduct(): array
    {
        $product = $this->createSimpleProduct();
        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $items = $response->json('items') ?? [];
        $cartItemId = (int) ($items[0]['id'] ?? 0);

        $this->assertGreaterThan(0, $cartItemId, 'Cart item ID missing from response.');

        return [
            'token' => $token,
            'cartItemId' => $cartItemId,
        ];
    }

    /**
     * Seed an active ten percent cart rule for every channel and customer group with the given coupon code.
     * Returns the coupon code, generated when none is given.
     */
    private function seedActiveCoupon(?string $code = null): string
    {
        $code = $code ?? ('SAVE10_'.strtoupper(uniqid()));

        $ruleId = \DB::table('cart_rules')->insertGetId([
            'name' => 'Test Rule '.$code,
            'description' => 'Test rule',
            'coupon_type' => '1',
            'use_auto_generation' => '0',
            'usage_per_customer' => '0',
            'uses_per_coupon' => '0',
            'times_used' => 0,
            'condition_type' => '2',
            'end_other_rules' => '0',
            'uses_attribute_conditions' => '0',
            'discount_quantity' => '0',
            'discount_step' => '0',
            'apply_to_shipping' => '0',
            'free_shipping' => '0',
            'sort_order' => 0,
            'status' => '1',
            'action_type' => 'by_percent',
            'discount_amount' => 10,
            'conditions' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (\DB::table('channels')->pluck('id') as $cid) {
            \DB::table('cart_rule_channels')->insertOrIgnore([
                'cart_rule_id' => $ruleId,
                'channel_id' => $cid,
            ]);
        }
        foreach (\DB::table('customer_groups')->pluck('id') as $cgid) {
            \DB::table('cart_rule_customer_groups')->insertOrIgnore([
                'cart_rule_id' => $ruleId,
                'customer_group_id' => $cgid,
            ]);
        }

        \DB::table('cart_rule_coupons')->insert([
            'cart_rule_id' => $ruleId,
            'code' => $code,
            'usage_limit' => 0,
            'usage_per_customer' => 0,
            'times_used' => 0,
            'is_primary' => 1,
            'type' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $code;
    }

    /**
     * Store a mini cart configuration value for the requested channel and locale and clear the config cache.
     */
    private function storeMiniCartSetting(string $code, string $value): void
    {
        DB::table('core_config')->where('code', $code)->delete();

        DB::table('core_config')->insert([
            'code' => $code,
            'value' => $value,
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->forgetCoreConfigCache();
    }
}
