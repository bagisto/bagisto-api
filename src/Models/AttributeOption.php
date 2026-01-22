<?php

namespace Webkul\BagistoApi\Models;
 
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource(
    shortName: 'AttributeOption',
    description: 'Attribute option resource',
    routePrefix: '/api/admin',
    security: "is_granted('ROLE_ADMIN')",
)]
class AttributeOption extends \Webkul\Attribute\Models\AttributeOption
{
    #[ApiProperty(readableLink: true)]
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
