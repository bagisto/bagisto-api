<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;

class AddToCartSimpleProductTest extends GraphQLTestCase
{
    private function loginCustomerAndGetToken(): string
    {
        $customerData = $this->createTestCustomer();

        return $customerData['token'];
    }

    private function customerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * Get guest cart token from the createCart mutation response.
     */
    private function getGuestCartToken(): string
    {
        $mutation = <<<'GQL'
            mutation createCart {
              createCartToken(input: {}) {
                cartToken {
                  cartToken
                  success
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);
        $response->assertSuccessful();

        $data = $response->json('data.createCartToken.cartToken');

        $this->assertNotNull($data, 'cartToken response is null');
        $this->assertTrue((bool) ($data['success'] ?? false));

        $token = $data['cartToken'] ?? null;
        $this->assertNotEmpty($token, 'guest cart token is missing');

        return $token;
    }

    private function guestHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    /**
     * Create a simple product with inventory ready for add-to-cart.
     */
    private function createSimpleProduct(float $price = 17.0): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'TEST-SIMPLE-'.uniqid(),
        ]);

        $this->upsertProductAttributeValue($product->id, 'price', $price, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'weight', 1.0, null, 'default');
        $this->ensureInventory($product, 50);

        return $product;
    }

    /**
     * Full add-to-cart mutation matching the API spec.
     */
    private function addToCartMutation(): string
    {
        return <<<'GQL'
            mutation createAddProductInCart(
                $productId: Int!
                $quantity: Int!
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                }
              ) {
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
    }

    /**
     * Add simple product to cart as a guest user.
     */
    public function test_add_simple_product_to_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $product = $this->createSimpleProduct(17.0);

        $response = $this->graphQL($this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors adding simple product to guest cart: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));
        $this->assertTrue((bool) ($data['haveStockableItems'] ?? false));

        // Verify cart totals
        $this->assertGreaterThan(0, (float) ($data['subtotal'] ?? 0));
        $this->assertGreaterThan(0, (float) ($data['grandTotal'] ?? 0));
        $this->assertSame((float) $data['subtotal'], (float) $data['baseSubtotal']);
        $this->assertNotNull($data['formattedSubtotal']);
        $this->assertNotNull($data['formattedGrandTotal']);

        // Verify cart item
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame($product->id, (int) ($item['productId'] ?? 0));
        $this->assertSame(1, (int) ($item['quantity'] ?? 0));
        $this->assertSame('simple', $item['type'] ?? '');
        $this->assertGreaterThan(0, (float) ($item['price'] ?? 0));
        $this->assertSame((float) $item['price'], (float) $item['basePrice']);
        $this->assertGreaterThan(0, (float) ($item['total'] ?? 0));
        $this->assertNotNull($item['formattedPrice']);
        $this->assertNotNull($item['formattedTotal']);
        $this->assertNotNull($item['sku']);
        $this->assertNotNull($item['name']);
        $this->assertNotNull($item['productUrlKey']);
        $this->assertTrue((bool) ($item['canChangeQty'] ?? false));

        // Verify pagination info
        $pageInfo = $data['items']['pageInfo'] ?? null;
        $this->assertNotNull($pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);

        $this->assertSame(1, (int) ($data['items']['totalCount'] ?? 0));
    }

    /**
     * Add simple product to cart as a logged-in customer.
     */
    public function test_add_simple_product_to_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $product = $this->createSimpleProduct(17.0);

        $response = $this->graphQL($this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors adding simple product to customer cart: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));
        $this->assertTrue((bool) ($data['haveStockableItems'] ?? false));

        // Verify cart totals
        $this->assertGreaterThan(0, (float) ($data['subtotal'] ?? 0));
        $this->assertGreaterThan(0, (float) ($data['grandTotal'] ?? 0));

        // Verify cart item
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame($product->id, (int) ($item['productId'] ?? 0));
        $this->assertSame(1, (int) ($item['quantity'] ?? 0));
        $this->assertSame('simple', $item['type'] ?? '');
    }

    /**
     * Add simple product with quantity greater than 1.
     */
    public function test_add_simple_product_with_multiple_quantity(): void
    {
        $token = $this->getGuestCartToken();
        $product = $this->createSimpleProduct(25.0);

        $response = $this->graphQL($this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 3,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertSame(3, (int) ($data['itemsQty'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));

        // Verify item quantity and total
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item);
        $this->assertSame(3, (int) ($item['quantity'] ?? 0));
        $this->assertSame((float) $item['price'] * 3, (float) $item['total']);
    }

    /**
     * Cart response contains all expected formatted fields.
     */
    public function test_add_simple_product_cart_has_all_formatted_fields(): void
    {
        $token = $this->getGuestCartToken();
        $product = $this->createSimpleProduct(10.0);

        $response = $this->graphQL($this->addToCartMutation(), [
            'productId' => $product->id,
            'quantity' => 1,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.createAddProductInCart.addProductInCart');
        $this->assertNotNull($data);

        // Verify all formatted fields exist
        $this->assertArrayHasKey('formattedSubtotal', $data);
        $this->assertArrayHasKey('formattedDiscountAmount', $data);
        $this->assertArrayHasKey('formattedTaxAmount', $data);
        $this->assertArrayHasKey('formattedShippingAmount', $data);
        $this->assertArrayHasKey('formattedGrandTotal', $data);
        $this->assertArrayHasKey('formattedSubTotalInclTax', $data);
        $this->assertArrayHasKey('formattedTaxTotal', $data);
        $this->assertArrayHasKey('formattedShippingAmountInclTax', $data);

        // Verify item formatted fields
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item);
        $this->assertArrayHasKey('formattedPrice', $item);
        $this->assertArrayHasKey('formattedTotal', $item);
        $this->assertArrayHasKey('formattedPriceInclTax', $item);
        $this->assertArrayHasKey('formattedTotalInclTax', $item);
    }

    /**
     * Adding an invalid product ID returns appropriate error.
     */
    public function test_add_invalid_product_id_to_cart(): void
    {
        $token = $this->getGuestCartToken();

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => 999999,
            'quantity' => 1,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        // Either returns success=false or null data for invalid product
        if ($data !== null) {
            $this->assertFalse((bool) ($data['success'] ?? true));
        }
    }

    /**
     * Adding a disabled product returns a proper error message (not 500).
     */
    public function test_add_disabled_product_to_cart_returns_error(): void
    {
        $this->seedRequiredData();

        $token = $this->getGuestCartToken();

        // Create a product and mark it as disabled via the attribute system
        $product = Product::factory()->create();
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity' => 1,
        ], $this->guestHeaders($token));

        // Must be a 200 with a GraphQL error — not a 500
        $response->assertSuccessful();

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Expected a GraphQL error for disabled product, got none');

        // Error message should be meaningful, not an unhandled exception trace
        $message = $errors[0]['message'] ?? '';
        $this->assertNotEmpty($message);
        $this->assertStringNotContainsStringIgnoringCase('internal server error', $message);
    }

    /**
     * Zero quantity returns a validation error.
     */
    public function test_add_product_with_zero_quantity_returns_error(): void
    {
        $token = $this->getGuestCartToken();

        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity' => 0,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }

    /**
     * Add to cart without Authorization header returns an error.
     */
    public function test_add_product_without_cart_token_returns_error(): void
    {
        $productData = $this->createTestProduct();
        $product = $productData['product'];

        $mutation = <<<'GQL'
            mutation createAddProductInCart($productId: Int!, $quantity: Int!) {
              createAddProductInCart(input: {productId: $productId, quantity: $quantity}) {
                addProductInCart {
                  success
                  message
                }
              }
            }
        GQL;

        // No Authorization header at all
        $response = $this->graphQL($mutation, [
            'productId' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertSuccessful();
        $this->assertNotEmpty($response->json('errors'));
    }
}
