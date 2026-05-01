<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Webkul\BookingProduct\Models\BookingProductRentalSlot as BaseModel;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['Product Types'], summary: 'Get a rental-type booking slot config by ID')),
        new GetCollection(openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['Product Types'], summary: 'List rental-type booking slot configs')),
    ],
    graphQlOperations: []
)]
class BookingProductRentalSlot extends BaseModel
{
    /**
     * Override to prevent API Platform from using Laravel's auto-generated getter for 'slots'
     */
    protected $casts = [];

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRentingType()
    {
        return $this->renting_type;
    }

    public function getDailyPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    public function getHourlyPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDailyPrice()
    {
        return $this->daily_price;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getHourlyPrice()
    {
        return $this->hourly_price;
    }

    public function getFormattedDailyPriceAttribute(): ?string
    {
        return $this->daily_price !== null ? core()->formatPrice($this->daily_price) : null;
    }

    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_daily_price(): ?string
    {
        return $this->getFormattedDailyPriceAttribute();
    }

    public function getFormattedHourlyPriceAttribute(): ?string
    {
        return $this->hourly_price !== null ? core()->formatPrice($this->hourly_price) : null;
    }

    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_hourly_price(): ?string
    {
        return $this->getFormattedHourlyPriceAttribute();
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSameSlotAllDays()
    {
        return $this->same_slot_all_days;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSlots()
    {
        return $this->slots;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getBookingProductId()
    {
        return $this->booking_product_id;
    }
}
