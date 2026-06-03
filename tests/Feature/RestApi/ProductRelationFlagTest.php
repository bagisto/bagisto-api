<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Wishlist;

class ProductRelationFlagTest extends RestApiTestCase
{
    /** Find a product row (by id) in the REST listing array. */
    private function rowFor(array $rows, int $productId): ?array
    {
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $productId) {
                return $row;
            }
        }

        return null;
    }

    // ── Listing ─────────────────────────────────────────────────

    public function test_listing_marks_flags_true_for_owner(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $token = 'RESTRELFLAGOWNER';
        $tagged = $this->createBaseProduct('simple', ['sku' => $token.'A'.uniqid()]);
        $other = $this->createBaseProduct('simple', ['sku' => $token.'B'.uniqid()]);

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $tagged->id,
            'channel_id'  => $channel->id,
        ]);
        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $tagged->id,
        ]);

        $response = $this->authenticatedGet($customer, "/api/shop/products?query={$token}&per_page=50");
        $response->assertOk();

        $rows = $response->json();
        $taggedRow = $this->rowFor($rows, $tagged->id);
        $otherRow = $this->rowFor($rows, $other->id);

        // 0/1 integers over REST.
        expect($taggedRow['isInWishlist'])->toBe(1);
        expect($taggedRow['isInCompare'])->toBe(1);
        expect($otherRow['isInWishlist'])->toBe(0);
        expect($otherRow['isInCompare'])->toBe(0);
    }

    public function test_listing_flags_false_for_guest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $token = 'RESTRELFLAGGUEST';
        $tagged = $this->createBaseProduct('simple', ['sku' => $token.uniqid()]);

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $tagged->id,
            'channel_id'  => $channel->id,
        ]);
        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $tagged->id,
        ]);

        $response = $this->publicGet("/api/shop/products?query={$token}&per_page=50");
        $response->assertOk();

        $row = $this->rowFor($response->json(), $tagged->id);

        expect($row['isInWishlist'])->toBe(0);
        expect($row['isInCompare'])->toBe(0);
    }

    // ── Detail ──────────────────────────────────────────────────

    public function test_detail_carries_both_flags_for_owner(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);
        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
        ]);

        $response = $this->authenticatedGet($customer, "/api/shop/products/{$product->id}");
        $response->assertOk();

        expect($response->json('isInWishlist'))->toBe(1);
        expect($response->json('isInCompare'))->toBe(1);
    }

    public function test_detail_flags_false_for_guest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'channel_id'  => $channel->id,
        ]);

        $response = $this->publicGet("/api/shop/products/{$product->id}");
        $response->assertOk();

        expect($response->json('isInWishlist'))->toBe(0);
        expect($response->json('isInCompare'))->toBe(0);
    }
}
