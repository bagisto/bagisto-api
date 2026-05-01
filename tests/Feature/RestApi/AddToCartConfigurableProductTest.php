<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AddToCartConfigurableProductTest extends RestApiTestCase
{
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    private string $addProductUrl = '/api/shop/add-product-in-cart';

    private function createGuestCartToken(): string
    {
        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);
        expect($response->getStatusCode())->toBeIn([200, 201]);

        $token = (string) ($response->json('cartToken') ?? $response->json('sessionToken'));
        $this->assertNotEmpty($token, 'Guest cart token missing from response.');

        return $token;
    }

    private function postWithToken(string $url, string $token, array $payload): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    private function createConfigurableProductPayload(): array
    {
        $this->seedRequiredData();

        $attributes = \Webkul\Attribute\Models\Attribute::query()
            ->where('is_configurable', 1)
            ->where('type', 'select')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($attributes->isEmpty()) {
            $this->markTestSkipped('No configurable select attributes found. Run Bagisto seeders for attributes like color/size.');
        }

        $parent = $this->createBaseProduct('configurable', [
            'sku' => 'REST-CONFIG-PARENT-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);
        $this->upsertProductAttributeValue($parent->id, 'weight', 1.5, null, 'default');

        $child = $this->createBaseProduct('simple', [
            'sku'       => 'REST-CONFIG-CHILD-'.uniqid(),
            'parent_id' => $parent->id,
        ]);
        $this->ensureInventory($child, 50);
        $this->upsertProductAttributeValue($child->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($child->id, 'weight', 1.5, null, 'default');

        DB::table('product_relations')->insert([
            'parent_id' => $parent->id,
            'child_id'  => $child->id,
        ]);

        $superAttribute = [];

        foreach ($attributes as $attribute) {
            $attributeId = (int) $attribute->id;
            $optionId = $this->createAttributeOption($attributeId, 'Opt-'.$child->sku);

            DB::table('product_super_attributes')->insert([
                'product_id'   => $parent->id,
                'attribute_id' => $attributeId,
            ]);

            $this->upsertProductAttributeValue($child->id, (string) $attribute->code, $optionId, null, 'default');

            $superAttribute[] = [
                'key'   => (string) $attributeId,
                'value' => (int) $optionId,
            ];
        }

        return [
            'productId'                  => (int) $parent->id,
            'selectedConfigurableOption' => (int) $child->id,
            'superAttribute'             => $superAttribute,
        ];
    }

    public function test_add_configurable_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createConfigurableProductPayload();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId'                  => $payload['productId'],
            'quantity'                   => 1,
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute'             => $payload['superAttribute'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    public function test_add_configurable_product_to_customer_cart(): void
    {
        $payload = $this->createConfigurableProductPayload();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId'                  => $payload['productId'],
            'quantity'                   => 1,
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute'             => $payload['superAttribute'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }
}
