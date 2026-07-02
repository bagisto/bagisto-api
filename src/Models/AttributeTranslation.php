<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['AttributeTranslation'],
                summary: 'List attribute translations',
                description: 'Returns the localized names of catalog attributes. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Attribute translation collection.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    ['id' => 1, 'attributeId' => 1, 'locale' => 'en', 'name' => 'SKU'],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['AttributeTranslation'],
                summary: 'Get an attribute translation',
                description: 'Returns a single attribute translation by its identifier. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Attribute translation.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['id' => 1, 'attributeId' => 1, 'locale' => 'en', 'name' => 'SKU'],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Translation not found.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
class AttributeTranslation extends \Webkul\Attribute\Models\AttributeTranslation
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}
