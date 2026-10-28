<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

/**
 * A signed-in customer is priced with their own customer group, as on the storefront.
 */
class CustomerGroupPricingTest extends RestApiTestCase
{
    private function productWithGroupPrices(): Product
    {
        $product = $this->createBaseProduct('simple');

        $channelId = core()->getCurrentChannel()->id;

        DB::table('product_price_indices')->where('product_id', $product->id)->delete();

        foreach (['guest' => 100, 'general' => 60] as $code => $price) {
            DB::table('product_price_indices')->insert([
                'product_id' => $product->id,
                'customer_group_id' => DB::table('customer_groups')->where('code', $code)->value('id'),
                'channel_id' => $channelId,
                'min_price' => $price,
                'regular_min_price' => $price,
                'max_price' => $price,
                'regular_max_price' => $price,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $product;
    }

    private function generalCustomer(): Customer
    {
        return $this->createCustomer([
            'customer_group_id' => DB::table('customer_groups')->where('code', 'general')->value('id'),
        ]);
    }

    public function test_guest_gets_the_guest_group_price(): void
    {
        $product = $this->productWithGroupPrices();

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        $response->assertOk();
        expect((float) $response->json('minimumPrice'))->toBe(100.0);
    }

    public function test_signed_in_customer_gets_their_group_price(): void
    {
        $product = $this->productWithGroupPrices();

        $customer = $this->generalCustomer();

        $response = $this->withHeaders($this->authHeaders($customer))->getJson('/api/shop/products/'.$product->id);

        $response->assertOk();
        expect((float) $response->json('minimumPrice'))->toBe(60.0);
    }
}
