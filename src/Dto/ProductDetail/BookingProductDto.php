<?php

namespace Webkul\BagistoApi\Dto\ProductDetail;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\BagistoApi\Service\BookingStartingPriceCalculator;

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

    #[ApiProperty(openapiContext: ['type' => 'number', 'example' => 250])]
    public ?float $starting_price = null;

    #[ApiProperty(openapiContext: ['type' => 'string', 'example' => '$250.00'])]
    public ?string $formatted_starting_price = null;

    #[ApiProperty(openapiContext: ['type' => 'number', 'example' => 250])]
    public ?float $starting_regular_price = null;

    #[ApiProperty(openapiContext: ['type' => 'string', 'example' => '$250.00'])]
    public ?string $formatted_starting_regular_price = null;

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
    public static function fromModel($bookingProduct): self
    {
        $decode = function ($value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : [];
            }

            return is_array($value) ? $value : [];
        };

        $slots = [];
        switch ($bookingProduct->type) {
            case 'appointment':
                if ($bookingProduct->appointment_slot) {
                    $as = $bookingProduct->appointment_slot;
                    $slots = [
                        'duration' => $as->duration,
                        'breakTime' => $as->break_time,
                        'sameSlotAllDays' => (bool) $as->same_slot_all_days,
                        'slots' => $decode($as->slots ?? null),
                    ];
                }
                break;
            case 'default':
                if ($bookingProduct->default_slot) {
                    $downloadableSample = $bookingProduct->default_slot;
                    $slots = [
                        'bookingType' => $downloadableSample->booking_type ?? null,
                        'sameSlotAllDays' => isset($downloadableSample->same_slot_all_days) ? (bool) $downloadableSample->same_slot_all_days : null,
                        'slots' => $decode($downloadableSample->slots ?? null),
                    ];
                }
                break;
            case 'rental':
                if ($bookingProduct->rental_slot) {
                    $rows = $bookingProduct->rental_slot;
                    $slots = [
                        'rentingType' => $rows->renting_type ?? null,
                        'dailyPrice' => isset($rows->daily_price) ? (float) $rows->daily_price : null,
                        'hourlyPrice' => isset($rows->hourly_price) ? (float) $rows->hourly_price : null,
                        'sameSlotAllDays' => isset($rows->same_slot_all_days) ? (bool) $rows->same_slot_all_days : null,
                        'slots' => $decode($rows->slots ?? null),
                    ];
                }
                break;
            case 'table':
                if ($bookingProduct->table_slot) {
                    $tableSlot = $bookingProduct->table_slot;
                    $slots = [
                        'guestCapacity' => $tableSlot->guest_capacity ?? null,
                        'prepTime' => $tableSlot->prep_time ?? null,
                        'allowGuestsOverbooking' => isset($tableSlot->allow_guests_overbooking) ? (bool) $tableSlot->allow_guests_overbooking : null,
                        'sameSlotAllDays' => isset($tableSlot->same_slot_all_days) ? (bool) $tableSlot->same_slot_all_days : null,
                        'slots' => $decode($tableSlot->slots ?? null),
                    ];
                }
                break;
            case 'event':
                $slots = [
                    'tickets' => $bookingProduct->event_tickets
                        ? $bookingProduct->event_tickets->map(fn ($eventTicket) => [
                            'id' => (int) $eventTicket->id,
                            'price' => $eventTicket->price !== null ? (float) $eventTicket->price : null,
                            'qty' => $eventTicket->qty ?? null,
                            'specialPrice' => $eventTicket->special_price !== null ? (float) $eventTicket->special_price : null,
                            'name' => $eventTicket->name ?? null,
                            'description' => $eventTicket->description ?? null,
                        ])->values()->all()
                        : [],
                ];
                break;
        }

        $dto = new self(
            id: (int) $bookingProduct->id,
            type: $bookingProduct->type,
            qty: $bookingProduct->qty !== null ? (int) $bookingProduct->qty : null,
            location: $bookingProduct->location ?? null,
            available_every_week: isset($bookingProduct->available_every_week) ? (bool) $bookingProduct->available_every_week : null,
            available_from: $bookingProduct->available_from ? (is_string($bookingProduct->available_from) ? $bookingProduct->available_from : $bookingProduct->available_from->toAtomString()) : null,
            available_to: $bookingProduct->available_to ? (is_string($bookingProduct->available_to) ? $bookingProduct->available_to : $bookingProduct->available_to->toAtomString()) : null,
            slots: $slots,
        );

        $starting = BookingStartingPriceCalculator::compute($bookingProduct);

        if ($starting !== null) {
            $dto->starting_price = $starting['final'];
            $dto->formatted_starting_price = $starting['formattedFinal'];
            $dto->starting_regular_price = $starting['regular'];
            $dto->formatted_starting_regular_price = $starting['formattedRegular'];
        }

        return $dto;
    }
}
