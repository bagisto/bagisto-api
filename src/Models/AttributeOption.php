<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Illuminate\Database\Eloquent\Model;

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

    #[ApiProperty(readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return $this->translation;
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
