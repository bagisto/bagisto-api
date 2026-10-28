<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\State\BookingSlotProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'BookingSlot',
    uriTemplate: '/booking-slots',
    operations: [
        new GetCollection(
            provider: BookingSlotProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['Product Types'],
                summary: 'Get available booking slots for a product on a specific date',
                description: 'Mirrors the GraphQL bookingSlots query. Routes the booking product to its type-specific helper (default | appointment | rental | event | table). Flat types return [{slotId, from, to, timestamp, qty}]; rental returns [{slotId, time, slots: [{from, to, timestamp, qty}]}].',
                parameters: [
                    new Parameter(
                        name: 'id',
                        in: 'query',
                        description: 'Booking product ID.',
                        required: true,
                        schema: [
                            'type' => 'integer',
                            'example' => 1,
                        ],
                    ),
                    new Parameter(
                        name: 'date',
                        in: 'query',
                        description: 'Date to fetch slots for (YYYY-MM-DD).',
                        required: true,
                        schema: [
                            'type' => 'string',
                            'format' => 'date',
                            'example' => '2026-03-26',
                        ],
                    ),
                ],
                responses: [
                    '200' => new Response(
                        description: 'Available booking slots for the product on the given date.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'slotId' => '1785738600-1785760200',
                                        'from' => '12:00 PM',
                                        'to' => '06:00 PM',
                                        'timestamp' => '1785738600-1785760200',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '400' => new Response(
                        description: 'Missing required `id` or `date` query parameter.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            args: [
                'id' => [
                    'type' => 'Int!',
                    'description' => 'The booking product ID',
                ],

                'date' => [
                    'type' => 'String!',
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
     * The identifier, unique per booking product, date and slot.
     *
     * @var int|string|null
     */
    #[ApiProperty(identifier: true, writable: false, readable: false)]
    public $_id = null;

    /**
     * The slot id: the timestamp range of a flat slot, or the position of a rental group.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $slot_id = null;

    /**
     * The time range label of a rental hourly group, or null for other booking types.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $time = null;

    /**
     * The start time of a flat slot, or null for a rental hourly group.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $from = null;

    /**
     * The end time of a flat slot, or null for a rental hourly group.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $to = null;

    /**
     * The timestamp range of a flat slot, or null for a rental hourly group.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $timestamp = null;

    /**
     * The quantity still available in a flat slot, or null for a rental hourly group.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $qty = null;

    /**
     * The hourly sub-slots of a rental group, each with from, to, timestamp and qty.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?iterable $slots = null;

    /**
     * Create a new booking slot, identified by the given identifier or else its slot id.
     */
    public function __construct(
        ?string $slotId = null,
        ?string $time = null,
        ?string $from = null,
        ?string $to = null,
        ?string $timestamp = null,
        ?string $qty = null,
        ?array $slots = null,
        ?string $identifier = null,
    ) {
        $this->_id = $identifier ?? $slotId ?? uniqid('slot_');
        $this->slot_id = $slotId;
        $this->time = $time;
        $this->from = $from;
        $this->to = $to;
        $this->timestamp = $timestamp;
        $this->qty = $qty;
        $this->slots = $slots;
    }
}
