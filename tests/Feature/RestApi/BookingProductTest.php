<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\BookingProduct\Models\BookingProductEventTicket;

class BookingProductTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/booking-products';

    public function test_get_single_booking_product(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('booking', ['sku' => 'BP-'.uniqid()]);
        $booking = BookingProduct::create([
            'product_id' => $product->id,
            'type' => 'default',
            'qty' => 10,
            'available_every_week' => 1,
        ]);

        $response = $this->publicGet($this->baseUrl.'/'.$booking->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($booking->id);
    }

    public function test_event_booking_exposes_starting_price(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('booking', ['sku' => 'BP-EVENT-'.uniqid()]);
        $booking = BookingProduct::create([
            'product_id' => $product->id,
            'type' => 'event',
            'qty' => 100,
            'available_every_week' => 1,
        ]);
        BookingProductEventTicket::create([
            'booking_product_id' => $booking->id,
            'price' => 50,
            'qty' => 100,
        ]);

        $expected = (float) $product->getTypeInstance()->getMinimalPrice() + 50.0;

        $response = $this->publicGet($this->baseUrl.'/'.$booking->id);

        $response->assertOk();
        expect((float) $response->json('startingPrice'))->toBe($expected);
        expect((float) $response->json('startingRegularPrice'))->toBe($expected);
        expect($response->json('formattedStartingPrice'))->not->toBeNull();
    }

    public function test_default_booking_has_null_starting_price(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('booking', ['sku' => 'BP-DEF-'.uniqid()]);
        $booking = BookingProduct::create([
            'product_id' => $product->id,
            'type' => 'default',
            'qty' => 10,
            'available_every_week' => 1,
        ]);

        $response = $this->publicGet($this->baseUrl.'/'.$booking->id);

        $response->assertOk();
        expect($response->json('startingPrice'))->toBeNull();
    }

    public function test_get_nonexistent_booking_product_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}
