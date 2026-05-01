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
        new GetCollection(paginationClientItemsPerPage: true),
        new Get,
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
