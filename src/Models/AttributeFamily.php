<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Webkul\Attribute\Models\AttributeFamily as BaseAttributeFamily;

#[ApiResource(operations: [], graphQlOperations: [])]
class AttributeFamily extends BaseAttributeFamily
{
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
