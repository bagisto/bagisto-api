<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ApiResource(operations: [], graphQlOperations: [])]
class BookingProductEventTicketTranslation extends Model
{
    /**
     * The table.
     */
    protected $table = 'booking_product_event_ticket_translations';

    /**
     * The timestamps.
     */
    public $timestamps = false;

    /**
     * The fillable.
     */
    protected $fillable = ['name', 'description', 'locale', 'booking_product_event_ticket_id'];

    /**
     * Event ticket.
     */
    public function eventTicket(): BelongsTo
    {
        return $this->belongsTo(BookingProductEventTicket::class, 'booking_product_event_ticket_id');
    }

    /**
     * Get booking product event ticket id.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getBookingProductEventTicketId()
    {
        return $this->booking_product_event_ticket_id;
    }

    /**
     * Get name.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get description.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get locale.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getLocale()
    {
        return $this->locale;
    }
}
