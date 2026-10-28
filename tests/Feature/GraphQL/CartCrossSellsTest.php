<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Checkout\Repositories\CartRepository;

class CartCrossSellsTest extends GraphQLTestCase
{
    /**
     * The cart cross-sells query.
     */
    private string $query = <<<'GQL'
        query cartCrossSells($first: Int) {
          cartCrossSells(first: $first) {
            totalCount
            edges { node { _id sku } }
          }
        }
    GQL;

    // ============================================================================
    // Cart Cross-Sells
    // ============================================================================

    /**
     * The query returns the cross-sells of the cart, leaving out products already in it.
     */
    public function test_cart_cross_sells_exclude_products_already_in_the_cart(): void
    {
        [$token, $recommendedId] = $this->createCartWithProducts();

        $response = $this->graphQL($this->query, [], ['Authorization' => 'Bearer '.$token]);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.cartCrossSells.totalCount'))->toBe(1);
        expect(collect($response->json('data.cartCrossSells.edges'))->pluck('node._id')->all())->toBe([$recommendedId]);
    }

    /**
     * An empty cart has no cross-sells.
     */
    public function test_cart_cross_sells_of_an_empty_cart_are_empty(): void
    {
        $this->seedRequiredData();

        $cart = $this->createGuestCart();
        $token = app(GuestCartTokensRepository::class)->createToken((int) $cart->id)->token;

        $response = $this->graphQL($this->query, [], ['Authorization' => 'Bearer '.$token]);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.cartCrossSells.totalCount'))->toBe(0);
        expect($response->json('data.cartCrossSells.edges'))->toBe([]);
    }

    /**
     * The query refuses a request without a cart token.
     */
    public function test_cart_cross_sells_require_a_cart_token(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->query);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.graphql.cart.authentication-required'));
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    /**
     * Create an active guest cart.
     */
    private function createGuestCart(): object
    {
        return app(CartRepository::class)->create([
            'is_guest' => 1,
            'is_active' => 1,
            'channel_id' => core()->getCurrentChannel()->id,
            'global_currency_code' => core()->getBaseCurrencyCode(),
            'base_currency_code' => core()->getBaseCurrencyCode(),
            'channel_currency_code' => core()->getChannelBaseCurrencyCode(),
            'cart_currency_code' => core()->getCurrentCurrencyCode(),
        ]);
    }

    /**
     * Create a guest cart holding a product with two cross-sells, one of them already in the cart.
     */
    private function createCartWithProducts(): array
    {
        $this->seedRequiredData();

        $cart = $this->createGuestCart();

        $source = $this->createBaseProduct('simple');
        $recommended = $this->createBaseProduct('simple');
        $alreadyInCart = $this->createBaseProduct('simple');

        foreach ([$source, $alreadyInCart] as $product) {
            DB::table('cart_items')->insert([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'sku' => $product->sku,
                'type' => $product->type,
                'name' => $product->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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
}
