<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\Core\Models\Country as BaseCountry;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new GetCollection,
        new Get,
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection,
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

    /**
     * Get the default translation for the country
     * Note: This field is excluded from GraphQL to avoid null conflicts.
     * Use 'translations' (collection) instead to get all translations.
     */
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
