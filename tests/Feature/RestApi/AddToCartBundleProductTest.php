<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AddToCartBundleProductTest extends RestApiTestCase
{
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    private string $addProductUrl = '/api/shop/add-product-in-cart';

    private function createGuestCartToken(): string
    {
        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);
        expect($response->getStatusCode())->toBeIn([200, 201]);

        return (string) ($response->json('cartToken') ?? $response->json('sessionToken'));
    }

    private function postWithToken(string $url, string $token, array $payload): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    private function createBundleProductPayload(): array
    {
        $bundle = $this->createBaseProduct('bundle');
        $this->ensureInventory($bundle, 50);
        $this->upsertProductAttributeValue($bundle->id, 'manage_stock', 0, null, 'default');

        $optionId = (int) DB::table('product_bundle_options')->insertGetId([
            'product_id'  => $bundle->id,
            'type'        => 'checkbox',
            'is_required' => 1,
            'sort_order'  => 1,
        ]);

        $optionProduct = $this->createBaseProduct('simple', [
            'sku' => 'REST-BUNDLE-OPT-'.$bundle->id.'-1',
        ]);
        $this->ensureInventory($optionProduct, 50);
        $this->upsertProductAttributeValue($optionProduct->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($optionProduct->id, 'price', 10.0, null, 'default');

        $bundleOptionProductId = (int) DB::table('product_bundle_option_products')->insertGetId([
            'product_id'               => $optionProduct->id,
            'product_bundle_option_id' => $optionId,
            'qty'                      => 1,
            'is_user_defined'          => 1,
            'is_default'               => 1,
            'sort_order'               => 1,
        ]);

        return [
            'productId'       => (int) $bundle->id,
            'bundleOptions'   => json_encode([(string) $optionId => [$bundleOptionProductId]], JSON_UNESCAPED_SLASHES),
            'bundleOptionQty' => json_encode([(string) $optionId => 1], JSON_UNESCAPED_SLASHES),
        ];
    }

    public function test_add_bundle_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createBundleProductPayload();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId'       => $payload['productId'],
            'quantity'        => 1,
            'bundleOptions'   => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    public function test_add_bundle_product_to_customer_cart(): void
    {
        $payload = $this->createBundleProductPayload();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId'       => $payload['productId'],
            'quantity'        => 1,
            'bundleOptions'   => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
    }
}
