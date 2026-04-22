<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\BookingProduct\Helpers\Booking as BookingHelper;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot;
use Webkul\BookingProduct\Repositories\BookingProductRepository;
use Webkul\Product\Models\Product;

class AddProductInCartTest extends RestApiTestCase
{
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    private string $addProductUrl = '/api/shop/add-product-in-cart';

    private function createGuestCartToken(): string
    {
        $response = $this->publicPost($this->cartTokensUrl, [
            'createNew' => true,
        ]);

        expect($response->status())->toBeIn([200, 201]);

        $token = $response->json('cartToken') ?? $response->json('sessionToken');

        $this->assertNotEmpty($token, 'Guest cart token is missing from REST response.');

        return (string) $token;
    }

    private function guestPostWithToken(string $url, string $token, array $payload): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    private function assertAddToCartResponse(TestResponse $response): array
    {
        expect($response->status())->toBeIn([200, 201]);

        $data = $response->json();

        expect($data)->toBeArray();
        expect((bool) ($data['success'] ?? false))->toBeTrue();
        expect((int) ($data['itemsCount'] ?? 0))->toBeGreaterThan(0);
        expect($data['items'] ?? null)->toBeArray();

        return $data;
    }

    private function createSimpleProduct(float $price = 17.0): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'REST-SIMPLE-'.uniqid(),
        ]);

        $this->upsertProductAttributeValue($product->id, 'price', $price, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'weight', 1.0, null, 'default');
        $this->ensureInventory($product, 50);

        return $product;
    }

    private function createConfigurableProductPayload(): array
    {
        $this->seedRequiredData();

        $attributes = \Webkul\Attribute\Models\Attribute::query()
            ->where('is_configurable', 1)
            ->where('type', 'select')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($attributes->isEmpty()) {
            $this->markTestSkipped('No configurable select attributes found. Run Bagisto seeders for configurable attributes.');
        }

        $parent = $this->createBaseProduct('configurable', [
            'sku' => 'REST-CONFIG-PARENT-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);
        $this->upsertProductAttributeValue($parent->id, 'weight', 1.5, null, 'default');

        $child = $this->createBaseProduct('simple', [
            'sku'       => 'REST-CONFIG-CHILD-'.uniqid(),
            'parent_id' => $parent->id,
        ]);
        $this->ensureInventory($child, 50);
        $this->upsertProductAttributeValue($child->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($child->id, 'weight', 1.5, null, 'default');

        DB::table('product_relations')->insert([
            'parent_id' => $parent->id,
            'child_id'  => $child->id,
        ]);

        $superAttribute = [];

        foreach ($attributes as $attribute) {
            $attributeId = (int) $attribute->id;
            $optionId = $this->createAttributeOption($attributeId, 'REST-OPT-'.$child->sku);

            DB::table('product_super_attributes')->insert([
                'product_id'   => $parent->id,
                'attribute_id' => $attributeId,
            ]);

            $this->upsertProductAttributeValue($child->id, (string) $attribute->code, $optionId, null, 'default');

            $superAttribute[(string) $attributeId] = (int) $optionId;
        }

        return [
            'productId'                  => (int) $parent->id,
            'selectedConfigurableOption' => (int) $child->id,
            'superAttribute'             => $superAttribute,
        ];
    }

    private function createGroupedProductPayload(int $associatedCount = 2): array
    {
        $grouped = $this->createBaseProduct('grouped', [
            'sku' => 'REST-GROUPED-'.uniqid(),
        ]);
        $this->ensureInventory($grouped, 50);

        $qtyMap = [];

        for ($i = 1; $i <= $associatedCount; $i++) {
            $associated = $this->createBaseProduct('simple', [
                'sku' => 'REST-GROUPED-ASSOC-'.$grouped->id.'-'.$i,
            ]);
            $this->ensureInventory($associated, 50);
            $this->upsertProductAttributeValue($associated->id, 'manage_stock', 0, null, 'default');

            DB::table('product_grouped_products')->insert([
                'product_id'            => $grouped->id,
                'associated_product_id' => $associated->id,
                'qty'                   => 1,
                'sort_order'            => $i,
            ]);

            $qtyMap[(string) $associated->id] = 1;
        }

        return [
            'productId' => (int) $grouped->id,
            'qty'       => $qtyMap,
        ];
    }

    private function createBundleProductPayload(): array
    {
        $bundle = $this->createBaseProduct('bundle', [
            'sku' => 'REST-BUNDLE-'.uniqid(),
        ]);

        $this->ensureInventory($bundle, 50);
        $this->upsertProductAttributeValue($bundle->id, 'manage_stock', 0, null, 'default');

        $optionId = (int) DB::table('product_bundle_options')->insertGetId([
            'product_id'  => $bundle->id,
            'type'        => 'checkbox',
            'is_required' => 1,
            'sort_order'  => 1,
        ]);

        $optionProduct = $this->createBaseProduct('simple', [
            'sku' => 'REST-BUNDLE-OPT-'.$bundle->id,
        ]);
        $this->ensureInventory($optionProduct, 50);
        $this->upsertProductAttributeValue($optionProduct->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($optionProduct->id, 'price', 10.0, null, 'default');

        $bundleOptionProductId = (int) DB::table('product_bundle_option_products')->insertGetId([
            'product_id'               => $optionProduct->id,
            'product_bundle_option_id' => $optionId,
            'qty'                      => 1,
            'is_user_defined'          => 1,
            'is_default'               => 1,
            'sort_order'               => 1,
        ]);

        return [
            'productId'       => (int) $bundle->id,
            'bundleOptions'   => [(string) $optionId => [(int) $bundleOptionProductId]],
            'bundleOptionQty' => [(string) $optionId => 1],
        ];
    }

    private function createDownloadableProductPayload(int $linksCount = 2): array
    {
        $product = $this->createBaseProduct('downloadable', [
            'sku' => 'REST-DOWNLOAD-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $links = [];

        for ($i = 1; $i <= $linksCount; $i++) {
            $links[] = (int) DB::table('product_downloadable_links')->insertGetId([
                'product_id' => $product->id,
                'url'        => 'https://example.com/download/'.$product->sku.'/'.$i,
                'file'       => null,
                'file_name'  => null,
                'type'       => 'url',
                'price'      => 0,
                'downloads'  => 0,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'productId' => (int) $product->id,
            'links'     => $links,
        ];
    }

    private function createDefaultBookingProductPayload(): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'REST-BOOKING-'.uniqid(),
        ]);

        $booking = BookingProduct::query()->create([
            'product_id'           => $product->id,
            'type'                 => 'default',
            'qty'                  => 100,
            'available_every_week' => 1,
        ]);

        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        $weekday = (int) Carbon::parse($tomorrow)->format('w');

        BookingProductDefaultSlot::query()->create([
            'booking_product_id' => $booking->id,
            'booking_type'       => 'many',
            'duration'           => 30,
            'break_time'         => 0,
            'slots'              => [
                (string) $weekday => [
                    ['from' => '09:00', 'to' => '10:00', 'qty' => 10, 'status' => 1],
                ],
            ],
        ]);

        $slot = $this->getSlotTimestamp($product->id, $tomorrow);

        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        return [
            'productId' => (int) $product->id,
            'booking'   => [
                'date' => $tomorrow,
                'slot' => $slot,
            ],
        ];
    }

    private function getSlotTimestamp(int $productId, string $date): ?string
    {
        try {
            /** @var BookingProductRepository $bookingProductRepository */
            $bookingProductRepository = app(BookingProductRepository::class);
            $bookingProduct = $bookingProductRepository->findOneByField('product_id', $productId);

            if (! $bookingProduct) {
                return null;
            }

            /** @var BookingHelper $bookingHelper */
            $bookingHelper = app(BookingHelper::class);
            $slots = $bookingHelper->getSlotsByDate($bookingProduct, $date);

            $timestamp = $slots[0]['timestamp'] ?? null;

            return is_string($timestamp) ? $timestamp : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function test_add_simple_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $product = $this->createSimpleProduct();

        $response = $this->guestPostWithToken($this->addProductUrl, $token, [
            'productId' => $product->id,
            'quantity'  => 1,
        ]);

        $data = $this->assertAddToCartResponse($response);

        expect((bool) ($data['isGuest'] ?? false))->toBeTrue();
        expect((int) ($data['items'][0]['productId'] ?? 0))->toBe($product->id);
        expect((string) ($data['items'][0]['type'] ?? ''))->toBe('simple');
    }

    public function test_add_simple_product_to_cart_as_customer(): void
    {
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);
        $product = $this->createSimpleProduct();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => $product->id,
            'quantity'  => 2,
        ]);

        $data = $this->assertAddToCartResponse($response);

        expect((bool) ($data['isGuest'] ?? true))->toBeFalse();
        expect((int) ($data['customerId'] ?? 0))->toBe($customer->id);
        expect((int) ($data['items'][0]['quantity'] ?? 0))->toBe(2);
    }

    public function test_add_configurable_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createConfigurableProductPayload();

        $response = $this->guestPostWithToken($this->addProductUrl, $token, [
            'productId'                  => $payload['productId'],
            'quantity'                   => 1,
            'selectedConfigurableOption' => $payload['selectedConfigurableOption'],
            'superAttribute'             => $payload['superAttribute'],
        ]);

        $data = $this->assertAddToCartResponse($response);
        $productId = (int) ($data['items'][0]['productId'] ?? 0);

        expect($productId)->toBeIn([
            $payload['productId'],
            $payload['selectedConfigurableOption'],
        ]);
    }

    public function test_add_grouped_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createGroupedProductPayload();

        $response = $this->guestPostWithToken($this->addProductUrl, $token, [
            'productId' => $payload['productId'],
            'quantity'  => 1,
            'qty'       => $payload['qty'],
        ]);

        $data = $this->assertAddToCartResponse($response);

        expect(count($data['items'] ?? []))->toBeGreaterThan(0);
    }

    public function test_add_bundle_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createBundleProductPayload();

        $response = $this->guestPostWithToken($this->addProductUrl, $token, [
            'productId'       => $payload['productId'],
            'quantity'        => 1,
            'bundleOptions'   => $payload['bundleOptions'],
            'bundleOptionQty' => $payload['bundleOptionQty'],
        ]);

        $data = $this->assertAddToCartResponse($response);

        expect((string) ($data['items'][0]['type'] ?? ''))->toBe('bundle');
    }

    public function test_add_downloadable_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createDownloadableProductPayload();

        $response = $this->guestPostWithToken($this->addProductUrl, $token, [
            'productId' => $payload['productId'],
            'quantity'  => 1,
            'links'     => $payload['links'],
        ]);

        $data = $this->assertAddToCartResponse($response);

        expect((string) ($data['items'][0]['type'] ?? ''))->toBe('downloadable');
    }

    public function test_add_default_booking_product_to_cart_as_guest(): void
    {
        $token = $this->createGuestCartToken();
        $payload = $this->createDefaultBookingProductPayload();

        $response = $this->guestPostWithToken($this->addProductUrl, $token, [
            'productId' => $payload['productId'],
            'quantity'  => 1,
            'booking'   => $payload['booking'],
        ]);

        $data = $this->assertAddToCartResponse($response);

        expect((string) ($data['items'][0]['type'] ?? ''))->toBe('booking');
    }
}
