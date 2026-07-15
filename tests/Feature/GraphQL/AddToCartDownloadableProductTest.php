<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class AddToCartDownloadableProductTest extends GraphQLTestCase
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

    private function createDownloadableProductPayload(int $linksCount = 2): array
    {
        $product = $this->createBaseProduct('downloadable');
        $this->ensureInventory($product, 50);

        $links = [];

        for ($i = 1; $i <= $linksCount; $i++) {
            $links[] = (int) DB::table('product_downloadable_links')->insertGetId([
                'product_id' => $product->id,
                'url' => 'https://example.com/download/'.$product->sku.'/'.$i,
                'file' => null,
                'file_name' => null,
                'type' => 'url',
                'price' => 0,
                'downloads' => 0,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'productId' => (int) $product->id,
            'links' => $links,
        ];
    }

    public function test_create_add_downloadable_product_in_cart_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $headers = $this->guestHeaders($token);

        $payload = $this->createDownloadableProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $links: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  links: $links
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
            'links' => $payload['links'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL returned errors while adding downloadable product to cart.');

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertGreaterThan(0, (int) ($data['itemsCount'] ?? 0));
    }

    public function test_create_add_downloadable_product_in_cart_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $headers = $this->customerHeaders($token);

        $payload = $this->createDownloadableProductPayload();

        $mutation = <<<'GQL'
            mutation createAddProductInCart(
              $productId: Int!
              $quantity: Int!
              $links: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  links: $links
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
            'links' => $payload['links'],
        ], $headers);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL returned errors while adding downloadable product to cart as customer.');

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
              $links: Iterable
            ) {
              createAddProductInCart(
                input: {
                  productId: $productId
                  quantity: $quantity
                  links: $links
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
     * Add downloadable product to guest cart with full response fields.
     */
    public function test_add_downloadable_product_full_response_as_guest(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createDownloadableProductPayload(2);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'links' => $payload['links'],
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertTrue((bool) ($data['isGuest'] ?? false));
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));

        // Downloadable products are not stockable
        $this->assertFalse((bool) ($data['haveStockableItems'] ?? true));

        // Verify cart totals
        $this->assertGreaterThanOrEqual(0, (float) ($data['subtotal'] ?? -1));
        $this->assertSame((float) $data['subtotal'], (float) $data['baseSubtotal']);
        $this->assertGreaterThanOrEqual(0, (float) ($data['grandTotal'] ?? -1));
        $this->assertSame((float) $data['grandTotal'], (float) $data['baseGrandTotal']);
        $this->assertNotNull($data['formattedSubtotal']);
        $this->assertNotNull($data['formattedGrandTotal']);
        $this->assertNotNull($data['formattedDiscountAmount']);
        $this->assertNotNull($data['formattedTaxAmount']);
        $this->assertNotNull($data['formattedShippingAmount']);

        // Shipping should be 0 for downloadable
        $this->assertSame(0.0, (float) ($data['shippingAmount'] ?? -1));
        $this->assertSame(0.0, (float) ($data['baseShippingAmount'] ?? -1));

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
        $this->assertSame($payload['productId'], (int) ($item['productId'] ?? 0));
        $this->assertSame(1, (int) ($item['quantity'] ?? 0));
        $this->assertSame('downloadable', $item['type'] ?? '');
        $this->assertGreaterThanOrEqual(0, (float) ($item['price'] ?? -1));
        $this->assertSame((float) $item['price'], (float) $item['basePrice']);
        $this->assertGreaterThanOrEqual(0, (float) ($item['total'] ?? -1));
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
     * Add downloadable product to customer cart with full response fields.
     */
    public function test_add_downloadable_product_full_response_as_customer(): void
    {
        $token = $this->loginCustomerAndGetToken();
        $payload = $this->createDownloadableProductPayload(2);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'links' => $payload['links'],
        ], $this->customerHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.createAddProductInCart.addProductInCart');

        $this->assertNotNull($data);
        $this->assertTrue((bool) ($data['success'] ?? false));
        $this->assertFalse((bool) ($data['isGuest'] ?? true));
        $this->assertNotNull($data['customerId'] ?? null);
        $this->assertNotNull($data['channelId'] ?? null);
        $this->assertSame(1, (int) ($data['itemsCount'] ?? 0));
        $this->assertSame(1, (int) ($data['itemsQty'] ?? 0));

        // Downloadable products are not stockable
        $this->assertFalse((bool) ($data['haveStockableItems'] ?? true));

        // Verify cart totals
        $this->assertGreaterThanOrEqual(0, (float) ($data['subtotal'] ?? -1));
        $this->assertGreaterThanOrEqual(0, (float) ($data['grandTotal'] ?? -1));

        // Verify item is downloadable type
        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item, 'Cart item node is missing');
        $this->assertSame('downloadable', $item['type'] ?? '');
        $this->assertSame($payload['productId'], (int) ($item['productId'] ?? 0));
        $this->assertNotNull($item['name']);
        $this->assertNotNull($item['sku']);
        $this->assertGreaterThanOrEqual(0, (float) ($item['price'] ?? -1));

        // Verify item-level formatted and inclusive fields
        $this->assertArrayHasKey('formattedPrice', $item);
        $this->assertArrayHasKey('formattedTotal', $item);
        $this->assertArrayHasKey('priceInclTax', $item);
        $this->assertArrayHasKey('basePriceInclTax', $item);
        $this->assertArrayHasKey('formattedPriceInclTax', $item);
        $this->assertArrayHasKey('totalInclTax', $item);
        $this->assertArrayHasKey('baseTotalInclTax', $item);
        $this->assertArrayHasKey('formattedTotalInclTax', $item);
        $this->assertArrayHasKey('options', $item);
    }

    /**
     * Downloadable product canChangeQty should be false.
     */
    public function test_downloadable_product_cannot_change_quantity(): void
    {
        $token = $this->getGuestCartToken();
        $payload = $this->createDownloadableProductPayload(1);

        $response = $this->graphQL($this->fullAddToCartMutation(), [
            'productId' => $payload['productId'],
            'quantity' => 1,
            'links' => $payload['links'],
        ], $this->guestHeaders($token));

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.createAddProductInCart.addProductInCart');
        $this->assertNotNull($data);

        $item = $data['items']['edges'][0]['node'] ?? null;
        $this->assertNotNull($item);
        $this->assertFalse((bool) ($item['canChangeQty'] ?? true));
    }
}
