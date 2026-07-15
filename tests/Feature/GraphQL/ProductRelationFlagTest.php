<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Wishlist;

class ProductRelationFlagTest extends GraphQLTestCase
{
    private string $listQuery = <<<'GQL'
        query Products($q: String!) {
          products(query: $q, first: 50) {
            edges {
              node {
                _id
                isInWishlist
                isInCompare
              }
            }
          }
        }
    GQL;

    private string $detailQuery = <<<'GQL'
        query Product($id: ID!) {
          product(id: $id) {
            _id
            isInWishlist
            isInCompare
          }
        }
    GQL;

    /** Pull the node for a given product id out of a products() edges response. */
    private function nodeFor(array $edges, int $productId): ?array
    {
        foreach ($edges as $edge) {
            if ((int) ($edge['node']['_id'] ?? 0) === $productId) {
                return $edge['node'];
            }
        }

        return null;
    }

    /**
     * Cast a GraphQL relation flag to a real bool. The flags are exposed as 0/1 and render as
     * "1" / "0" strings over GraphQL (REST returns 1 / 0 integers). PHP casts both "0" and ""
     * to false and "1" to true, so this stays correct regardless of the scalar rendering.
     */
    private function flag(mixed $value): bool
    {
        return (bool) (int) $value;
    }

    // ── Listing — wishlist ──────────────────────────────────────

    public function test_listing_marks_wishlisted_product_true_for_owner(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $token = 'RELFLAGWISHOWNER';
        $wishlisted = $this->createBaseProduct('simple', ['sku' => $token.'A'.uniqid()]);
        $other = $this->createBaseProduct('simple', ['sku' => $token.'B'.uniqid()]);

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $wishlisted->id,
            'channel_id' => $channel->id,
        ]);

        $response = $this->authenticatedGraphQL($customer, $this->listQuery, ['q' => $token]);
        $response->assertOk();

        $edges = $response->json('data.products.edges');

        expect($this->flag($this->nodeFor($edges, $wishlisted->id)['isInWishlist']))->toBeTrue();
        expect($this->flag($this->nodeFor($edges, $other->id)['isInWishlist']))->toBeFalse();
    }

    public function test_listing_wishlist_flag_false_for_guest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $token = 'RELFLAGWISHGUEST';
        $wishlisted = $this->createBaseProduct('simple', ['sku' => $token.uniqid()]);

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $wishlisted->id,
            'channel_id' => $channel->id,
        ]);

        $response = $this->graphQL($this->listQuery, ['q' => $token]);
        $response->assertOk();

        $node = $this->nodeFor($response->json('data.products.edges'), $wishlisted->id);
        expect($this->flag($node['isInWishlist']))->toBeFalse();
    }

    // ── Listing — compare ───────────────────────────────────────

    public function test_listing_marks_compared_product_true_for_owner(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $token = 'RELFLAGCOMPOWNER';
        $compared = $this->createBaseProduct('simple', ['sku' => $token.'A'.uniqid()]);
        $other = $this->createBaseProduct('simple', ['sku' => $token.'B'.uniqid()]);

        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $compared->id,
        ]);

        $response = $this->authenticatedGraphQL($customer, $this->listQuery, ['q' => $token]);
        $response->assertOk();

        $edges = $response->json('data.products.edges');

        expect($this->flag($this->nodeFor($edges, $compared->id)['isInCompare']))->toBeTrue();
        expect($this->flag($this->nodeFor($edges, $other->id)['isInCompare']))->toBeFalse();
    }

    public function test_listing_compare_flag_false_for_guest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $token = 'RELFLAGCOMPGUEST';
        $compared = $this->createBaseProduct('simple', ['sku' => $token.uniqid()]);

        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $compared->id,
        ]);

        $response = $this->graphQL($this->listQuery, ['q' => $token]);
        $response->assertOk();

        $node = $this->nodeFor($response->json('data.products.edges'), $compared->id);
        expect($this->flag($node['isInCompare']))->toBeFalse();
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
            'product_id' => $product->id,
            'channel_id' => $channel->id,
        ]);
        CompareItem::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->authenticatedGraphQL($customer, $this->detailQuery, [
            'id' => "/api/shop/products/{$product->id}",
        ]);
        $response->assertOk();

        expect($this->flag($response->json('data.product.isInWishlist')))->toBeTrue();
        expect($this->flag($response->json('data.product.isInCompare')))->toBeTrue();
    }

    public function test_detail_flags_false_for_guest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $channel = Channel::first();
        $product = $this->createBaseProduct('simple');

        Wishlist::factory()->create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'channel_id' => $channel->id,
        ]);

        $response = $this->graphQL($this->detailQuery, [
            'id' => "/api/shop/products/{$product->id}",
        ]);
        $response->assertOk();

        expect($this->flag($response->json('data.product.isInWishlist')))->toBeFalse();
        expect($this->flag($response->json('data.product.isInCompare')))->toBeFalse();
    }
}
