<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class AddToCartConfigurableProductTest extends GraphQLTestCase
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
     * Find a configurable product + in-stock variant and build the payload expected by the GraphQL mutation.
     *
     * @return array{productId:int,selectedConfigurableOption:int,superAttribute:array<int,array{key:string,value:int}>}
     */
    private function createConfigurableProductPayload(): array
    {
        $this->seedRequiredData();

        $attributes = Attribute::query()
            ->where('is_configurable', 1)
            ->where('type', 'select')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($attributes->isEmpty()) {
            $this->markTestSkipped('No configurable select attributes found. Run Bagisto seeders for attributes like color/size.');
        }

        $parent = $this->createBaseProduct('configurable', [
            'sku' => 'TEST-CONFIG-PARENT-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);
        $this->upsertProductAttributeValue($parent->id, 'weight', 1.5, null, 'default');

        $child = $this->createBaseProduct('simple', [
            'sku' => 'TEST-CONFIG-CHILD-'.uniqid(),
            'parent_id' => $parent->id,
        ]);
        $this->ensureInventory($child, 50);

        // Disable manage stock for the child product so inventory check passes
        $this->upsertProductAttributeValue($child->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($child->id, 'weight', 1.5, null, 'default');

        DB::table('product_relations')->insert([
            'parent_id' => $parent->id,
            'child_id' => $child->id,
        ]);

        $superAttribute = [];

        foreach ($attributes as $attribute) {
            $attributeId = (int) $attribute->id;
            $optionId = $this->createAttributeOption($attributeId, 'Opt-'.$child->sku);

            DB::table('product_super_attributes')->insert([
                'product_id' => $parent->id,
                'attribute_id' => $attributeId,
            ]);

            $this->upsertProductAttributeValue($child->id, (string) $attribute->code, $optionId, null, 'default');

            $superAttribute[] = [
                'key' => (string) $attributeId,
                'value' => (int) $optionId,
            ];
        }

        return [
            'productId' => (int) $parent->id,
            'selectedConfigurableOption' => (int) $child->id,
            'superAttribute' => $superAttribute,
        ];
    }

    /**
     * Add Configurable Product In Cart (Guest)
     */
    public function test_create_add_configurable_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $payload = $this->createConfigurableProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $selectedConfigurableOption: Int!
              $superAttribute: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  selectedConfigurableOption: $selectedConfigurableOption
                  superAttribute: $superAttribute
                }
              ) {
                addProductInCart {
                  id
                  _id
                  cartToken
                  success
                  message
                  isGuest
                  itemsQty
                  itemsCount
                  haveStockableItems
                  items {
                    totalCount
                    edges {
                      node {
                        id
                        productId
                        sku
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
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute' => $payload['superAttribute'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding configurable product to cart: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));

        $firstItem = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($firstItem, 'Cart item node is missing');
        $this->assertSame(1, (int) ($firstItem['quantity'] ?? 0));

        $productId = (int) ($firstItem['productId'] ?? 0);
        $this->assertTrue(
            in_array($productId, [(int) $payload['productId'], (int) $payload['selectedConfigurableOption']], true),
            'Cart item productId did not match either the configurable parent or selected variant.'
        );
    }

    public function test_create_add_configurable_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $payload = $this->createConfigurableProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $selectedConfigurableOption: Int!
              $superAttribute: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  selectedConfigurableOption: $selectedConfigurableOption
                  superAttribute: $superAttribute
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
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute' => $payload['superAttribute'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding configurable product to cart as customer: '.json_encode($json['errors']));
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
              $selectedConfigurableOption: Int!
              $superAttribute: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  selectedConfigurableOption: $selectedConfigurableOption
                  superAttribute: $superAttribute
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
                        baseImage
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
     * Add configurable product to guest cart with full response fields.
     */
    public function test_add_configurable_product_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createConfigurableProductPayload();

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute' => $payload['superAttribute'],
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
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));

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

        // Verify cart item
        $this->assertSame(1, (int) ($data['items']['totalCount'] ?? 0));

        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');

        $productId = (int) ($item['productId'] ?? 0);
        $this->assertTrue(
            in_array($productId, [$payload['productId'], $payload['selectedConfigurableOption']], true),
            'Cart item productId did not match either configurable parent or selected variant.'
        );

        $this->assertSame(1, (int) ($item['quantity'] ?? 0));
        $this->assertSame('configurable', $item['type'] ?? '');
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
        $this->assertArrayHasKey('productUrlKey', $item);
        $this->assertArrayHasKey('canChangeQty', $item);
        $this->assertArrayHasKey('baseImage', $item);
        $this->assertArrayHasKey('options', $item);

        // Verify pagination info
        $pageInfo = $data['items']['pageInfo'] ?? null;
        $this->assertNotNull($pageInfo);
        $this->assertArrayHasKey('startCursor', $pageInfo);
        $this->assertArrayHasKey('endCursor', $pageInfo);
        $this->assertArrayHasKey('hasNextPage', $pageInfo);
        $this->assertArrayHasKey('hasPreviousPage', $pageInfo);

        // Verify cursor on edge
        $this->assertArrayHasKey('cursor', $data['items']['edges'][0]);
    }

    /**
     * Add configurable product to customer cart with full response fields.
     */
    public function test_add_configurable_product_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $payload = $this->createConfigurableProductPayload();

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute' => $payload['superAttribute'],
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
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));

        // Verify cart totals
        $this->assertGreaterThan(0, (float) ($data['subtotal'] ?? 0));
        $this->assertGreaterThan(0, (float) ($data['grandTotal'] ?? 0));

        // Verify item is configurable type
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame('configurable', $item['type'] ?? '');
        $this->assertNotNull($item['name']);
        $this->assertNotNull($item['sku']);
        $this->assertGreaterThan(0, (float) ($item['price'] ?? 0));

        // Verify item-level formatted and inclusive fields
        $this->assertArrayHasKey('formattedPrice', $item);
        $this->assertArrayHasKey('formattedTotal', $item);
        $this->assertArrayHasKey('priceInclTax', $item);
        $this->assertArrayHasKey('basePriceInclTax', $item);
        $this->assertArrayHasKey('formattedPriceInclTax', $item);
        $this->assertArrayHasKey('totalInclTax', $item);
        $this->assertArrayHasKey('baseTotalInclTax', $item);
        $this->assertArrayHasKey('formattedTotalInclTax', $item);
        $this->assertArrayHasKey('baseImage', $item);
        $this->assertArrayHasKey('options', $item);
    }

    /**
     * Add configurable product with quantity greater than 1.
     */
    public function test_add_configurable_product_with_multiple_quantity(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createConfigurableProductPayload();

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 3,
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute' => $payload['superAttribute'],
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

        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item);
        $this->assertSame(3, (int) ($item['quantity'] ?? 0));
        $this->assertSame((float) $item['price'] * 3, (float) $item['total']);
    }
}
