<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\Attribute\Models\AttributeFamily as BaseAttributeFamily;

#[ApiResource(operations: [], graphQlOperations: [])]
class AttributeFamily extends BaseAttributeFamily
{
    /**
     * `configurable_attributes` is an accessor returning a collection of models; API Platform
     * types it `String`, so selecting it threw. Hiding it keeps it out of the schema while the.
     *
     * @var list<string>
     */
    protected $hidden = ['configurable_attributes'];

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Core exposes these as query builders rather than relations or scalars, so API Platform
     * types them `String` and serialising one throws — every query selecting them returned a.
     */
    #[ApiProperty(readable: false, writable: false)]
    public function custom_attributes()
    {
        return parent::custom_attributes();
    }

    /**
     * Get configurable attributes attribute.
     */
    #[ApiProperty(readable: false, writable: false)]
    public function getConfigurableAttributesAttribute()
    {
        return parent::getConfigurableAttributesAttribute();
    }
}
