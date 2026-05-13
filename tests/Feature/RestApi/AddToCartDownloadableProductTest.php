<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AddToCartDownloadableProductTest extends RestApiTestCase
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

    private function createDownloadableProductPayload(int $linksCount = 2): array
    {
        $product = $this->createBaseProduct('downloadable');
        $this->ensureInventory($product, 50);

        $links = [];

        for ($i = 1; $i <= $linksCount; $i++) {
            $links[] = (int) DB::table('product_downloadable_links')->insertGetId([
                'product_id' => $product->id,
                'url'        => 'https://example.com/download/'.$product->sku.'/'.$i,
                'file'       => null,
                'file_name'  => null,
                'type'       => 'url',
                'price'      => 0,
                'downloads'  => 0,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'productId' => (int) $product->id,
            'links'     => $links,
        ];
    }

    public function test_add_downloadable_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createDownloadableProductPayload();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => $payload['productId'],
            'quantity'  => 1,
            'links'     => $payload['links'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    public function test_add_downloadable_product_to_customer_cart(): void
    {
        $payload = $this->createDownloadableProductPayload();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $payload['productId'],
            'quantity'  => 1,
            'links'     => $payload['links'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
    }
}
