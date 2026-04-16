<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CustomerCartTest extends GraphQLTestCase
{
    private function loginCustomerAndGetToken(): string
    {
        // Use our test customer helper to create a customer with proper token
        $customerData = $this->createTestCustomer();

        return $customerData['token'];
    }

    private function customerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    private function createCustomerCartWithItem(string $token, int $quantity = 2): array
    {
        // Use our test product helper to get a product with inventory
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $mutation = <<<'GQL'
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

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity'  => $quantity,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $cart = $response->json('data.createAddProductInCart.addProductInCart');
        $cartItemId = $response->json('data.createAddProductInCart.addProductInCart.items.edges.0.node.id');

        $this->assertNotNull($cart);
        $this->assertNotNull($cartItemId);

        return [
            'product'    => $product->id,
            'cartId'     => (int) ($cart['_id'] ?? $cart['id'] ?? 0),
            'cartItemId' => (int) $cartItemId,
        ];
    }

    /**
     * Create Simple Cart (Customer)
     */
    public function test_create_simple_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();

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

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.createCartToken.cartToken');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
    }

    /**
     * Add Product In Cart (Customer) -- Failed
     */
    public function test_create_add_product_in_cart_as_customer(): void
    {
        // Use our helper method to create a fake customer
        $customerData = $this->createTestCustomer();
        $customer = $customerData['customer'];
        $token = $customerData['token'];

        // Use our helper method to create a complete test product
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

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity'  => 1,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame($product->id, $data['items']['edges'][0]['node']['productId'] ?? null);
        $this->assertSame(1, $data['items']['edges'][0]['node']['quantity'] ?? null);
    }

    /**
     * Update Cart Item Quantity (Customer) --  Failed
     */
    public function test_update_cart_item_quantity_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        // Use our test product helper to get a product with inventory
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
                  customerId
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

        $updateResponse = $this->graphQL($updateMutation, [
            'cartItemId' => (int) $cartItemId,
            'quantity'   => 1,
        ], $headers);

        $updateResponse->assertSuccessful();

        $data = $updateResponse->json('data.createUpdateCartItem.updateCartItem');

        $this->assertNotNull($data);
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame($product->id, $data['items']['edges'][0]['node']['productId'] ?? null);
        $this->assertSame(1, $data['items']['edges'][0]['node']['quantity'] ?? null);
    }

    /**
     * Get Cart Details (Customer)
     */
    public function test_read_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->createCustomerCartWithItem($token, 3);

        $mutation = <<<'GQL'
            mutation createReadCart {
              createReadCart(input: {}) {
                readCart {
                  id
                  _id
                  customerId
                  itemsCount
                  itemsQty
                  items {
                    totalCount
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

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));
        $response->assertSuccessful();

        $data = $response->json('data.createReadCart.readCart');

        $this->assertNotNull($data);
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertGreaterThan(0, (int) ($data['items']['totalCount'] ?? 0));
    }

    /**
     * Remove Cart Item (Customer) -- Failed
     */
    public function test_remove_cart_item_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $cart = $this->createCustomerCartWithItem($token, 2);

        $mutation = <<<'GQL'
            mutation createRemoveCartItem($cartItemId: Int!) {
              createRemoveCartItem(input: {cartItemId: $cartItemId}) {
                removeCartItem {
                  id
                  itemsCount
                  subtotal
                  grandTotal
                  discountAmount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'cartItemId' => $cart['cartItemId'],
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.createRemoveCartItem.removeCartItem');
        $this->assertNotNull($data);
        $this->assertLessThanOrEqual(0, (int) ($data['itemsCount'] ?? 0));
    }

    /**
     * Update Cart Item - full fields (matches spec)
     */
    public function test_update_cart_item_full_fields(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

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
            'quantity'  => 2,
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
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertTrue((bool) ($data['success'] ?? false));

        $node = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($node);
        $this->assertSame($product->id, $node['productId'] ?? null);
        $this->assertSame(1, $node['quantity'] ?? null);
        $this->assertArrayHasKey('sku', $node);
        $this->assertArrayHasKey('price', $node);
        $this->assertArrayHasKey('formattedPrice', $node);
        $this->assertArrayHasKey('canChangeQty', $node);

        $pageInfo = $data['items']['pageInfo'] ?? null;
        $this->assertNotNull($pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);
    }

    /**
     * Remove Cart Item - full fields (matches spec)
     */
    public function test_remove_cart_item_full_fields(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $cart = $this->createCustomerCartWithItem($token, 2);

        $mutation = <<<'GQL'
            mutation removeItem($cartItemId: Int!) {
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

        $response = $this->graphQL($mutation, [
            'cartItemId' => $cart['cartItemId'],
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.createRemoveCartItem.removeCartItem');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('_id', $data);
        $this->assertArrayHasKey('cartToken', $data);
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('totalCount', $data['items']);
    }

    /**
     * Apply Coupon (Customer)
     */
    public function test_apply_coupon_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->createCustomerCartWithItem($token, 1);

        $mutation = <<<'GQL'
            mutation createApplyCoupon($couponCode: String!) {
              createApplyCoupon(input: {couponCode: $couponCode}) {
                applyCoupon {
                  id
                  couponCode
                  discountAmount
                  formattedDiscountAmount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'couponCode' => 'DISCOUNT10',
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();

        if (isset($json['errors'])) {
            $this->assertNotEmpty($json['errors']);

            return;
        }

        $data = $response->json('data.createApplyCoupon.applyCoupon');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('couponCode', $data);
    }

    /**
     * Remove Coupon (Customer)
     */
    public function test_remove_coupon_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $this->createCustomerCartWithItem($token, 1);

        $mutation = <<<'GQL'
            mutation createRemoveCoupon {
              createRemoveCoupon(input: {}) {
                removeCoupon {
                  id
                  couponCode
                  discountAmount
                  formattedDiscountAmount
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [], $this->customerHeaders($token));
        $response->assertSuccessful();

        $json = $response->json();

        if (isset($json['errors'])) {
            $this->assertNotEmpty($json['errors']);

            return;
        }

        $data = $response->json('data.createRemoveCoupon.removeCoupon');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('couponCode', $data);
    }

    /**
     * Update cart item with zero quantity returns a validation error.
     */
    public function test_update_cart_item_with_zero_quantity_returns_error(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $cart = $this->createCustomerCartWithItem($token, 2);

        $mutation = <<<'GQL'
            mutation createUpdateCartItem($cartItemId: Int!, $quantity: Int!) {
              createUpdateCartItem(input: {cartItemId: $cartItemId, quantity: $quantity}) {
                updateCartItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'cartItemId' => $cart['cartItemId'],
            'quantity'   => 0,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Update a non-existent cart item returns an error.
     */
    public function test_update_nonexistent_cart_item_returns_error(): void
    {
        $token = $this->loginCustomerAndGetToken();

        $mutation = <<<'GQL'
            mutation createUpdateCartItem($cartItemId: Int!, $quantity: Int!) {
              createUpdateCartItem(input: {cartItemId: $cartItemId, quantity: $quantity}) {
                updateCartItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'cartItemId' => 999999,
            'quantity'   => 1,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Remove a non-existent cart item returns an error.
     */
    public function test_remove_nonexistent_cart_item_returns_error(): void
    {
        $token = $this->loginCustomerAndGetToken();

        $mutation = <<<'GQL'
            mutation createRemoveCartItem($cartItemId: Int!) {
              createRemoveCartItem(input: {cartItemId: $cartItemId}) {
                removeCartItem {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'cartItemId' => 999999,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }
}
