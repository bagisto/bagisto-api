<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class BookingProductDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $type = null;

    public ?int $qty = null;

    public ?string $location = null;

    public ?bool $available_every_week = null;

    public ?string $available_from = null;

    public ?string $available_to = null;

    /**
     * Slot configuration. Shape varies by booking type:
     *  - appointment: { duration, breakTime, sameSlotAllDays, slots: [{from,to,...}] }
     *  - default:     { bookingType, sameSlotAllDays, slots: [...] }
     *  - rental:      { rentingType, dailyPrice, hourlyPrice, sameSlotAllDays, slots: [...] }
     *  - table:       { guestCapacity, prepTime, sameSlotAllDays, slots: [...] }
     *  - event:       { tickets: [{id,price,qtyAvailable,...}] }
     */
    #[ApiProperty(openapiContext: ['type' => 'object'])]
    public mixed $slots = [];

    public function __construct(
        ?int $id = null,
        ?string $type = null,
        ?int $qty = null,
        ?string $location = null,
        ?bool $available_every_week = null,
        ?string $available_from = null,
        ?string $available_to = null,
        mixed $slots = [],
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->qty = $qty;
        $this->location = $location;
        $this->available_every_week = $available_every_week;
        $this->available_from = $available_from;
        $this->available_to = $available_to;
        $this->slots = $slots;
    }

    /**
     * Build a DTO from a Bagisto BookingProduct model with its slot relations
     * already loaded. Shared between ProductDetailProvider (PDP embed) and
     * BookingProductDetailProvider (standalone /booking-products/{id} endpoint).
     */
    public static function fromModel($bp): self
    {
        $decode = function ($v) {
            if (is_string($v)) {
                $d = json_decode($v, true);

                return is_array($d) ? $d : [];
            }

            return is_array($v) ? $v : [];
        };

        $slots = [];
        switch ($bp->type) {
            case 'appointment':
                if ($bp->appointment_slot) {
                    $as = $bp->appointment_slot;
                    $slots = [
                        'duration'           => $as->duration,
                        'breakTime'          => $as->break_time,
                        'sameSlotAllDays'    => (bool) $as->same_slot_all_days,
                        'slots'              => $decode($as->slots ?? null),
                    ];
                }
                break;
            case 'default':
                if ($bp->default_slot) {
                    $ds = $bp->default_slot;
                    $slots = [
                        'bookingType'     => $ds->booking_type ?? null,
                        'sameSlotAllDays' => isset($ds->same_slot_all_days) ? (bool) $ds->same_slot_all_days : null,
                        'slots'           => $decode($ds->slots ?? null),
                    ];
                }
                break;
            case 'rental':
                if ($bp->rental_slot) {
                    $rs = $bp->rental_slot;
                    $slots = [
                        'rentingType'     => $rs->renting_type ?? null,
                        'dailyPrice'      => isset($rs->daily_price) ? (float) $rs->daily_price : null,
                        'hourlyPrice'     => isset($rs->hourly_price) ? (float) $rs->hourly_price : null,
                        'sameSlotAllDays' => isset($rs->same_slot_all_days) ? (bool) $rs->same_slot_all_days : null,
                        'slots'           => $decode($rs->slots ?? null),
                    ];
                }
                break;
            case 'table':
                if ($bp->table_slot) {
                    $ts = $bp->table_slot;
                    $slots = [
                        'guestCapacity'          => $ts->guest_capacity ?? null,
                        'prepTime'               => $ts->prep_time ?? null,
                        'allowGuestsOverbooking' => isset($ts->allow_guests_overbooking) ? (bool) $ts->allow_guests_overbooking : null,
                        'sameSlotAllDays'        => isset($ts->same_slot_all_days) ? (bool) $ts->same_slot_all_days : null,
                        'slots'                  => $decode($ts->slots ?? null),
                    ];
                }
                break;
            case 'event':
                $slots = [
                    'tickets' => $bp->event_tickets
                        ? $bp->event_tickets->map(fn ($t) => [
                            'id'           => (int) $t->id,
                            'price'        => $t->price !== null ? (float) $t->price : null,
                            'qty'          => $t->qty ?? null,
                            'specialPrice' => $t->special_price !== null ? (float) $t->special_price : null,
                            'name'         => $t->name ?? null,
                            'description'  => $t->description ?? null,
                        ])->values()->all()
                        : [],
                ];
                break;
        }

        return new self(
            id: (int) $bp->id,
            type: $bp->type,
            qty: $bp->qty !== null ? (int) $bp->qty : null,
            location: $bp->location ?? null,
            available_every_week: isset($bp->available_every_week) ? (bool) $bp->available_every_week : null,
            available_from: $bp->available_from ? (is_string($bp->available_from) ? $bp->available_from : $bp->available_from->toAtomString()) : null,
            available_to: $bp->available_to ? (is_string($bp->available_to) ? $bp->available_to : $bp->available_to->toAtomString()) : null,
            slots: $slots,
        );
    }
}
