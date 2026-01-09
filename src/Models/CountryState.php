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
        new GetCollection,
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
        new Get(provider: CountryStateQueryProvider::class),
    ],
    graphQlOperations: []
)]
// Root collection: /country-states with GraphQL collection query
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CountryState',
    uriTemplate: '/country-states',
    operations: [
        new GetCollection,
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
        new Get(provider: CountryStateQueryProvider::class),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
    ]
)]
class CountryState extends BaseCountryState {}
