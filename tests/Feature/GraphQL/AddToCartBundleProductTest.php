<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;

class AddToCartBundleProductTest extends GraphQLTestCase
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

    private function createBundleProductPayload(): array
    {
        $bundle = $this->createBaseProduct('bundle');

        // Ensure inventory exists for the bundle product
        $this->ensureInventory($bundle, 50);

        // Disable manage stock for the bundle product so inventory check passes
        $this->upsertProductAttributeValue($bundle->id, 'manage_stock', 0, null, 'default');

        // Refresh bundle from database to get updated attribute values
        $bundle = Product::find($bundle->id);

        $optionId = (int) DB::table('product_bundle_options')->insertGetId([
            'product_id' => $bundle->id,
            'type' => 'checkbox',
            'is_required' => 1,
            'sort_order' => 1,
        ]);

        $optionProduct = $this->createBaseProduct('simple', [
            'sku' => 'TEST-BUNDLE-OPT-'.$bundle->id.'-1',
        ]);

        // Ensure inventory exists for the option product
        $this->ensureInventory($optionProduct, 50);

        // Disable manage stock for the option product so inventory check passes
        $this->upsertProductAttributeValue($optionProduct->id, 'manage_stock', 0, null, 'default');

        // Refresh option product from database to get updated attribute values
        $optionProduct = Product::find($optionProduct->id);

        // Also set price for the option product
        $this->upsertProductAttributeValue($optionProduct->id, 'price', 10.00, null, 'default');

        $bundleOptionProductId = DB::table('product_bundle_option_products')->insertGetId([
            'product_id' => $optionProduct->id,
            'product_bundle_option_id' => $optionId,
            'qty' => 1,
            'is_user_defined' => 1,
            'is_default' => 1,
            'sort_order' => 1,
        ]);

        $bundleOptions = [
            (string) $optionId => [(int) $bundleOptionProductId],
        ];

        $bundleOptionQty = [
            (string) $optionId => 1,
        ];

        return [
            'productId' => (int) $bundle->id,
            'bundleOptions' => json_encode($bundleOptions, JSON_UNESCAPED_SLASHES),
            'bundleOptionQty' => json_encode($bundleOptionQty, JSON_UNESCAPED_SLASHES),
        ];
    }

    public function test_create_add_bundle_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $payload = $this->createBundleProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $bundleOptions: String
              $bundleOptionQty: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  bundleOptions: $bundleOptions
                  bundleOptionQty: $bundleOptionQty
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
            'bundleOptions' => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding bundle product to cart: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    public function test_create_add_bundle_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $payload = $this->createBundleProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $bundleOptions: String
              $bundleOptionQty: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  bundleOptions: $bundleOptions
                  bundleOptionQty: $bundleOptionQty
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
            'bundleOptions' => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL returned errors while adding bundle product to cart as customer: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    /**
     * Create a bundle product with multiple bundle options and custom quantities.
     *
     * @param  int  $optionCount  Number of bundle options to create
     * @param  array<int,int>  $optionQtyOverrides  Keyed by 1-based option index => qty (defaults to 1)
     */
    /**
     * Create a bundle product with multiple options.
     *
     * Bundle option qty rules (from CartTokenProcessor):
     * - select/radio: qty CAN be changed by customer (can_change_qty = true)
     * - checkbox/multiselect: qty is FIXED by admin (can_change_qty = false)
     */
    private function createMultiOptionBundlePayload(int $optionCount = 4): array
    {
        $bundle = $this->createBaseProduct('bundle');
        $this->ensureInventory($bundle, 50);
        $this->upsertProductAttributeValue($bundle->id, 'manage_stock', 0, null, 'default');

        // Alternate between select (changeable qty) and checkbox (fixed qty)
        $types = ['select', 'checkbox', 'radio', 'checkbox'];

        $bundleOptions = [];
        $bundleOptionQty = [];

        for ($i = 1; $i <= $optionCount; $i++) {
            $type = $types[$i - 1] ?? 'select';

            $optionId = (int) DB::table('product_bundle_options')->insertGetId([
                'product_id' => $bundle->id,
                'type' => $type,
                'is_required' => 1,
                'sort_order' => $i,
            ]);

            $optionProduct = $this->createBaseProduct('simple', [
                'sku' => 'TEST-BNDL-OPT-'.$bundle->id.'-'.$i,
            ]);
            $this->ensureInventory($optionProduct, 50);
            $this->upsertProductAttributeValue($optionProduct->id, 'manage_stock', 0, null, 'default');
            $this->upsertProductAttributeValue($optionProduct->id, 'price', 10.00 * $i, null, 'default');

            $bundleOptionProductId = (int) DB::table('product_bundle_option_products')->insertGetId([
                'product_id' => $optionProduct->id,
                'product_bundle_option_id' => $optionId,
                'qty' => 1,
                'is_user_defined' => 1,
                'is_default' => 1,
                'sort_order' => 1,
            ]);

            $bundleOptions[(string) $optionId] = [$bundleOptionProductId];

            // Only set custom qty for select/radio (changeable), use default for checkbox/multiselect (fixed)
            $canChangeQty = in_array($type, ['select', 'radio']);
            $bundleOptionQty[(string) $optionId] = $canChangeQty ? ($i + 1) : 1;
        }

        return [
            'productId' => (int) $bundle->id,
            'bundleOptions' => json_encode($bundleOptions, JSON_UNESCAPED_SLASHES),
            'bundleOptionQty' => json_encode($bundleOptionQty, JSON_UNESCAPED_SLASHES),
            'optionCount' => $optionCount,
        ];
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
              $bundleOptions: String
              $bundleOptionQty: String
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  bundleOptions: $bundleOptions
                  bundleOptionQty: $bundleOptionQty
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
     * Add bundle product with multiple options to guest cart — full response.
     */
    public function test_add_bundle_product_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createMultiOptionBundlePayload(4);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'bundleOptions' => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
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
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertGreaterThan(0, (int) ($data['itemsQty'] ?? 0));
        $this->assertArrayHasKey('haveStockableItems', $data);

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
        $edges = $data['items']['edges'] ?? [];
        $this->assertNotEmpty($edges);

        $item = $edges[0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame($payload['productId'], (int) ($item['productId'] ?? 0));
        $this->assertSame('bundle', $item['type'] ?? '');
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
        $this->assertArrayHasKey('cursor', $edges[0]);
    }

    /**
     * Add bundle product with multiple options to customer cart — full response.
     */
    public function test_add_bundle_product_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $payload = $this->createMultiOptionBundlePayload(4);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'bundleOptions' => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
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
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
        $this->assertGreaterThan(0, (int) ($data['itemsQty'] ?? 0));
        $this->assertArrayHasKey('haveStockableItems', $data);

        // Verify cart totals
        $this->assertGreaterThan(0, (float) ($data['subtotal'] ?? 0));
        $this->assertGreaterThan(0, (float) ($data['grandTotal'] ?? 0));

        // Verify item is bundle type
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame('bundle', $item['type'] ?? '');
        $this->assertSame($payload['productId'], (int) ($item['productId'] ?? 0));
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
     * Bundle product with single option and default quantity.
     */
    public function test_add_bundle_product_single_option(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createBundleProductPayload();

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'bundleOptions' => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));

        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item);
        $this->assertSame('bundle', $item['type'] ?? '');
        $this->assertSame(1, (int) ($item['quantity'] ?? 0));
    }
}
