<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AddToCartGroupedProductTest extends RestApiTestCase
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

    private function createGroupedProductPayload(int $associatedCount = 2): array
    {
        $grouped = $this->createBaseProduct('grouped');
        $this->ensureInventory($grouped, 50);

        $qtyMap = [];

        for ($i = 1; $i <= $associatedCount; $i++) {
            $associated = $this->createBaseProduct('simple', [
                'sku' => 'REST-GROUPED-ASSOC-'.$grouped->id.'-'.$i,
            ]);
            $this->ensureInventory($associated, 50);
            $this->upsertProductAttributeValue($associated->id, 'manage_stock', 0, null, 'default');

            DB::table('product_grouped_products')->insert([
                'product_id'            => $grouped->id,
                'associated_product_id' => $associated->id,
                'qty'                   => 1,
                'sort_order'            => $i,
            ]);

            $qtyMap[(string) $associated->id] = 1;
        }

        return [
            'productId'  => (int) $grouped->id,
            'groupedQty' => json_encode($qtyMap, JSON_UNESCAPED_SLASHES),
        ];
    }

    public function test_add_grouped_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createGroupedProductPayload();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId'  => $payload['productId'],
            'quantity'   => 1,
            'groupedQty' => $payload['groupedQty'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    public function test_add_grouped_product_to_customer_cart(): void
    {
        $payload = $this->createGroupedProductPayload();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId'  => $payload['productId'],
            'quantity'   => 1,
            'groupedQty' => $payload['groupedQty'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
    }
}
