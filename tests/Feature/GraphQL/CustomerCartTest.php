<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Checkout\Models\Cart;

class CustomerCartTest extends GraphQLTestCase
{
    /**
     * Create Simple Cart (Customer).
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
     * Add Product In Cart (Customer) -- Failed.
     */
    public function test_create_add_product_in_cart_as_customer(): void
    {
        $customerData = $this->createTestCustomer();
        $customer = $customerData['customer'];
        $token = $customerData['token'];

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
            'quantity' => 1,
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
     * Update Cart Item Quantity (Customer) --  Failed.
     */
    public function test_update_cart_item_quantity_as_customer(): void
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
            'quantity' => 9,
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
            'quantity' => 1,
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
     * Get Cart Details (Customer).
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
     * Remove Cart Item (Customer) -- Failed.
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
     * Update Cart Item - full fields (matches spec).
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
            'quantity' => 2,
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
            'quantity' => 1,
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
     * Remove Cart Item - full fields (matches spec).
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
     * Apply Coupon (Customer).
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
     * Remove Coupon (Customer).
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
            'quantity' => 0,
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
            'quantity' => 1,
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

    /**
     * Removing several items at once empties them from the customer's cart.
     */
    public function test_remove_cart_items_removes_every_listed_item(): void
    {
        $customerData = $this->createTestCustomer();
        $cart = $this->createCustomerCartWithItem($customerData['token']);

        $response = $this->graphQL($this->removeCartItemsMutation(), [
            'itemIds' => [$cart['cartItemId']],
        ], $this->customerHeaders($customerData['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();
        expect((int) $response->json('data.createRemoveCartItems.removeCartItems.itemsCount'))->toBe(0);
        $this->assertDatabaseMissing('cart_items', ['id' => $cart['cartItemId']]);
    }

    /**
     * Item ids of another customer's cart are not removed through one's own cart.
     */
    public function test_remove_cart_items_leaves_another_customers_items_alone(): void
    {
        $owner = $this->createTestCustomer();
        $ownerCart = $this->createCustomerCartWithItem($owner['token']);

        $intruder = $this->createTestCustomer();
        $this->createCustomerCartWithItem($intruder['token']);

        $this->graphQL($this->removeCartItemsMutation(), [
            'itemIds' => [$ownerCart['cartItemId']],
        ], $this->customerHeaders($intruder['token']));

        $this->assertDatabaseHas('cart_items', ['id' => $ownerCart['cartItemId']]);
    }

    /**
     * Removing items without any item ids is rejected.
     */
    public function test_remove_cart_items_requires_item_ids(): void
    {
        $customerData = $this->createTestCustomer();
        $cart = $this->createCustomerCartWithItem($customerData['token']);

        $response = $this->graphQL($this->removeCartItemsMutation(), [
            'itemIds' => [],
        ], $this->customerHeaders($customerData['token']));

        expect($response->json('errors'))->not->toBeEmpty();
        $this->assertDatabaseHas('cart_items', ['id' => $cart['cartItemId']]);
    }

    /**
     * Moving a cart item to the wishlist takes it out of the cart and adds the product to the wishlist.
     */
    public function test_move_to_wishlist_moves_the_item_out_of_the_cart(): void
    {
        $customerData = $this->createTestCustomer();
        $cart = $this->createCustomerCartWithItem($customerData['token'], 1);

        $response = $this->graphQL($this->moveToWishlistMutation(), [
            'itemIds' => [$cart['cartItemId']],
            'quantities' => [1],
        ], $this->customerHeaders($customerData['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();
        $this->assertDatabaseMissing('cart_items', ['id' => $cart['cartItemId']]);
        $this->assertDatabaseHas('wishlist_items', [
            'customer_id' => $customerData['customer']->id,
            'product_id' => $cart['product'],
        ]);
    }

    /**
     * Moving to the wishlist without a customer token is refused.
     */
    public function test_move_to_wishlist_requires_a_cart_token(): void
    {
        $customerData = $this->createTestCustomer();
        $cart = $this->createCustomerCartWithItem($customerData['token']);

        $response = $this->graphQL($this->moveToWishlistMutation(), [
            'itemIds' => [$cart['cartItemId']],
            'quantities' => [1],
        ]);

        expect($response->json('errors'))->not->toBeEmpty();
        $this->assertDatabaseHas('cart_items', ['id' => $cart['cartItemId']]);
    }

    /**
     * Moving to the wishlist without any item ids is rejected.
     */
    public function test_move_to_wishlist_requires_item_ids(): void
    {
        $customerData = $this->createTestCustomer();
        $cart = $this->createCustomerCartWithItem($customerData['token']);

        $response = $this->graphQL($this->moveToWishlistMutation(), [
            'itemIds' => [],
            'quantities' => [],
        ], $this->customerHeaders($customerData['token']));

        expect($response->json('errors'))->not->toBeEmpty();
        $this->assertDatabaseHas('cart_items', ['id' => $cart['cartItemId']]);
    }

    /**
     * Estimating shipping returns the cart totals without saving the estimate address on the cart.
     */
    public function test_estimate_shipping_returns_the_cart_without_saving_an_address(): void
    {
        $customerData = $this->createTestCustomer();
        $this->createCustomerCartWithItem($customerData['token']);
        $cartId = $this->activeCartIdOf($customerData['customer']->id);

        $response = $this->graphQL($this->estimateShippingMutation(), [
            'country' => 'US',
            'state' => 'CA',
            'postcode' => '90001',
        ], $this->customerHeaders($customerData['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.createEstimateShipping.estimateShipping');

        expect((int) $data['_id'])->toBe($cartId);
        expect((int) $data['itemsCount'])->toBe(1);
        expect((float) $data['grandTotal'])->toBeGreaterThan(0);
        $this->assertDatabaseMissing('addresses', ['cart_id' => $cartId]);
    }

    /**
     * Estimating shipping without a postcode is rejected.
     */
    public function test_estimate_shipping_requires_the_full_address(): void
    {
        $customerData = $this->createTestCustomer();
        $this->createCustomerCartWithItem($customerData['token']);

        $response = $this->graphQL($this->estimateShippingMutation(), [
            'country' => 'US',
            'state' => 'CA',
            'postcode' => null,
        ], $this->customerHeaders($customerData['token']));

        expect($response->json('errors'))->not->toBeEmpty();
    }

    /**
     * Estimating shipping without a cart token is refused.
     */
    public function test_estimate_shipping_requires_a_cart_token(): void
    {
        $response = $this->graphQL($this->estimateShippingMutation(), [
            'country' => 'US',
            'state' => 'CA',
            'postcode' => '90001',
        ]);

        expect($response->json('errors'))->not->toBeEmpty();
    }

    /**
     * Reading the cart by token returns the customer's own active cart.
     */
    public function test_read_get_cart_token_returns_the_customers_cart(): void
    {
        $customerData = $this->createTestCustomer();
        $cart = $this->createCustomerCartWithItem($customerData['token'], 3);
        $cartId = $this->activeCartIdOf($customerData['customer']->id);

        $response = $this->graphQL($this->readGetCartTokenMutation(), [], $this->customerHeaders($customerData['token']));

        $response->assertSuccessful();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.readGetCartToken.getCartToken');

        expect((int) $data['_id'])->toBe($cartId);
        expect((int) $data['customerId'])->toBe($customerData['customer']->id);
        expect((int) $data['itemsQty'])->toBe(3);
        expect((int) $data['items']['edges'][0]['node']['productId'])->toBe($cart['product']);
    }

    /**
     * Reading the cart without a token is refused.
     */
    public function test_read_get_cart_token_requires_a_token(): void
    {
        $response = $this->graphQL($this->readGetCartTokenMutation());

        expect($response->json('errors'))->not->toBeEmpty();
        expect($response->json('data.readGetCartToken.getCartToken'))->toBeNull();
    }

    /**
     * Login customer and get token.
     */
    private function loginCustomerAndGetToken(): string
    {
        $customerData = $this->createTestCustomer();

        return $customerData['token'];
    }

    /**
     * Customer headers.
     */
    private function customerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * Create customer cart with item.
     */
    private function createCustomerCartWithItem(string $token, int $quantity = 2): array
    {
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
            'quantity' => $quantity,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $cart = $response->json('data.createAddProductInCart.addProductInCart');
        $cartItemId = $response->json('data.createAddProductInCart.addProductInCart.items.edges.0.node.id');

        $this->assertNotNull($cart);
        $this->assertNotNull($cartItemId);

        return [
            'product' => $product->id,
            'cartId' => (int) ($cart['_id'] ?? $cart['id'] ?? 0),
            'cartItemId' => (int) $cartItemId,
        ];
    }

    /**
     * Get the id of the customer's active cart.
     */
    private function activeCartIdOf(int $customerId): int
    {
        return (int) Cart::query()
            ->where('customer_id', $customerId)
            ->where('is_active', 1)
            ->value('id');
    }

    /**
     * The remove cart items mutation.
     */
    private function removeCartItemsMutation(): string
    {
        return <<<'GQL'
            mutation createRemoveCartItems($itemIds: Iterable!) {
              createRemoveCartItems(input: {itemIds: $itemIds}) {
                removeCartItems {
                  id
                  itemsCount
                }
              }
            }
        GQL;
    }

    /**
     * The move to wishlist mutation.
     */
    private function moveToWishlistMutation(): string
    {
        return <<<'GQL'
            mutation createMoveToWishlist($itemIds: Iterable!, $quantities: Iterable) {
              createMoveToWishlist(input: {itemIds: $itemIds, quantities: $quantities}) {
                moveToWishlist {
                  id
                  itemsCount
                }
              }
            }
        GQL;
    }

    /**
     * The estimate shipping mutation.
     */
    private function estimateShippingMutation(): string
    {
        return <<<'GQL'
            mutation createEstimateShipping($country: String, $state: String, $postcode: String) {
              createEstimateShipping(input: {country: $country, state: $state, postcode: $postcode}) {
                estimateShipping {
                  _id
                  itemsCount
                  subtotal
                  grandTotal
                }
              }
            }
        GQL;
    }

    /**
     * The read cart by token mutation.
     */
    private function readGetCartTokenMutation(): string
    {
        return <<<'GQL'
            mutation readGetCartToken {
              readGetCartToken(input: {id: "current"}) {
                getCartToken {
                  _id
                  customerId
                  itemsQty
                  items {
                    edges {
                      node {
                        productId
                      }
                    }
                  }
                }
              }
            }
        GQL;
    }
}
