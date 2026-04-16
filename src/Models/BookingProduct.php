<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\BookingProduct\Models\BookingProduct as BaseBookingProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/booking-products/{id}',
    operations: [],
    graphQlOperations: []
)]
class BookingProduct extends BaseBookingProduct
{
    /**
     * Get default slot (for default booking type)
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDefaultSlot()
    {
        return $this->default_slot;
    }

    /**
     * Get appointment slot (for appointment booking type)
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getAppointmentSlot()
    {
        return $this->appointment_slot;
    }

    /**
     * Get event tickets (for event booking type)
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getEventTickets()
    {
        return $this->event_tickets;
    }

    /**
     * Get rental slot (for rental booking type)
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getRentalSlot()
    {
        return $this->rental_slot;
    }

    /**
     * Get table slot (for table booking type)
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getTableSlot()
    {
        return $this->table_slot;
    }

    /**
     * Get the default slot relationship using BagistoApi model
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function default_slot(): HasOne
    {
        return $this->hasOne(BookingProductDefaultSlot::class, 'booking_product_id');
    }

    /**
     * Get the appointment slot relationship using BagistoApi model
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function appointment_slot(): HasOne
    {
        return $this->hasOne(BookingProductAppointmentSlot::class, 'booking_product_id');
    }

    /**
     * Get the event tickets relationship using BagistoApi model
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function event_tickets(): HasMany
    {
        return $this->hasMany(BookingProductEventTicket::class, 'booking_product_id')
            ->with(['translation', 'translations']);
    }

    /**
     * Get the rental slot relationship using BagistoApi model
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function rental_slot(): HasOne
    {
        return $this->hasOne(BookingProductRentalSlot::class, 'booking_product_id');
    }

    /**
     * Get the table slot relationship using BagistoApi model
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function table_slot(): HasOne
    {
        return $this->hasOne(BookingProductTableSlot::class, 'booking_product_id');
    }
}
