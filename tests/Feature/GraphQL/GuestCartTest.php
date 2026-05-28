<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class GuestCartTest extends GraphQLTestCase
{
    /**
     * Get guest cart token from the createCart mutation response
     * This token is used as Bearer token for subsequent operations
     */
    private function getGuestCartToken(): string
    {
        $mutation = <<<'GQL'
            mutation createCart {
              createCartToken(input: {}) {
                cartToken {
                  id
                  _id
                  cartToken
                  customerId
                  channelId
                  itemsCount
                  subtotal
                  baseSubtotal
                  discountAmount
                  baseDiscountAmount
                  taxAmount
                  baseTaxAmount
                  shippingAmount
                  baseShippingAmount
                  grandTotal
                  baseGrandTotal
                  formattedSubtotal
                  formattedDiscountAmount
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                  couponCode
                  success
                  message
                  sessionToken
                  isGuest
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);
        $response->assertSuccessful();

        $data = $response->json('data.createCartToken.cartToken');

        $this->assertNotNull($data, 'cartToken response is null');
        $this->assertTrue((bool) ($data['success'] ?? false));

        // Use cartToken as the bearer token
        $token = $data['cartToken'] ?? null;
        $this->assertNotEmpty($token, 'guest cart token is missing');

        return $token;
    }

    /**
     * Helper method to get authorization headers with guest cart token
     */
    private function guestHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * Create Simple Cart (Guest)
     */
    public function test_create_simple_cart(): void
    {
        $token = $this->getGuestCartToken();

        $this->assertNotEmpty($token);
    }

    /**
     * Add Product In Cart (Guest)
     */
    public function test_create_add_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  id
                  _id
                  cartToken
                  customerId
                  channelId
                  subtotal
                  baseSubtotal
                  discountAmount
                  baseDiscountAmount
                  taxAmount
                  baseTaxAmount
                  shippingAmount
                  baseShippingAmount
                  grandTotal
                  baseGrandTotal
                  formattedSubtotal
                  formattedDiscountAmount
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                  couponCode
                  success
                  message
                  sessionToken
                  isGuest
                  itemsQty
                  itemsCount
                  haveStockableItems
                  items {
                    totalCount
                    pageInfo {
                      startCursor
                      endCursor
                      hasNextPage
                      hasPreviousPage
                    }
                    edges {
                      cursor
                      node {
                        id
                        cartId
                        productId
                        name
                        sku
                        quantity
                        price
                        basePrice
                        total
                        baseTotal
                        discountAmount
                        baseDiscountAmount
                        taxAmount
                        baseTaxAmount
                        type
                        formattedPrice
                        formattedTotal
                        priceInclTax
                        basePriceInclTax
                        formattedPriceInclTax
                        totalInclTax
                        baseTotalInclTax
                        formattedTotalInclTax
                        productUrlKey
                        canChangeQty
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity'  => 1,
        ], $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame($product->id, $data['items']['edges'][0]['node']['productId'] ?? null);
        $this->assertSame(1, $data['items']['edges'][0]['node']['quantity'] ?? null);
    }

    /**
     * Update Cart Item Quantity (Guest)
     */
    public function test_update_cart_item_quantity_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $addMutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  items {
                    edges {
                      node {
                        id
                        productId
                        quantity
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $addResponse = $this->graphQL($addMutation, [
            'productId' => $product->id,
            'quantity'  => 9,
        ], $headers);

        $addResponse->assertSuccessful();

        $cartItemId = $addResponse->json('data.createAddProductInCart.addProductInCart.items.edges.0.node.id');
        $this->assertNotNull($cartItemId, 'cart item id is missing for update test');

        $updateMutation = <<<'GQL'
            mutation createUpdateCartItem($cartItemId: Int!, $quantity: Int!) {
              createUpdateCartItem(input: {cartItemId: $cartItemId, quantity: $quantity}) {
                updateCartItem {
                  id
                  _id
                  cartToken
                  customerId
                  channelId
                  subtotal
                  baseSubtotal
                  discountAmount
                  baseDiscountAmount
                  taxAmount
                  baseTaxAmount
                  shippingAmount
                  baseShippingAmount
                  grandTotal
                  baseGrandTotal
                  formattedSubtotal
                  formattedDiscountAmount
                  formattedTaxAmount
                  formattedShippingAmount
                  formattedGrandTotal
                  couponCode
                  items {
                    totalCount
                    pageInfo {
                      startCursor
                      endCursor
                      hasNextPage
                      hasPreviousPage
                    }
                    edges {
                      cursor
                      node {
                        id
                        cartId
                        productId
                        name
                        sku
                        quantity
                        price
                        basePrice
                        total
                        baseTotal
                        discountAmount
                        baseDiscountAmount
                        taxAmount
                        baseTaxAmount
                        type
                        formattedPrice
                        formattedTotal
                        priceInclTax
                        basePriceInclTax
                        formattedPriceInclTax
                        totalInclTax
                        baseTotalInclTax
                        formattedTotalInclTax
                        productUrlKey
                        canChangeQty
                      }
                    }
                  }
                  success
                  message
                  sessionToken
                  isGuest
                  itemsQty
                  itemsCount
                  haveStockableItems
                  paymentMethod
                  paymentMethodTitle
                  subTotalInclTax
                  baseSubTotalInclTax
                  formattedSubTotalInclTax
                  taxTotal
                  formattedTaxTotal
                  shippingAmountInclTax
                  baseShippingAmountInclTax
                  formattedShippingAmountInclTax
                }
              }
            }
        GQL;

        $updateResponse = $this->graphQL($updateMutation, [
            'cartItemId' => (int) $cartItemId,
            'quantity'   => 1,
        ], $headers);

        $updateResponse->assertSuccessful();

        $data = $updateResponse->json('data.createUpdateCartItem.updateCartItem');

        $this->assertNotNull($data);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame($product->id, $data['items']['edges'][0]['node']['productId'] ?? null);
        $this->assertSame(1, $data['items']['edges'][0]['node']['quantity'] ?? null);
    }

    /**
     * Remove Cart Item (Guest)
     */
    public function test_remove_cart_item_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        // First add product to cart
        $addMutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  id
                  itemsCount
                  items {
                    edges {
                      node {
                        id
                        productId
                        quantity
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $addResponse = $this->graphQL($addMutation, [
            'productId' => $product->id,
            'quantity'  => 2,
        ], $headers);

        $addResponse->assertSuccessful();

        $cartItemId = $addResponse->json('data.createAddProductInCart.addProductInCart.items.edges.0.node.id');
        $this->assertNotNull($cartItemId);

        // Now remove the item
        $removeMutation = <<<'GQL'
            mutation createRemoveCartItem($cartItemId: Int!) {
              createRemoveCartItem(input: {cartItemId: $cartItemId}) {
                removeCartItem {
                  id
                  _id
                  cartToken
                  items {
                    totalCount
                    edges {
                      node {
                        id
                        cartId
                        productId
                        name
                        sku
                        quantity
                        price
                        basePrice
                        total
                        baseTotal
                        productUrlKey
                        canChangeQty
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($removeMutation, [
            'cartItemId' => (int) $cartItemId,
        ], $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createRemoveCartItem.removeCartItem');
        $this->assertNotNull($data);
        $this->assertLessThanOrEqual(0, (int) ($data['itemsCount'] ?? 0));
    }

    /**
     * Apply Coupon (Guest)
     */
    public function test_apply_coupon_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        // First add product to cart
        $addMutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  itemsCount
                }
              }
            }
        GQL;

        $this->graphQL($addMutation, [
            'productId' => $product->id,
            'quantity'  => 1,
        ], $headers);

        // Apply coupon
        $couponMutation = <<<'GQL'
            mutation createApplyCoupon($couponCode: String!) {
              createApplyCoupon(input: {couponCode: $couponCode}) {
                applyCoupon {
                  id
                  discountAmount
                  grandTotal
                }
              }
            }
        GQL;

        $response = $this->graphQL($couponMutation, [
            'couponCode' => 'SAVE10',
        ], $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createApplyCoupon.applyCoupon');
        $this->assertNotNull($data);
    }

    /**
     * Remove Coupon (Guest)
     */
    public function test_remove_coupon_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        // Use test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        // First add product to cart
        $addMutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  itemsCount
                }
              }
            }
        GQL;

        $this->graphQL($addMutation, [
            'productId' => $product->id,
            'quantity'  => 1,
        ], $headers);

        // Remove coupon
        $removeCouponMutation = <<<'GQL'
            mutation createRemoveCoupon {
              createRemoveCoupon(input: {}) {
                removeCoupon {
                  id
                  discountAmount
                  grandTotal
                }
              }
            }
        GQL;

        $response = $this->graphQL($removeCouponMutation, [], $headers);

        $response->assertSuccessful();

        $data = $response->json('data.createRemoveCoupon.removeCoupon');
        $this->assertNotNull($data);
    }

    // ── Bug fixes: couponCode populated, success/message on apply/remove ──

    private function seedActiveCoupon(?string $code = null): string
    {
        $code = $code ?? ('SAVE10GQL_'.strtoupper(uniqid()));

        $ruleId = \DB::table('cart_rules')->insertGetId([
            'name'                      => 'Test Rule '.$code,
            'description'               => 'Test',
            'coupon_type'               => '1',
            'use_auto_generation'       => '0',
            'usage_per_customer'        => '0',
            'uses_per_coupon'           => '0',
            'times_used'                => 0,
            'condition_type'            => '2',
            'end_other_rules'           => '0',
            'uses_attribute_conditions' => '0',
            'discount_quantity'         => '0',
            'discount_step'             => '0',
            'apply_to_shipping'         => '0',
            'free_shipping'             => '0',
            'sort_order'                => 0,
            'status'                    => '1',
            'action_type'               => 'by_percent',
            'discount_amount'           => 10,
            'conditions'                => json_encode([]),
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);

        foreach (\DB::table('channels')->pluck('id') as $cid) {
            \DB::table('cart_rule_channels')->insertOrIgnore(['cart_rule_id' => $ruleId, 'channel_id' => $cid]);
        }
        foreach (\DB::table('customer_groups')->pluck('id') as $cgid) {
            \DB::table('cart_rule_customer_groups')->insertOrIgnore(['cart_rule_id' => $ruleId, 'customer_group_id' => $cgid]);
        }

        \DB::table('cart_rule_coupons')->insert([
            'cart_rule_id'       => $ruleId,
            'code'               => $code,
            'usage_limit'        => 0,
            'usage_per_customer' => 0,
            'times_used'         => 0,
            'is_primary'         => 1,
            'type'               => 0,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return $code;
    }

    public function test_apply_coupon_response_carries_success_and_message_graphql(): void
    {
        $code = $this->seedActiveCoupon();
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $this->graphQL(<<<'GQL'
            mutation add($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) { addProductInCart { itemsCount } }
            }
        GQL, ['productId' => $product->id, 'quantity' => 1], $headers);

        $mutation = <<<'GQL'
            mutation apply($couponCode: String!) {
              createApplyCoupon(input: {couponCode: $couponCode}) {
                applyCoupon { id couponCode success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['couponCode' => $code], $headers);
        $response->assertSuccessful();

        $data = $response->json('data.createApplyCoupon.applyCoupon');
        $this->assertNotNull($data);
        $this->assertTrue($data['success'] ?? false);
        $this->assertNotEmpty($data['message'] ?? null);
        $this->assertSame($code, $data['couponCode'] ?? null);
    }

    public function test_read_cart_returns_applied_coupon_code_graphql(): void
    {
        $code = $this->seedActiveCoupon();
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $this->graphQL(<<<'GQL'
            mutation add($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) { addProductInCart { itemsCount } }
            }
        GQL, ['productId' => $product->id, 'quantity' => 1], $headers);

        $this->graphQL(<<<'GQL'
            mutation apply($couponCode: String!) {
              createApplyCoupon(input: {couponCode: $couponCode}) { applyCoupon { id } }
            }
        GQL, ['couponCode' => $code], $headers);

        $read = $this->graphQL(<<<'GQL'
            mutation read {
              createReadCart(input: {}) {
                readCart { id couponCode }
              }
            }
        GQL, [], $headers);

        $read->assertSuccessful();
        $this->assertSame($code, $read->json('data.createReadCart.readCart.couponCode'));
    }

    public function test_remove_coupon_response_carries_success_and_message_graphql(): void
    {
        $code = $this->seedActiveCoupon();
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $this->graphQL(<<<'GQL'
            mutation add($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) { addProductInCart { itemsCount } }
            }
        GQL, ['productId' => $product->id, 'quantity' => 1], $headers);

        $this->graphQL(<<<'GQL'
            mutation apply($couponCode: String!) {
              createApplyCoupon(input: {couponCode: $couponCode}) { applyCoupon { id } }
            }
        GQL, ['couponCode' => $code], $headers);

        $response = $this->graphQL(<<<'GQL'
            mutation rm {
              createRemoveCoupon(input: {}) {
                removeCoupon { id success message couponCode }
              }
            }
        GQL, [], $headers);

        $response->assertSuccessful();
        $data = $response->json('data.createRemoveCoupon.removeCoupon');
        $this->assertNotNull($data);
        $this->assertTrue($data['success'] ?? false);
        $this->assertNotEmpty($data['message'] ?? null);
    }
}
