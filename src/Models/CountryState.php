<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Link;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CountryStateCollectionProvider;
use Webkul\BagistoApi\State\CountryStateQueryProvider;
use Webkul\Core\Models\CountryState as BaseCountryState;

/**
 * CountryState - Subresource of Country with REST and GraphQL support
 *
 * Pattern: Like AttributeOption - multiple #[ApiResource] annotations
 * - Subresource routes: /countries/{country_id}/states
 * - Root routes: /country-states (for GraphQL queries and root REST access)
 */

// Subresource nested collection: /countries/{country_id}/states
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/countries/{country_id}/states',
    uriVariables: [
        'country_id' => new Link(
            fromClass: Country::class,
            fromProperty: 'states',
            identifiers: ['id']
        ),
    ],
    operations: [
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CountryState'],
                summary: 'List states for a country',
                description: 'Returns the states of the given country. Each state\'s `translations` are IRIs. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of states.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'           => 1,
                                        'countryId'    => 244,
                                        'countryCode'  => 'US',
                                        'code'         => 'AL',
                                        'defaultName'  => 'Alabama',
                                        'translations' => [
                                            '/api/shop/country_state_translations/1',
                                            '/api/shop/country_state_translations/569',
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
    graphQlOperations: []
)]
// Subresource single item: /countries/{country_id}/states/{id}
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/countries/{country_id}/states/{id}',
    uriVariables: [
        'country_id' => new Link(
            fromClass: Country::class,
            fromProperty: 'states',
            identifiers: ['id']
        ),
        'id' => new Link(fromClass: CountryState::class),
    ],
    operations: [
        new Get(
            provider: CountryStateQueryProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CountryState'],
                summary: 'Get a state of a country by ID',
                description: 'Returns one state scoped to the given country. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'The state.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'           => 1,
                                    'countryId'    => 244,
                                    'countryCode'  => 'US',
                                    'code'         => 'AL',
                                    'defaultName'  => 'Alabama',
                                    'translations' => [
                                        '/api/shop/country_state_translations/1',
                                        '/api/shop/country_state_translations/569',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'State not found for this country.'),
                ],
            ),
        ),
    ],
    graphQlOperations: []
)]
// Root collection: /country-states with GraphQL collection query
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CountryState',
    uriTemplate: '/country-states',
    operations: [
        new GetCollection(
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CountryState'],
                summary: 'List all country states',
                description: 'Returns all states across every country. Each state\'s `translations` are IRIs. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of states.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'           => 1,
                                        'countryId'    => 244,
                                        'countryCode'  => 'US',
                                        'code'         => 'AL',
                                        'defaultName'  => 'Alabama',
                                        'translations' => [
                                            '/api/shop/country_state_translations/1',
                                            '/api/shop/country_state_translations/569',
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
        new QueryCollection(
            provider: CountryStateCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'countryId' => [
                    'type'        => 'Int!',
                    'description' => 'Filter states by country ID (required)',
                ],
                'first'  => ['type' => 'Int', 'description' => 'Limit results (forward pagination)'],
                'last'   => ['type' => 'Int', 'description' => 'Limit results (backward pagination)'],
                'after'  => ['type' => 'String', 'description' => 'Cursor for forward pagination'],
                'before' => ['type' => 'String', 'description' => 'Cursor for backward pagination'],
            ]
        ),
    ]
)]
// Root single item: /country-states/{id} with GraphQL query
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/country-states/{id}',
    operations: [
        new Get(
            provider: CountryStateQueryProvider::class,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['CountryState'],
                summary: 'Get a country state by ID',
                description: 'Returns one state by its global ID. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'The state.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'           => 1,
                                    'countryId'    => 244,
                                    'countryCode'  => 'US',
                                    'code'         => 'AL',
                                    'defaultName'  => 'Alabama',
                                    'translations' => [
                                        '/api/shop/country_state_translations/1',
                                        '/api/shop/country_state_translations/569',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'State not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class CountryState extends BaseCountryState {}
