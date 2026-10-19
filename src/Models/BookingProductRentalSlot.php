<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BookingProduct\Models\BookingProductRentalSlot as BaseModel;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(openapi: new Operation(
            tags: ['Product Types'],
            summary: 'Get a rental-type booking slot config by ID',
            description: 'Public endpoint. Returns a single rental-type booking slot config by its ID.',
            responses: [
                '200' => new Response(
                    description: 'Rental slot config',

                    content: new \ArrayObject(['application/json' => ['example' => [
                        'id' => 1,
                        'bookingProductId' => 4,
                        'rentingType' => 'daily_hourly',
                        'dailyPrice' => 99,
                        'hourlyPrice' => 105,
                        'sameSlotAllDays' => 1,
                        'slots' => '[{"to": "18:00", "from": "12:00"}]',
                        'formattedDailyPrice' => '$99.00',
                        'formattedHourlyPrice' => '$105.00',
                    ]]]),
                ),
                '404' => new Response(description: 'Rental slot not found.'),
            ],
        )),
        new GetCollection(openapi: new Operation(
            tags: ['Product Types'],
            summary: 'List rental-type booking slot configs',
            description: 'Public endpoint. Returns the list of rental-type booking slot configs.',
            responses: [
                '200' => new Response(
                    description: 'Rental slot config list',
                    content: new \ArrayObject(['application/json' => ['example' => [[
                        'id' => 1,
                        'bookingProductId' => 4,
                        'rentingType' => 'daily_hourly',
                        'dailyPrice' => 99,
                        'hourlyPrice' => 105,
                        'sameSlotAllDays' => 1,
                        'slots' => '[{"to": "18:00", "from": "12:00"}]',
                        'formattedDailyPrice' => '$99.00',
                        'formattedHourlyPrice' => '$105.00',
                    ]]]]),
                ),
            ],
        )),
    ],
    graphQlOperations: []
)]
class BookingProductRentalSlot extends BaseModel
{
    /**
     * Override to prevent API Platform from using Laravel's auto-generated getter for 'slots'.
     */
    protected $casts = [];

    /**
     * Get renting type.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRentingType()
    {
        return $this->renting_type;
    }

    /**
     * Get daily price attribute.
     */
    public function getDailyPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    /**
     * Get hourly price attribute.
     */
    public function getHourlyPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    /**
     * Get daily price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDailyPrice()
    {
        return $this->daily_price;
    }

    /**
     * Get hourly price.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getHourlyPrice()
    {
        return $this->hourly_price;
    }

    /**
     * Get formatted daily price attribute.
     */
    public function getFormattedDailyPriceAttribute(): ?string
    {
        return $this->daily_price !== null ? core()->formatPrice($this->daily_price) : null;
    }

    /**
     * Get formatted daily price.
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_daily_price(): ?string
    {
        return $this->getFormattedDailyPriceAttribute();
    }

    /**
     * Get formatted hourly price attribute.
     */
    public function getFormattedHourlyPriceAttribute(): ?string
    {
        return $this->hourly_price !== null ? core()->formatPrice($this->hourly_price) : null;
    }

    /**
     * Get formatted hourly price.
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_hourly_price(): ?string
    {
        return $this->getFormattedHourlyPriceAttribute();
    }

    /**
     * Get same slot all days.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSameSlotAllDays()
    {
        return $this->same_slot_all_days;
    }

    /**
     * Get slots.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Get booking product id.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getBookingProductId()
    {
        return $this->booking_product_id;
    }
}
