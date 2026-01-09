<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Laravel\Eloquent\Filter\EqualsFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\QueryParameter;
use Illuminate\Database\Eloquent\Model;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ThemeCustomization',
    uriTemplate: '/theme-customizations/{id}',
    operations: [
        new Get(uriTemplate: '/theme-customizations/{id}'),
        new GetCollection(uriTemplate: '/theme-customizations'),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection,
    ],
)]
#[QueryParameter(key: 'type', filter: EqualsFilter::class)]
class ThemeCustomization extends \Webkul\Theme\Models\ThemeCustomization
{
    protected $appends = ['code', 'theme_code'];

    #[ApiProperty(readable: true, writable: false)]
    public function getCodeAttribute(): ?string
    {
        return $this->attributes['theme_code'] ?? null;
    }

    #[ApiProperty(readable: true, writable: false)]
    public function getTheme_codeAttribute(): ?string
    {
        return $this->attributes['theme_code'] ?? null;
    }

    #[ApiProperty(readable: true, writable: false, readableLink: true)]
    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model
    {
        return $this->translation;
    }

    #[ApiProperty(readable: true, writable: false, readableLink: true)]
    public function getTranslations()
    {
        return $this->translations;
    }
}
