<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\BookingProduct\Models\BookingProduct;

class BookingProductTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/booking-products';

    public function test_get_single_booking_product(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('booking', ['sku' => 'BP-'.uniqid()]);
        $booking = BookingProduct::create([
            'product_id'           => $product->id,
            'type'                 => 'default',
            'qty'                  => 10,
            'available_every_week' => 1,
        ]);

        $response = $this->publicGet($this->baseUrl.'/'.$booking->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($booking->id);
    }

    public function test_get_nonexistent_booking_product_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}
