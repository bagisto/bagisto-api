<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;

class MergeCartTest extends GraphQLTestCase
{
    // ─── Helper Methods ─────────────────────────────────────────────────

    private function createGuestCartToken(): string
    {
        $mutation = <<<'GQL'
            mutation {
              createCartToken(input: {}) {
                cartToken {
                  id
                  cartToken
                  isGuest
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);
        $response->assertSuccessful();

        return $response->json('data.createCartToken.cartToken.cartToken');
    }

    private function guestHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer '.$token];
    }

    private function addProductToGuestCart(string $guestToken, int $productId, int $quantity = 1, array $extra = []): ?array
    {
        $variables = array_merge([
            'productId' => $productId,
            'quantity' => $quantity,
        ], $extra);

        $mutation = <<<'GQL'
            mutation addProduct(
                $productId: Int!
                $quantity: Int!
                $selectedConfigurableOption: Int
                $superAttribute: Iterable
            ) {
              createAddProductInCart(input: {
                productId: $productId
                quantity: $quantity
                selectedConfigurableOption: $selectedConfigurableOption
                superAttribute: $superAttribute
              }) {
                addProductInCart {
                  id
                  _id
                  itemsCount
                  itemsQty
                  grandTotal
                  isGuest
                  success
                  items {
                    edges {
                      node {
                        id
                        productId
                        name
                        type
                        quantity
                        price
                        total
                        options
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, $variables, $this->guestHeaders($guestToken));
        $response->assertSuccessful();

        return $response->json('data.createAddProductInCart.addProductInCart');
    }

    private function createCustomerWithAuth(): array
    {
        $customer = $this->createCustomer([
            'password' => bcrypt('password123'),
            'token' => md5(uniqid(rand(), true)),
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        return compact('customer', 'token');
    }

    private function mergeCart(string $customerToken, int $cartId): array
    {
        $mutation = <<<'GQL'
            mutation mergeCart($cartId: Int!) {
              createMergeCart(input: { cartId: $cartId }) {
                mergeCart {
                  id
                  _id
                  itemsCount
                  itemsQty
                  grandTotal
                  subtotal
                  success
                  message
                  isGuest
                  customerId
                  items {
                    edges {
                      node {
                        id
                        productId
                        name
                        type
                        quantity
                        price
                        total
                        options
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'cartId' => $cartId,
        ], ['Authorization' => 'Bearer '.$customerToken]);

        return [
            'response' => $response,
            'data' => $response->json('data.createMergeCart.mergeCart'),
            'errors' => $response->json('errors'),
        ];
    }

    // ─── Tests ──────────────────────────────────────────────────────────

    /**
     * Merge guest cart with simple product into customer cart (customer has no existing cart).
     */
    public function test_merge_simple_product_into_empty_customer_cart(): void
    {
        $guestToken = $this->createGuestCartToken();
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $guestCart = $this->addProductToGuestCart($guestToken, $product->id, 2);
        $guestCartId = (int) $guestCart['_id'];

        $this->assertTrue($guestCart['isGuest']);
        $this->assertSame(1, $guestCart['itemsCount']);

        // Login and merge
        ['token' => $customerToken] = $this->createCustomerWithAuth();
        $result = $this->mergeCart($customerToken, $guestCartId);

        $result['response']->assertSuccessful();
        $this->assertNull($result['errors'], 'Merge should not return errors');

        $data = $result['data'];
        $this->assertTrue($data['success']);
        $this->assertGreaterThanOrEqual(1, $data['itemsCount']);
        $this->assertGreaterThan(0, $data['grandTotal']);
        $this->assertFalse($data['isGuest'], 'Merged cart should belong to customer');
    }

    /**
     * Merge guest cart into customer cart that already has items — items should combine.
     */
    public function test_merge_into_existing_customer_cart(): void
    {
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        ['token' => $customerToken] = $this->createCustomerWithAuth();

        // Add product to customer cart first
        $this->graphQL(<<<'GQL'
            mutation addProduct($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: { productId: $productId, quantity: $quantity }) {
                addProductInCart { id itemsCount }
              }
            }
        GQL, [
            'productId' => $product->id,
            'quantity' => 1,
        ], ['Authorization' => 'Bearer '.$customerToken]);

        // Create guest cart with same product
        $guestToken = $this->createGuestCartToken();
        $guestCart = $this->addProductToGuestCart($guestToken, $product->id, 3);
        $guestCartId = (int) $guestCart['_id'];

        // Merge
        $result = $this->mergeCart($customerToken, $guestCartId);
        $result['response']->assertSuccessful();

        $data = $result['data'];
        $this->assertTrue($data['success']);

        // Same product should have combined quantities (1 + 3 = 4)
        $items = collect($data['items']['edges'] ?? [])->pluck('node');
        $matchingItem = $items->firstWhere('productId', $product->id);
        $this->assertNotNull($matchingItem, 'Product should exist in merged cart');
        $this->assertSame(4, $matchingItem['quantity'], 'Quantities should be combined for same product');
    }

    /**
     * Merge requires authentication — should fail without bearer token.
     */
    public function test_merge_fails_without_authentication(): void
    {
        $guestToken = $this->createGuestCartToken();
        $productData = $this->createTestProduct();
        $guestCart = $this->addProductToGuestCart($guestToken, $productData['product']->id, 1);
        $guestCartId = (int) $guestCart['_id'];

        // Call merge without auth token
        $mutation = <<<'GQL'
            mutation mergeCart($cartId: Int!) {
              createMergeCart(input: { cartId: $cartId }) {
                mergeCart { id success }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['cartId' => $guestCartId]);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Merge without auth should return an error');
    }

    /**
     * Merge with non-existent cart ID should fail gracefully.
     */
    public function test_merge_fails_with_invalid_cart_id(): void
    {
        ['token' => $customerToken] = $this->createCustomerWithAuth();

        $result = $this->mergeCart($customerToken, 999999);

        $errors = $result['errors'];
        $this->assertNotEmpty($errors, 'Merge with invalid cart ID should return an error');
    }

    /**
     * Guest cart should be deactivated after merge.
     */
    public function test_guest_cart_deactivated_after_merge(): void
    {
        $guestToken = $this->createGuestCartToken();
        $productData = $this->createTestProduct();
        $guestCart = $this->addProductToGuestCart($guestToken, $productData['product']->id, 1);
        $guestCartId = (int) $guestCart['_id'];

        ['token' => $customerToken] = $this->createCustomerWithAuth();
        $result = $this->mergeCart($customerToken, $guestCartId);
        $result['response']->assertSuccessful();

        // Verify guest cart is deactivated in DB
        $guestCartRow = DB::table('cart')->where('id', $guestCartId)->first();
        $this->assertSame(0, (int) $guestCartRow->is_active, 'Guest cart should be deactivated after merge');
    }

    /**
     * Merge guest cart with multiple different products.
     */
    public function test_merge_multiple_products(): void
    {
        // We need two different products with inventory
        $products = DB::table('product_inventories')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('SUM(qty) > 0')
            ->limit(2)
            ->pluck('product_id');

        if ($products->count() < 2) {
            $this->markTestSkipped('Need at least 2 products with inventory');
        }

        $guestToken = $this->createGuestCartToken();
        $this->addProductToGuestCart($guestToken, $products[0], 1);
        $guestCart = $this->addProductToGuestCart($guestToken, $products[1], 2);
        $guestCartId = (int) $guestCart['_id'];

        $this->assertSame(2, $guestCart['itemsCount'], 'Guest cart should have 2 items');

        ['token' => $customerToken] = $this->createCustomerWithAuth();
        $result = $this->mergeCart($customerToken, $guestCartId);
        $result['response']->assertSuccessful();

        $data = $result['data'];
        $this->assertTrue($data['success']);
        $this->assertSame(2, $data['itemsCount'], 'Merged cart should have 2 items');
    }

    /**
     * Merge should not fail when guest cart has configurable product with child item.
     */
    public function test_merge_configurable_product(): void
    {
        // Find a configurable product with variants that have inventory
        $configurable = Product::query()
            ->where('type', 'configurable')
            ->whereHas('variants', function ($q) {
                $q->whereHas('inventories', function ($iq) {
                    $iq->where('qty', '>', 0);
                });
            })
            ->first();

        if (! $configurable) {
            $this->markTestSkipped('No configurable product with variant inventory found');
        }

        $variant = $configurable->variants()
            ->whereHas('inventories', function ($q) {
                $q->where('qty', '>', 0);
            })
            ->first();

        if (! $variant) {
            $this->markTestSkipped('No variant with inventory found');
        }

        // Build super_attribute from variant's attribute values
        $superAttribute = [];
        // Get super attributes from the parent product
        $superAttrIds = DB::table('product_super_attributes')
            ->where('product_id', $configurable->id)
            ->pluck('attribute_id');

        foreach ($superAttrIds as $attrId) {
            $val = DB::table('product_attribute_values')
                ->where('product_id', $variant->id)
                ->where('attribute_id', $attrId)
                ->value('integer_value');

            if ($val) {
                $superAttribute[(string) $attrId] = (string) $val;
            }
        }

        if (empty($superAttribute)) {
            $this->markTestSkipped('Could not determine super attributes for variant');
        }

        $guestToken = $this->createGuestCartToken();
        $guestCart = $this->addProductToGuestCart($guestToken, $configurable->id, 1, [
            'selectedConfigurableOption' => $variant->id,
            'superAttribute' => $superAttribute,
        ]);

        if ($guestCart === null || ($guestCart['itemsCount'] ?? 0) !== 1) {
            $this->markTestSkipped('Configurable variant is not saleable in this environment (inventory not bound to the active channel); cannot add to cart.');
        }

        $guestCartId = (int) $guestCart['_id'];
        $this->assertSame(1, $guestCart['itemsCount']);

        ['token' => $customerToken] = $this->createCustomerWithAuth();
        $result = $this->mergeCart($customerToken, $guestCartId);

        $result['response']->assertSuccessful();
        $this->assertNull($result['errors'], 'Merge with configurable product should not error: '.json_encode($result['errors'] ?? []));

        $data = $result['data'];
        $this->assertTrue($data['success']);
        $this->assertGreaterThanOrEqual(1, $data['itemsCount']);

        // Verify the configurable item is present with options
        $items = collect($data['items']['edges'] ?? [])->pluck('node');
        $configItem = $items->firstWhere('type', 'configurable');
        $this->assertNotNull($configItem, 'Configurable item should exist in merged cart');
    }

    /**
     * Merging the same guest cart twice should not duplicate items.
     * The second merge operates on the already-deactivated cart (no items left).
     */
    public function test_merge_same_cart_twice_does_not_duplicate(): void
    {
        $guestToken = $this->createGuestCartToken();
        $productData = $this->createTestProduct();
        $guestCart = $this->addProductToGuestCart($guestToken, $productData['product']->id, 1);
        $guestCartId = (int) $guestCart['_id'];

        ['token' => $customerToken] = $this->createCustomerWithAuth();

        // First merge succeeds
        $result1 = $this->mergeCart($customerToken, $guestCartId);
        $result1['response']->assertSuccessful();
        $this->assertTrue($result1['data']['success']);
        $firstMergeCount = $result1['data']['itemsCount'];

        // Second merge on same deactivated cart should not add more items
        $result2 = $this->mergeCart($customerToken, $guestCartId);
        $result2['response']->assertSuccessful();

        $data2 = $result2['data'];
        $this->assertLessThanOrEqual($firstMergeCount, $data2['itemsCount'],
            'Items should not increase when merging same cart again');
    }
}
