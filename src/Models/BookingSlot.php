<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\State\BookingSlotProvider;

/**
 * Booking Slot GraphQL API Resource
 *
 * Provides available booking slots for a product on a specific date.
 *
 * For non-rental types (default, appointment, table, event):
 *   Each BookingSlot is a single flat slot with from/to/timestamp/qty.
 *
 * For rental hourly type:
 *   Each BookingSlot is a time-range group with a nested `slots` array.
 *   The `time` field is the group label (e.g., "10:00 AM - 12:00 PM"),
 *   and `slots` contains the individual hourly sub-slots.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'BookingSlot',
    uriTemplate: '/booking-slots',
    operations: [
        new GetCollection(
            provider: BookingSlotProvider::class,
            paginationEnabled: false,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product Types'],
                summary: 'Get available booking slots for a product on a specific date',
                description: 'Mirrors the GraphQL bookingSlots query. Routes the booking product to its type-specific helper (default | appointment | rental | event | table). Flat types return [{slotId, from, to, timestamp, qty}]; rental returns [{slotId, time, slots: [{from, to, timestamp, qty}]}].',
                parameters: [
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'id',
                        in: 'query',
                        description: 'Booking product ID.',
                        required: true,
                        schema: ['type' => 'integer', 'example' => 1],
                    ),
                    new \ApiPlatform\OpenApi\Model\Parameter(
                        name: 'date',
                        in: 'query',
                        description: 'Date to fetch slots for (YYYY-MM-DD).',
                        required: true,
                        schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-03-26'],
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            args: [
                'id' => [
                    'type'        => 'Int!',
                    'description' => 'The booking product ID',
                ],
                'date' => [
                    'type'        => 'String!',
                    'description' => 'The date for which to get slots (YYYY-MM-DD)',
                ],
            ],
            provider: BookingSlotProvider::class,
            paginationEnabled: false,
            description: 'Get available booking slots for a product on a specific date',
        ),
    ]
)]
class BookingSlot
{
    /**
     * @var int|string|null Internal identifier (hidden from API)
     */
    #[ApiProperty(identifier: true, writable: false, readable: false)]
    public $_id = null;

    /**
     * @var string|null Slot identifier (timestamp for flat slots, sequential index for groups).
     *
     * Property name is snake_case so the project's name converter
     * (`OutputOnlySnakeToCamelNameConverter`) reads it consistently across
     * REST and GraphQL. The converter camelCases on output, so the response
     * field is `slotId` either way.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $slot_id = null;

    /**
     * @var string|null Time range group label for rental hourly slots (e.g., "10:00 AM - 12:00 PM").
     *                  Null for non-rental booking types.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $time = null;

    /**
     * @var string|null Start time of the slot (e.g., "12:00 PM"). Used by non-rental types.
     *                  Null for rental hourly (data is in `slots` array instead).
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $from = null;

    /**
     * @var string|null End time of the slot (e.g., "12:45 PM"). Used by non-rental types.
     *                  Null for rental hourly (data is in `slots` array instead).
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $to = null;

    /**
     * @var string|null Timestamp range (e.g., "1774247400-1774250100"). Used by non-rental types.
     *                  Null for rental hourly (data is in `slots` array instead).
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $timestamp = null;

    /**
     * @var string|null Indicates if the slot is available or qty remaining. Used by non-rental types.
     *                  Null for rental hourly (data is in `slots` array instead).
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $qty = null;

    /**
     * @var iterable|null Nested hourly sub-slots for rental hourly type.
     *                    Each element: { from, to, timestamp, qty }
     *                    Null for non-rental booking types.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?iterable $slots = null;

    /**
     * Create a new BookingSlot instance
     */
    public function __construct(
        ?string $slotId = null,
        ?string $time = null,
        ?string $from = null,
        ?string $to = null,
        ?string $timestamp = null,
        ?string $qty = null,
        ?array $slots = null
    ) {
        $this->_id = $slotId ?? uniqid('slot_');
        $this->slot_id = $slotId;
        $this->time = $time;
        $this->from = $from;
        $this->to = $to;
        $this->timestamp = $timestamp;
        $this->qty = $qty;
        $this->slots = $slots;
    }
}
