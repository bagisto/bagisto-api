<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\BagistoApi\Service\BookingStartingPriceCalculator;
use Webkul\BookingProduct\Models\BookingProduct as BaseBookingProduct;

#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/booking-products/{id}',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'Get booking-type product configuration',
                description: 'Returns the booking-specific configuration with the type-specific slot data (`slots`) embedded inline — no dangling IRIs. The shape of `slots` varies by `type`: appointment / default / rental / table return a single config block; event returns `tickets[]`. To compute availability for a specific date use `/api/shop/booking-slots?id={booking_product_id}&date=YYYY-MM-DD`.',
                responses: [
                    '200' => new Response(
                        description: 'Booking product configuration.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'type' => 'default',
                                    'qty' => 150,
                                    'location' => 'Noida, Uttar Pradesh',
                                    'showLocation' => 0,
                                    'availableEveryWeek' => null,
                                    'availableFrom' => '2026-04-06T12:00:00+05:30',
                                    'availableTo' => '2026-12-31T12:00:00+05:30',
                                    'createdAt' => '2026-04-03T00:24:30+05:30',
                                    'updatedAt' => '2026-04-06T21:09:47+05:30',
                                    'slots' => [
                                        'bookingType' => 'one',
                                        'sameSlotAllDays' => null,
                                        'slots' => [
                                            ['id' => '1', 'to' => '18:00', 'from' => '12:00', 'to_day' => '1', 'from_day' => '1'],
                                            ['id' => '2', 'to' => '18:00', 'from' => '12:00', 'to_day' => '2', 'from_day' => '2'],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Booking product not found.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'BookingProduct',
    uriTemplate: '/products/{productId}/booking-products',
    uriVariables: [
        'productId' => new Link(
            fromClass: Product::class,
            fromProperty: 'booking_products',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'List booking-product configurations for a product',
                description: 'Booking-type only. Returns the booking-specific configuration row(s) for the given product. The `type` field on each row (default/appointment/rental/event/table) indicates which slot helper governs availability — use `/api/shop/booking-slots?id={id}&date=YYYY-MM-DD` to fetch slots.',
                responses: [
                    '200' => new Response(
                        description: 'Booking product configurations for the product.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'type' => 'default',
                                        'qty' => 150,
                                        'location' => 'Noida, Uttar Pradesh',
                                        'showLocation' => 0,
                                        'availableEveryWeek' => null,
                                        'availableFrom' => '2026-04-06T12:00:00+05:30',
                                        'availableTo' => '2026-12-31T12:00:00+05:30',
                                        'createdAt' => '2026-04-03T00:24:30+05:30',
                                        'updatedAt' => '2026-04-06T21:09:47+05:30',
                                        'slots' => [
                                            'bookingType' => 'one',
                                            'sameSlotAllDays' => null,
                                            'slots' => [
                                                ['id' => '1', 'to' => '18:00', 'from' => '12:00', 'to_day' => '1', 'from_day' => '1'],
                                                ['id' => '2', 'to' => '18:00', 'from' => '12:00', 'to_day' => '2', 'from_day' => '2'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
class BookingProduct extends BaseBookingProduct
{
    protected $appends = [
        'slots',
        'starting_price',
        'formatted_starting_price',
        'starting_regular_price',
        'formatted_starting_regular_price',
    ];

    private bool $startingResolved = false;

    private ?array $startingData = null;

    /**
     * Eloquent accessor — surfaced via $appends so API Platform sees `slots`
     * as a regular model attribute. Returns the type-specific slot config
     * inline as a nested object/array.
     */
    public function getSlotsAttribute(): array
    {
        $decode = function ($v) {
            if (is_string($v)) {
                $d = json_decode($v, true);

                return is_array($d) ? $d : [];
            }

            return is_array($v) ? $v : [];
        };

        $slots = [];

        switch ($this->type) {
            case 'appointment':
                if ($as = $this->appointment_slot) {
                    $slots = [
                        'duration' => $as->duration,
                        'breakTime' => $as->break_time,
                        'sameSlotAllDays' => (bool) $as->same_slot_all_days,
                        'slots' => $decode($as->slots ?? null),
                    ];
                }
                break;
            case 'default':
                if ($ds = $this->default_slot) {
                    $slots = [
                        'bookingType' => $ds->booking_type ?? null,
                        'sameSlotAllDays' => isset($ds->same_slot_all_days) ? (bool) $ds->same_slot_all_days : null,
                        'slots' => $decode($ds->slots ?? null),
                    ];
                }
                break;
            case 'rental':
                if ($rs = $this->rental_slot) {
                    $slots = [
                        'rentingType' => $rs->renting_type ?? null,
                        'dailyPrice' => isset($rs->daily_price) ? (float) $rs->daily_price : null,
                        'hourlyPrice' => isset($rs->hourly_price) ? (float) $rs->hourly_price : null,
                        'sameSlotAllDays' => isset($rs->same_slot_all_days) ? (bool) $rs->same_slot_all_days : null,
                        'slots' => $decode($rs->slots ?? null),
                    ];
                }
                break;
            case 'table':
                if ($ts = $this->table_slot) {
                    $slots = [
                        'guestCapacity' => $ts->guest_capacity ?? null,
                        'prepTime' => $ts->prep_time ?? null,
                        'allowGuestsOverbooking' => isset($ts->allow_guests_overbooking) ? (bool) $ts->allow_guests_overbooking : null,
                        'sameSlotAllDays' => isset($ts->same_slot_all_days) ? (bool) $ts->same_slot_all_days : null,
                        'slots' => $decode($ts->slots ?? null),
                    ];
                }
                break;
            case 'event':
                $slots = [
                    'tickets' => $this->event_tickets
                        ? $this->event_tickets->map(fn ($t) => [
                            'id' => (int) $t->id,
                            'price' => $t->price !== null ? (float) $t->price : null,
                            'qty' => $t->qty ?? null,
                            'specialPrice' => $t->special_price !== null ? (float) $t->special_price : null,
                            'name' => $t->name ?? null,
                            'description' => $t->description ?? null,
                        ])->values()->all()
                        : [],
                ];
                break;
        }

        return $slots;
    }

    public function getStartingPriceAttribute(): ?float
    {
        return $this->startingData()['final'] ?? null;
    }

    public function getFormattedStartingPriceAttribute(): ?string
    {
        return $this->startingData()['formattedFinal'] ?? null;
    }

    public function getStartingRegularPriceAttribute(): ?float
    {
        return $this->startingData()['regular'] ?? null;
    }

    public function getFormattedStartingRegularPriceAttribute(): ?string
    {
        return $this->startingData()['formattedRegular'] ?? null;
    }

    private function startingData(): ?array
    {
        if (! $this->startingResolved) {
            $this->startingData = BookingStartingPriceCalculator::compute($this);
            $this->startingResolved = true;
        }

        return $this->startingData;
    }

    /**
     * Backing slot relations. Kept readable so the GraphQL schema continues
     * to expose `defaultSlot` / `appointmentSlot` / `eventTickets` /
     * `rentalSlot` / `tableSlot` as fields on the BookingProduct type.
     *
     * For REST these will surface as IRIs to /api/shop/booking_product_*_slots/{id}
     * — the slot models declare matching Get/GetCollection operations so the
     * IRIs resolve. The new `slots` accessor above also inlines the same data
     * for REST clients that prefer one-shot access.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public function default_slot(): HasOne
    {
        return $this->hasOne(BookingProductDefaultSlot::class, 'booking_product_id');
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function appointment_slot(): HasOne
    {
        return $this->hasOne(BookingProductAppointmentSlot::class, 'booking_product_id');
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function event_tickets(): HasMany
    {
        return $this->hasMany(BookingProductEventTicket::class, 'booking_product_id')
            ->with(['translation', 'translations']);
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function rental_slot(): HasOne
    {
        return $this->hasOne(BookingProductRentalSlot::class, 'booking_product_id');
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function table_slot(): HasOne
    {
        return $this->hasOne(BookingProductTableSlot::class, 'booking_product_id');
    }
}
