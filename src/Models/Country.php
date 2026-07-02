<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;
use Webkul\Core\Models\Country as BaseCountry;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection(
            paginationClientItemsPerPage: true,
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Country'],
                summary: 'List countries',
                description: 'Returns all countries with their `states` and per-locale `translations`. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'List of countries.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'           => 1,
                                        'code'         => 'AF',
                                        'name'         => 'Afghanistan',
                                        'states'       => [],
                                        'translations' => [
                                            ['id' => 1, 'countryId' => 1, 'locale' => 'ar', 'name' => 'أفغانستان'],
                                            ['id' => 256, 'countryId' => 1, 'locale' => 'es', 'name' => 'Afganistán'],
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
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Country'],
                summary: 'Get a country by ID',
                description: 'Returns one country with its `states` and per-locale `translations`. Public endpoint.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'The country.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'           => 1,
                                    'code'         => 'AF',
                                    'name'         => 'Afghanistan',
                                    'states'       => [],
                                    'translations' => [
                                        ['id' => 1, 'countryId' => 1, 'locale' => 'ar', 'name' => 'أفغانستان'],
                                        ['id' => 256, 'countryId' => 1, 'locale' => 'es', 'name' => 'Afganistán'],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new \ApiPlatform\OpenApi\Model\Response(description: 'Country not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: CursorAwareCollectionProvider::class),
    ]
)]
class Country extends BaseCountry
{
    #[ApiProperty(readableLink: true)]
    public function getStates()
    {
        return $this->states;
    }

    public function getNameAttribute($value)
    {
        return $value;
    }

    public function states()
    {
        return $this->hasMany(\Webkul\BagistoApi\Models\CountryState::class, 'country_id');
    }

    #[ApiProperty(readable: false)]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?\Illuminate\Database\Eloquent\Model
    {
        return parent::getTranslation($locale, $withFallback);
    }

    #[ApiProperty(readableLink: true, description: 'Translations for the country')]
    public function getTranslations()
    {
        return $this->getAttribute('translations') ?? parent::translations();
    }
}
