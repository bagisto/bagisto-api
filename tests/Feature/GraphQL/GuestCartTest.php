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
}
