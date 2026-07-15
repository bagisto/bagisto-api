<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot as BaseModel;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(openapi: new Operation(
            tags: ['Product Types'],
            summary: 'Get a default-type booking slot config by ID',
            description: 'Public endpoint. Returns a single default-type booking slot configuration by ID.',
            responses: [
                '200' => new Response(
                    description: 'Default-type booking slot config',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                'id' => 1,
                                'bookingType' => 'one',
                                'duration' => null,
                                'breakTime' => null,
                                'slots' => '[{"id": "1", "to": "18:00", "from": "12:00", "to_day": "1", "from_day": "1"}, {"id": "2", "to": "18:00", "from": "12:00", "to_day": "2", "from_day": "2"}]',
                            ],
                        ],
                    ]),
                ),
                '404' => new Response(
                    description: 'Default slot not found.',
                ),
            ],
        )),
        new GetCollection(openapi: new Operation(
            tags: ['Product Types'],
            summary: 'List default-type booking slot configs',
            description: 'Public endpoint. Returns the list of default-type booking slot configurations.',
            responses: [
                '200' => new Response(
                    description: 'List of default-type booking slot configs',
                    content: new \ArrayObject([
                        'application/json' => [
                            'example' => [
                                [
                                    'id' => 1,
                                    'bookingType' => 'one',
                                    'duration' => null,
                                    'breakTime' => null,
                                    'slots' => '[{"id": "1", "to": "18:00", "from": "12:00", "to_day": "1", "from_day": "1"}, {"id": "2", "to": "18:00", "from": "12:00", "to_day": "2", "from_day": "2"}]',
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
class BookingProductDefaultSlot extends BaseModel
{
    /**
     * Override to prevent API Platform from using Laravel's auto-generated getter for 'slots'
     */
    protected $casts = [];

    #[ApiProperty(writable: false, readable: true, required: false)]
    public function getBookingType()
    {
        return $this->booking_type;
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
