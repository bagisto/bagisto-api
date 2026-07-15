<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class AddToCartGroupedProductTest extends GraphQLTestCase
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

    private function createGroupedProductPayload(int $associatedCount = 2): array
    {
        $grouped = $this->createBaseProduct('grouped');
        $this->ensureInventory($grouped, 50);

        $qtyMap = [];

        for ($i = 1; $i <= $associatedCount; $i++) {
            $associated = $this->createBaseProduct('simple', [
                'sku' => 'TEST-GROUPED-ASSOC-'.$grouped->id.'-'.$i,
            ]);
            $this->ensureInventory($associated, 50);

            // Disable manage stock for the associated product so inventory check passes
            $this->upsertProductAttributeValue($associated->id, 'manage_stock', 0, null, 'default');

            DB::table('product_grouped_products')->insert([
                'product_id' => $grouped->id,
                'associated_product_id' => $associated->id,
                'qty' => 1,
                'sort_order' => $i,
            ]);

            $qtyMap[(string) $associated->id] = 1;
        }

        return [
            'productId' => (int) $grouped->id,
            'groupedQty' => json_encode($qtyMap, JSON_UNESCAPED_SLASHES),
        ];
    }

    public function test_create_add_grouped_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $payload = $this->createGroupedProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $groupedQty: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  groupedQty: $groupedQty
                }
              ) {
                addProductInCart {
                  id
                  cartToken
                  success
                  isGuest
                  itemsCount
                  items {
                    edges {
                      node {
                        id
                        productId
                        quantity
                        type
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'groupedQty' => $payload['groupedQty'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding grouped product to cart: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    public function test_create_add_grouped_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $payload = $this->createGroupedProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $groupedQty: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  groupedQty: $groupedQty
                }
              ) {
                addProductInCart {
                  id
                  customerId
                  success
                  isGuest
                  itemsCount
                  items {
                    edges {
                      node {
                        id
                        productId
                        quantity
                        type
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'groupedQty' => $payload['groupedQty'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding grouped product to cart as customer: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    /**
     * Full add-to-cart mutation matching the complete API spec.
     */
    private function fullAddToCartMutation(): string
    {
        return <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $groupedQty: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  groupedQty: $groupedQty
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
                        options
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
     * Add grouped product to guest cart with full response fields.
     */
    public function test_add_grouped_product_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createGroupedProductPayload(3);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'groupedQty' => $payload['groupedQty'],
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));

        // Grouped product adds each associated product as a separate cart item
        $this->assertSame(3, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(3, (int) ($data['itemsQty'] ?? 0));
        $this->assertTrue((bool) ($data['haveStockableItems'] ?? false));

        // Verify cart totals
        $this->assertGreaterThan(0, (float) ($data['subtotal'] ?? 0));
        $this->assertGreaterThan(0, (float) ($data['grandTotal'] ?? 0));
        $this->assertSame((float) $data['subtotal'], (float) $data['baseSubtotal']);
        $this->assertSame((float) $data['grandTotal'], (float) $data['baseGrandTotal']);
        $this->assertNotNull($data['formattedSubtotal']);
        $this->assertNotNull($data['formattedGrandTotal']);
        $this->assertNotNull($data['formattedDiscountAmount']);
        $this->assertNotNull($data['formattedTaxAmount']);
        $this->assertNotNull($data['formattedShippingAmount']);

        // Verify tax/shipping inclusive fields
        $this->assertArrayHasKey('subTotalInclTax', $data);
        $this->assertArrayHasKey('baseSubTotalInclTax', $data);
        $this->assertArrayHasKey('formattedSubTotalInclTax', $data);
        $this->assertArrayHasKey('taxTotal', $data);
        $this->assertArrayHasKey('formattedTaxTotal', $data);
        $this->assertArrayHasKey('shippingAmountInclTax', $data);
        $this->assertArrayHasKey('baseShippingAmountInclTax', $data);
        $this->assertArrayHasKey('formattedShippingAmountInclTax', $data);

        // Verify nullable fields are present
        $this->assertArrayHasKey('couponCode', $data);
        $this->assertArrayHasKey('paymentMethod', $data);
        $this->assertArrayHasKey('paymentMethodTitle', $data);
        $this->assertArrayHasKey('sessionToken', $data);

        // Verify cart items — each associated product becomes its own item
        $this->assertSame(3, (int) ($data['items']['totalCount'] ?? 0));
        $edges = $data['items']['edges'] ?? [];
        $this->assertCount(3, $edges);

        foreach ($edges as $index => $edge) {
            $item = $edge['node'] ?? null;
            $this->assertNotNull($item, "Cart item node at index {$index} is missing");
            $this->assertSame(1, (int) ($item['quantity'] ?? 0));
            $this->assertSame('simple', $item['type'] ?? '');
            $this->assertGreaterThan(0, (float) ($item['price'] ?? 0));
            $this->assertSame((float) $item['price'], (float) $item['basePrice']);
            $this->assertGreaterThan(0, (float) ($item['total'] ?? 0));
            $this->assertSame((float) $item['total'], (float) $item['baseTotal']);
            $this->assertNotNull($item['name']);
            $this->assertNotNull($item['sku']);
            $this->assertNotNull($item['formattedPrice']);
            $this->assertNotNull($item['formattedTotal']);
            $this->assertNotNull($item['formattedPriceInclTax']);
            $this->assertNotNull($item['formattedTotalInclTax']);
            $this->assertArrayHasKey('priceInclTax', $item);
            $this->assertArrayHasKey('basePriceInclTax', $item);
            $this->assertArrayHasKey('totalInclTax', $item);
            $this->assertArrayHasKey('baseTotalInclTax', $item);
            $this->assertArrayHasKey('productUrlKey', $item);
            $this->assertArrayHasKey('canChangeQty', $item);
            $this->assertArrayHasKey('options', $item);

            // Verify cursor on edge
            $this->assertArrayHasKey('cursor', $edge);
        }

        // Verify pagination info
        $pageInfo = $data['items']['pageInfo'] ?? null;
        $this->assertNotNull($pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);
    }

    /**
     * Add grouped product to customer cart with full response fields.
     */
    public function test_add_grouped_product_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $payload = $this->createGroupedProductPayload(2);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'groupedQty' => $payload['groupedQty'],
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertNotNull($data['channelId'] ?? null);
        $this->assertSame(2, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(2, (int) ($data['itemsQty'] ?? 0));
        $this->assertTrue((bool) ($data['haveStockableItems'] ?? false));

        // Verify cart totals
        $this->assertGreaterThan(0, (float) ($data['subtotal'] ?? 0));
        $this->assertGreaterThan(0, (float) ($data['grandTotal'] ?? 0));

        // Verify items
        $edges = $data['items']['edges'] ?? [];
        $this->assertCount(2, $edges);

        foreach ($edges as $edge) {
            $item = $edge['node'] ?? null;
            $this->assertNotNull($item);
            $this->assertSame('simple', $item['type'] ?? '');
            $this->assertNotNull($item['name']);
            $this->assertNotNull($item['sku']);
            $this->assertGreaterThan(0, (float) ($item['price'] ?? 0));
            $this->assertArrayHasKey('formattedPrice', $item);
            $this->assertArrayHasKey('formattedTotal', $item);
            $this->assertArrayHasKey('priceInclTax', $item);
            $this->assertArrayHasKey('formattedPriceInclTax', $item);
            $this->assertArrayHasKey('totalInclTax', $item);
            $this->assertArrayHasKey('formattedTotalInclTax', $item);
            $this->assertArrayHasKey('options', $item);
        }
    }

    /**
     * Grouped product with varying quantities per associated product.
     */
    public function test_add_grouped_product_with_varying_quantities(): void
    {
        $token = $this->getGuestCartToken();

        // Create grouped product with 2 associated products manually to control quantities
        $grouped = $this->createBaseProduct('grouped');
        $this->ensureInventory($grouped, 50);

        $assoc1 = $this->createBaseProduct('simple', [
            'sku' => 'TEST-GRP-VAR-'.$grouped->id.'-1',
        ]);
        $this->ensureInventory($assoc1, 50);
        $this->upsertProductAttributeValue($assoc1->id, 'manage_stock', 0, null, 'default');

        $assoc2 = $this->createBaseProduct('simple', [
            'sku' => 'TEST-GRP-VAR-'.$grouped->id.'-2',
        ]);
        $this->ensureInventory($assoc2, 50);
        $this->upsertProductAttributeValue($assoc2->id, 'manage_stock', 0, null, 'default');

        DB::table('product_grouped_products')->insert([
            ['product_id' => $grouped->id, 'associated_product_id' => $assoc1->id, 'qty' => 1, 'sort_order' => 1],
            ['product_id' => $grouped->id, 'associated_product_id' => $assoc2->id, 'qty' => 1, 'sort_order' => 2],
        ]);

        // Assign different quantities: 2 of assoc1, 3 of assoc2
        $groupedQty = json_encode([
            (string) $assoc1->id => 2,
            (string) $assoc2->id => 3,
        ], JSON_UNESCAPED_SLASHES);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => (int) $grouped->id,
            'quantity' => 1,
            'groupedQty' => $groupedQty,
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));

        // 2 distinct associated products as cart items
        $this->assertSame(2, (int) ($data['itemsCount'] ?? 0));

        // Total quantity = 2 + 3 = 5
        $this->assertSame(5, (int) ($data['itemsQty'] ?? 0));

        $edges = $data['items']['edges'] ?? [];
        $this->assertCount(2, $edges);

        // Collect quantities keyed by productId
        $itemQuantities = [];
        foreach ($edges as $edge) {
            $item = $edge['node'];
            $itemQuantities[(int) $item['productId']] = (int) $item['quantity'];
            $this->assertSame((float) $item['price'] * (int) $item['quantity'], (float) $item['total']);
        }

        $this->assertSame(2, $itemQuantities[$assoc1->id] ?? 0);
        $this->assertSame(3, $itemQuantities[$assoc2->id] ?? 0);
    }
}
