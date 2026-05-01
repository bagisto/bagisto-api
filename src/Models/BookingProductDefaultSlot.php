<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Webkul\BookingProduct\Models\BookingProductDefaultSlot as BaseModel;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['Product Types'], summary: 'Get a default-type booking slot config by ID')),
        new GetCollection(openapi: new \ApiPlatform\OpenApi\Model\Operation(tags: ['Product Types'], summary: 'List default-type booking slot configs')),
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
