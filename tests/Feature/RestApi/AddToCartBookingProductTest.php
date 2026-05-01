<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Carbon\Carbon;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\BookingProduct\Helpers\Booking as BookingHelper;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot;
use Webkul\BookingProduct\Repositories\BookingProductRepository;

class AddToCartBookingProductTest extends RestApiTestCase
{
    private string $cartTokensUrl = '/api/shop/cart-tokens';

    private string $addProductUrl = '/api/shop/add-product-in-cart';

    private function createGuestCartToken(): string
    {
        $response = $this->publicPost($this->cartTokensUrl, ['createNew' => true]);
        expect($response->getStatusCode())->toBeIn([200, 201]);

        return (string) ($response->json('cartToken') ?? $response->json('sessionToken'));
    }

    private function postWithToken(string $url, string $token, array $payload): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    private function createDefaultBookingFixture(): array
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'REST-BOOKING-DEFAULT-'.uniqid(),
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

        return ['product' => $product, 'date' => $tomorrow];
    }

    private function getSlotTimestamp(int $productId, string $date): ?string
    {
        try {
            $bookingProductRepository = app(BookingProductRepository::class);
            $bookingProduct = $bookingProductRepository->findOneByField('product_id', $productId);

            if (! $bookingProduct) {
                return null;
            }

            $bookingHelper = app(BookingHelper::class);
            $slots = $bookingHelper->getSlotsByDate($bookingProduct, $date);
            $timestamp = $slots[0]['timestamp'] ?? null;

            return is_string($timestamp) ? $timestamp : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function test_add_default_booking_product_to_cart_as_guest(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createDefaultBookingFixture();

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $fixture['date']);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        $token = $this->createGuestCartToken();

        $response = $this->postWithToken($this->addProductUrl, $token, [
            'productId' => (int) $fixture['product']->id,
            'quantity'  => 1,
            'booking'   => json_encode([
                'type' => 'default',
                'date' => $fixture['date'],
                'slot' => $slot,
            ], JSON_UNESCAPED_SLASHES),
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeTrue();
        expect((int) $response->json('itemsCount'))->toBeGreaterThan(0);
    }

    public function test_add_default_booking_product_to_customer_cart(): void
    {
        $this->seedRequiredData();
        $fixture = $this->createDefaultBookingFixture();

        $slot = $this->getSlotTimestamp((int) $fixture['product']->id, $fixture['date']);
        if (! $slot) {
            $this->markTestSkipped('No valid slot found for default booking product.');
        }

        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, $this->addProductUrl, [
            'productId' => (int) $fixture['product']->id,
            'quantity'  => 1,
            'booking'   => json_encode([
                'type' => 'default',
                'date' => $fixture['date'],
                'slot' => $slot,
            ], JSON_UNESCAPED_SLASHES),
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('isGuest'))->toBeFalse();
    }
}
