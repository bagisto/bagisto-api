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
            uriTemplate: '/attribute-options',
            routePrefix: '/api/shop',
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'List all attribute options',
                description: 'Returns all attribute options with their translations. Public endpoint.',
                responses: [
                    '200' => new Model\Response(
                        description: 'List of attribute options.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'adminName' => 'Red',
                                        'sortOrder' => 0,
                                        'translation' => [
                                            'id' => 1,
                                            'attributeOptionId' => 1,
                                            'locale' => 'en',
                                            'label' => 'Red',
                                        ],
                                        'translations' => [
                                            [
                                                'id' => 86,
                                                'attributeOptionId' => 1,
                                                'locale' => 'de',
                                                'label' => '',
                                            ],
                                            [
                                                'id' => 1,
                                                'attributeOptionId' => 1,
                                                'locale' => 'en',
                                                'label' => 'Red',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/attribute-options/{id}',
            routePrefix: '/api/shop',
            openapi: new Model\Operation(
                tags: ['Attribute'],
                summary: 'Get a single attribute option by ID',
                description: 'Returns one attribute option with its translations. Public endpoint.',
                responses: [
                    '200' => new Model\Response(
                        description: 'Attribute option detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'adminName' => 'Red',
                                    'sortOrder' => 0,
                                    'translation' => [
                                        'id' => 1,
                                        'attributeOptionId' => 1,
                                        'locale' => 'en',
                                        'label' => 'Red',
                                    ],
                                    'translations' => [
                                        [
                                            'id' => 86,
                                            'attributeOptionId' => 1,
                                            'locale' => 'de',
                                            'label' => '',
                                        ],
                                        [
                                            'id' => 1,
                                            'attributeOptionId' => 1,
                                            'locale' => 'en',
                                            'label' => 'Red',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(
                        description: 'Attribute option not found.',
                    ),
                ],
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
