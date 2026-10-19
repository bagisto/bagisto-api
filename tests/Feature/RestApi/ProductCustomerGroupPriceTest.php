<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductCustomerGroupPrice;

class ProductCustomerGroupPriceTest extends RestApiTestCase
{
    /**
     * The tier prices of a product are listed with their fields, and no other product's rows.
     */
    public function test_lists_only_the_products_tier_prices(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TIER-'.uniqid()]);
        $otherProduct = $this->createBaseProduct('simple', ['sku' => 'TIER-OTHER-'.uniqid()]);
        $groupId = (int) CustomerGroup::where('code', 'general')->value('id');

        $fixed = $this->createTierPrice($product, 10, 'fixed', 7.5, null);
        $discount = $this->createTierPrice($product, 20, 'discount', 15, $groupId);
        $foreign = $this->createTierPrice($otherProduct, 5, 'fixed', 9, null);

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/customer-group-prices');

        $response->assertOk();

        $rows = collect($response->json())->keyBy('id');

        expect($rows->keys()->map(fn ($id) => (int) $id)->sort()->values()->all())
            ->toBe(collect([$fixed->id, $discount->id])->sort()->values()->all());
        expect($rows->has($foreign->id))->toBeFalse();

        $row = $rows->get($discount->id);

        expect((int) $row['qty'])->toBe(20);
        expect($row['valueType'])->toBe('discount');
        expect((float) $row['value'])->toBe(15.0);
        expect((int) $row['productId'])->toBe($product->id);
        expect((int) $row['customerGroupId'])->toBe($groupId);
        expect($rows->get($fixed->id)['customerGroupId'])->toBeNull();
    }

    /**
     * A product without tier prices returns an empty list.
     */
    public function test_product_without_tier_prices_returns_an_empty_list(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'TIER-NONE-'.uniqid()]);

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/customer-group-prices');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    /**
     * Create a tier price row for the product.
     */
    private function createTierPrice(Product $product, int $qty, string $valueType, float $value, ?int $customerGroupId): ProductCustomerGroupPrice
    {
        return ProductCustomerGroupPrice::create([
            'qty' => $qty,
            'value_type' => $valueType,
            'value' => $value,
            'product_id' => $product->id,
            'customer_group_id' => $customerGroupId,
            'unique_id' => implode('|', array_filter([$qty, $product->id, $customerGroupId])),
        ]);
    }
}
