<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BookingProduct\Models\BookingProductAppointmentSlot as BaseModel;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(openapi: new Operation(
            tags: ['Product Types'],
            summary: 'Get an appointment-type booking slot config by ID',
            description: 'Public endpoint. Returns a single appointment-type booking slot configuration by ID.',
            responses: [
                '200' => new Response(
                    description: 'Appointment-type booking slot config',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                'id' => 1,
                                'bookingProductId' => 3,
                                'duration' => 45,
                                'breakTime' => 15,
                                'sameSlotAllDays' => 1,
                                'slots' => '[{"to": "10:45", "from": "10:00"}, {"to": "11:45", "from": "11:00"}]',
                            ],
                        ],
                    ]),
                ),
                '404' => new Response(
                    description: 'Appointment slot not found.',
                ),
            ],
        )),
        new GetCollection(openapi: new Operation(
            tags: ['Product Types'],
            summary: 'List appointment-type booking slot configs',
            description: 'Public endpoint. Returns the list of appointment-type booking slot configurations.',
            responses: [
                '200' => new Response(
                    description: 'List of appointment-type booking slot configs',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                [
                                    'id' => 1,
                                    'bookingProductId' => 3,
                                    'duration' => 45,
                                    'breakTime' => 15,
                                    'sameSlotAllDays' => 1,
                                    'slots' => '[{"to": "10:45", "from": "10:00"}, {"to": "11:45", "from": "11:00"}]',
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
class BookingProductAppointmentSlot extends BaseModel
{
    /**
     * Override to prevent API Platform from using Laravel's auto-generated getter for 'slots'
     */
    protected $casts = [];

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
