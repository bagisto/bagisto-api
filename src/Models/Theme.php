<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\ThemeQueryResolver;
use Webkul\BagistoApi\State\ThemeProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Theme',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/theme',
            provider: ThemeProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['Theme'],
                summary: 'Get the active theme of the current channel',
                description: 'Returns the theme this channel\'s storefront is drawn with, and the section types it can hold. The sections themselves are read from `/api/shop/sections`, which is already scoped to this theme. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The active theme.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'code' => 'default',
                                        'name' => 'Default',

                                        'sectionTypes' => [
                                            'image_carousel',
                                            'product_carousel',
                                            'category_carousel',
                                            'footer_links',
                                            'static_content',
                                            'services_content',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: ThemeQueryResolver::class,
            description: 'The theme the current channel\'s storefront is drawn with and the section types it can hold; read the sections themselves with `sections`. Takes no arguments. Needs the X-STOREFRONT-KEY header.',
            args: [],
        ),
    ],
)]
class Theme
{
    /**
     * The code.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $code = null;

    /**
     * The name.
     */
    public ?string $name = null;

    /**
     * The section types the theme supports.
     *
     * @var array<int, string>
     */
    #[ApiProperty(
        writable: false,
        schema: [
            'type' => 'array',
            'items' => ['type' => 'string'],
        ],
    )]
    public ?array $section_types = null;
}
