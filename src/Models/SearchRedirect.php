<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\SearchRedirectQueryResolver;
use Webkul\BagistoApi\State\SearchRedirectProvider;

/**
 * The redirect set on a search term in Marketing > Search Terms, which the storefront follows before searching.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'SearchRedirect',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/search-redirect',
            provider: SearchRedirectProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['Product'],
                summary: 'Search-term redirect',
                description: 'Call before searching. When the term has a redirect URL in Marketing > Search Terms for the current channel and locale, the storefront sends the shopper there instead of showing results; `redirectUrl` is that URL, or null. Returned as a list of one entry.',
                parameters: [
                    new Parameter(
                        name: 'query',
                        in: 'query',
                        description: 'The search term.',
                        required: true,
                        schema: ['type' => 'string', 'example' => 'summer sale'],
                    ),
                ],
                responses: [
                    '200' => new Response(
                        description: 'The redirect for the term.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    ['query' => 'summer sale', 'redirectUrl' => 'https://shop.example.com/summer-sale'],
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
            resolver: SearchRedirectQueryResolver::class,
            args: ['query' => ['type' => 'String!', 'description' => 'The search term.']],
            description: 'The redirect URL set on a search term, or null.',
        ),
    ],
)]
class SearchRedirect
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $query = null;

    #[ApiProperty(writable: false)]
    public ?string $redirect_url = null;
}
