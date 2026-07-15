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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\ChannelProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            provider: ChannelProvider::class,
            openapi: new Operation(
                tags: ['Channel'],
                summary: 'Get a channel by ID',
                description: 'Returns a single storefront channel with its configuration, SEO defaults, and related locale/currency/translation references. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Channel found.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'default',
                                    'timezone' => null,
                                    'theme' => 'default',
                                    'hostname' => 'https://api-demo.bagisto.com',
                                    'logo' => null,
                                    'favicon' => null,
                                    'homeSeo' => [
                                        'meta_title' => 'Demo store',
                                        'meta_keywords' => 'Demo store meta keyword',
                                        'meta_description' => 'Demo store meta description',
                                    ],
                                    'isMaintenanceOn' => 0,
                                    'allowedIps' => '192.168.45.51',
                                    'createdAt' => null,
                                    'updatedAt' => '2026-04-08T17:23:40+05:30',
                                    'logoUrl' => null,
                                    'faviconUrl' => null,
                                    'locales' => ['/api/shop/locales/1', '/api/shop/locales/10'],
                                    'currencies' => ['/api/shop/currencies/1'],
                                    'defaultLocale' => '/api/shop/locales/1',
                                    'baseCurrency' => '/api/shop/currencies/1',
                                    'translation' => '/api/shop/channel_translations/1',
                                    'translations' => ['/api/shop/channel_translations/1', '/api/shop/channel_translations/5'],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Channel not found.',
                    ),
                ],
            ),
        ),
        new GetCollection(
            provider: ChannelProvider::class,
            paginationClientItemsPerPage: true,
            openapi: new Operation(
                tags: ['Channel'],
                summary: 'List channels',
                description: 'Lists all storefront channels with their configuration, SEO defaults, and related locale/currency/translation references. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Channels listed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'code' => 'default',
                                        'timezone' => null,
                                        'theme' => 'default',
                                        'hostname' => 'https://api-demo.bagisto.com',
                                        'logo' => null,
                                        'favicon' => null,
                                        'homeSeo' => [
                                            'meta_title' => 'Demo store',
                                            'meta_keywords' => 'Demo store meta keyword',
                                            'meta_description' => 'Demo store meta description',
                                        ],
                                        'isMaintenanceOn' => 0,
                                        'allowedIps' => '192.168.45.51',
                                        'createdAt' => null,
                                        'updatedAt' => '2026-04-08T17:23:40+05:30',
                                        'logoUrl' => null,
                                        'faviconUrl' => null,
                                        'locales' => ['/api/shop/locales/1', '/api/shop/locales/10'],
                                        'currencies' => ['/api/shop/currencies/1'],
                                        'defaultLocale' => '/api/shop/locales/1',
                                        'baseCurrency' => '/api/shop/currencies/1',
                                        'translation' => '/api/shop/channel_translations/1',
                                        'translations' => ['/api/shop/channel_translations/1', '/api/shop/channel_translations/5'],
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
