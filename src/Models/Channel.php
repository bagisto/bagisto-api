<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\ChannelProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(provider: ChannelProvider::class),
        new GetCollection(provider: ChannelProvider::class),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: ChannelProvider::class),
    ]
)]
class Channel extends \Webkul\Core\Models\Channel
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Override locales relationship to return API resource model
     */
    public function locales(): BelongsToMany
    {
        return $this->belongsToMany(Locale::class, 'channel_locales', 'channel_id', 'locale_id');
    }

    /**
     * Override currencies relationship to return API resource model
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'channel_currencies', 'channel_id', 'currency_id');
    }

    /**
     * Override default locale relationship to return API resource model
     */
    public function default_locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /**
     * Override base currency relationship to return API resource model
     */
    public function base_currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Expose logo URL for API
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getLogoUrl(): ?string
    {
        return $this->logo_url();
    }

    /**
     * Expose favicon URL for API
     */
    #[ApiProperty(writable: false, readable: true)]
    public function getFaviconUrl(): ?string
    {
        return $this->favicon_url();
    }
}
