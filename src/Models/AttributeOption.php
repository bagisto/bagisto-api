<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;

#[ApiResource(
    shortName: 'AttributeOption',
    description: 'Attribute option resource',
    operations: [
        new GetCollection(
            uriTemplate: '/attribute_options',
            routePrefix: '/api/admin',
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Get(
            uriTemplate: '/attribute_options/{id}',
            routePrefix: '/api/admin',
            security: "is_granted('ROLE_ADMIN')",
        ),
        new GetCollection(
            uriTemplate: '/attribute-options',
            routePrefix: '/api/shop',
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'List all attribute options',
                description: 'Returns all attribute options.',
            ),
        ),
        new Get(
            uriTemplate: '/attribute-options/{id}',
            routePrefix: '/api/shop',
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'Get a single attribute option by ID',
            ),
        ),
    ],
)]
class AttributeOption extends \Webkul\Attribute\Models\AttributeOption
{
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(readableLink: true)]
    public function getTranslations()
    {
        return $this->translations;
    }

    #[ApiProperty(readableLink: true, description: 'Current locale translation')]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?EloquentModel
    {
        return $this->translation;
    }
}
