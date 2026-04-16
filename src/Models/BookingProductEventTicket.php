<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\BookingProduct\Models\BookingProductEventTicket as BaseModel;

#[ApiResource(routePrefix: '/api/shop', operations: [], graphQlOperations: [])]
class BookingProductEventTicket extends BaseModel
{
    protected $appends = ['formatted_price', 'formatted_special_price'];

    /**
     * Get the translations for the event ticket using BagistoApi model
     */
    public function translations(): HasMany
    {
        return $this->hasMany(BookingProductEventTicketTranslation::class, 'booking_product_event_ticket_id');
    }

    public function getPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    public function getSpecialPriceAttribute($value)
    {
        return $value !== null ? (float) core()->convertPrice((float) $value) : null;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getPrice()
    {
        return $this->price;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getQty()
    {
        return $this->qty;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSpecialPrice()
    {
        return $this->special_price;
    }

    public function getFormattedPriceAttribute(): ?string
    {
        return $this->price !== null ? core()->formatPrice($this->price) : null;
    }

    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_price(): ?string
    {
        return $this->getFormattedPriceAttribute();
    }

    public function getFormattedSpecialPriceAttribute(): ?string
    {
        return $this->special_price !== null ? core()->formatPrice($this->special_price) : null;
    }

    #[ApiProperty(writable: false, readable: true)]
    public function getFormatted_special_price(): ?string
    {
        return $this->getFormattedSpecialPriceAttribute();
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSpecialPriceFrom()
    {
        return $this->special_price_from;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getSpecialPriceTo()
    {
        return $this->special_price_to;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getBookingProductId()
    {
        return $this->booking_product_id;
    }

    public function translation(): HasOne
    {
        return $this->hasOne(BookingProductEventTicketTranslation::class, 'booking_product_event_ticket_id');
    }
}
