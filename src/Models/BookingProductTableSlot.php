<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Webkul\BookingProduct\Models\BookingProductTableSlot as BaseModel;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(openapi: new \ApiPlatform\OpenApi\Model\Operation(
            tags: ['Product Types'],
            summary: 'Get a table-type booking slot config by ID',
            description: 'Public endpoint. Returns the table-type booking slot configuration for the given ID.',
            responses: [
                '200' => new \ApiPlatform\OpenApi\Model\Response(
                    description: 'Table-type booking slot config',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                'id'                      => 1,
                                'bookingProductId'        => 5,
                                'priceType'               => 'guest',
                                'guestLimit'              => 0,
                                'duration'                => 45,
                                'breakTime'               => 15,
                                'preventSchedulingBefore' => 2,
                                'sameSlotAllDays'         => 1,
                                'slots'                   => '[{"to": "12:45", "from": "12:00"}, {"to": "13:45", "from": "13:00"}]',
                            ],
                        ],
                    ]),
                ),
                '404' => new \ApiPlatform\OpenApi\Model\Response(
                    description: 'Table slot not found.',
                ),
            ],
        )),
        new GetCollection(openapi: new \ApiPlatform\OpenApi\Model\Operation(
            tags: ['Product Types'],
            summary: 'List table-type booking slot configs',
            description: 'Public endpoint. Returns all table-type booking slot configurations.',
            responses: [
                '200' => new \ApiPlatform\OpenApi\Model\Response(
                    description: 'List of table-type booking slot configs',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                [
                                    'id'                      => 1,
                                    'bookingProductId'        => 5,
                                    'priceType'               => 'guest',
                                    'guestLimit'              => 0,
                                    'duration'                => 45,
                                    'breakTime'               => 15,
                                    'preventSchedulingBefore' => 2,
                                    'sameSlotAllDays'         => 1,
                                    'slots'                   => '[{"to": "12:45", "from": "12:00"}, {"to": "13:45", "from": "13:00"}]',
                                ],
                            ],
                        ],
                    ]),
                ),
            ],
        )),
    ],
    graphQlOperations: []
)]
class BookingProductTableSlot extends BaseModel
{
    /**
     * Override to prevent API Platform from using Laravel's auto-generated getter for 'slots'
     */
    protected $casts = [];

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getPriceType()
    {
        return $this->price_type;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getGuestLimit()
    {
        return $this->guest_limit;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getDuration()
    {
        return $this->duration;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getBreakTime()
    {
        return $this->break_time;
    }

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getPreventSchedulingBefore()
    {
        return $this->prevent_scheduling_before;
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
