<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Webkul\Product\Models\ProductCustomizableOptionTranslation as BaseProductCustomizableOptionTranslation;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'Get a product customizable option translation by ID',
                description: 'Returns a single locale-specific translation row (`label`) for a customizable option. Referenced from `/api/shop/products/{id}/customizable-options` responses via the `translations` IRI list.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Customizable option translation.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'                          => 1,
                                    'productCustomizableOptionId' => 1,
                                    'locale'                      => 'en',
                                    'label'                       => 'Engraving text',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'Translation not found.'),
                ],
            ),
        ),
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Product'],
                summary: 'List customizable option translations',
                description: 'Lists all customizable option translation rows. Use the parent product\'s `customizable-options` sub-resource to scope to one product.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of customizable option translations.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'                          => 1,
                                        'productCustomizableOptionId' => 1,
                                        'locale'                      => 'en',
                                        'label'                       => 'Engraving text',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class ProductCustomizableOptionTranslation extends BaseProductCustomizableOptionTranslation {}
