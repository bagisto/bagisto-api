<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\Core\Models\Country as BaseCountry;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            paginationClientItemsPerPage: true,
            openapi: new Operation(
                tags: ['Country'],
                summary: 'List countries',
                description: 'Returns all countries with their `states` and per-locale `translations`. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of countries.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'code' => 'AF',
                                        'name' => 'Afghanistan',
                                        'states' => [],

                                        'translations' => [
                                            [
                                                'id' => 1,
                                                'countryId' => 1,
                                                'locale' => 'ar',
                                                'name' => 'أفغانستان',
                                            ], [
                                                'id' => 256,
                                                'countryId' => 1,
                                                'locale' => 'es',
                                                'name' => 'Afganistán',
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
            openapi: new Operation(
                tags: ['Country'],
                summary: 'Get a country by ID',
                description: 'Returns one country with its `states` and per-locale `translations`. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The country.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'AF',
                                    'name' => 'Afghanistan',
                                    'states' => [],

                                    'translations' => [
                                        [
                                            'id' => 1,
                                            'countryId' => 1,
                                            'locale' => 'ar',
                                            'name' => 'أفغانستان',
                                        ], [
                                            'id' => 256,
                                            'countryId' => 1,
                                            'locale' => 'es',
                                            'name' => 'Afganistán',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Country not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One country with its states. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header.',
        ),
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
            description: 'Every country with its states, ordered by ID. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header.',
        ),
    ]
)]
class Country extends BaseCountry
{
    /**
     * Get states.
     */
    #[ApiProperty(readableLink: true)]
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Get name attribute.
     */
    public function getNameAttribute($value)
    {
        return $value;
    }

    /**
     * States.
     */
    public function states()
    {
        return $this->hasMany(CountryState::class, 'country_id');
    }

    /**
     * Get translation.
     */
    #[ApiProperty(readable: false)]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return parent::getTranslation($locale, $withFallback);
    }

    /**
     * Get translations.
     */
    #[ApiProperty(readableLink: true, description: 'Translations for the country')]
    public function getTranslations()
    {
        return $this->getAttribute('translations') ?? parent::translations();
    }
}
