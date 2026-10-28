<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class ShippingRateOutput
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    public ?string $id = null;

    /**
     * The code.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $code = null;

    /**
     * The label.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $label = null;

    /**
     * The price.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $price = null;

    /**
     * The formatted price.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $formatted_price = null;

    /**
     * The description.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $description = null;

    /**
     * The method.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $method = null;

    /**
     * The method title.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $method_title = null;

    /**
     * The method description.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $method_description = null;

    /**
     * The base price.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?float $base_price = null;

    /**
     * The base formatted price.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $base_formatted_price = null;

    /**
     * The carrier.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $carrier = null;

    /**
     * The carrier title.
     */
    #[ApiProperty(readable: true, writable: false)]
    public ?string $carrier_title = null;
}
