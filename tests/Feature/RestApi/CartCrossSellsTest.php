<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Checkout\Repositories\CartRepository;

class CartCrossSellsTest extends RestApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    private function createCartWithProducts(): array
    {
        $this->seedRequiredData();

        $cart = app(CartRepository::class)->create([
            'is_guest' => 1,
            'is_active' => 1,
            'channel_id' => core()->getCurrentChannel()->id,
            'global_currency_code' => core()->getBaseCurrencyCode(),
            'base_currency_code' => core()->getBaseCurrencyCode(),
            'channel_currency_code' => core()->getChannelBaseCurrencyCode(),
            'cart_currency_code' => core()->getCurrentCurrencyCode(),
        ]);

        $source = $this->createBaseProduct('simple');
        $recommended = $this->createBaseProduct('simple');
        $alreadyInCart = $this->createBaseProduct('simple');

        DB::table('cart_items')->insert([
            'cart_id' => $cart->id,
            'product_id' => $source->id,
            'quantity' => 1,
            'sku' => $source->sku,
            'type' => $source->type,
            'name' => $source->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('cart_items')->insert([
            'cart_id' => $cart->id,
            'product_id' => $alreadyInCart->id,
            'quantity' => 1,
            'sku' => $alreadyInCart->sku,
            'type' => $alreadyInCart->type,
            'name' => $alreadyInCart->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('product_cross_sells')->insert([
            [
                'parent_id' => $source->id,
                'child_id' => $recommended->id,
            ], [
                'parent_id' => $source->id,
                'child_id' => $alreadyInCart->id,
            ],
        ]);

        $token = app(GuestCartTokensRepository::class)->createToken((int) $cart->id)->token;

        return [$token, (int) $recommended->id];
    }

    public function test_get_cart_cross_sells_excludes_products_already_in_the_cart(): void
    {
        [$token, $recommendedId] = $this->createCartWithProducts();

        $response = $this->getJson('/api/shop/cart/cross-sells', [
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk();

        expect(collect($response->json())->pluck('id')->all())->toBe([$recommendedId]);
    }

    public function test_get_cart_cross_sells_rejects_an_invalid_cart_token(): void
    {
        $this->seedRequiredData();

        $response = $this->getJson('/api/shop/cart/cross-sells', [
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer invalid-cart-token',
        ]);

        expect($response->getStatusCode())->toBeIn([401, 404]);
    }
}
