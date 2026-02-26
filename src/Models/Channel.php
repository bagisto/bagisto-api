<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
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
     * Expose locales relationship as array for API (read-only)
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?array $_locales = null;

    /**
     * Expose currencies relationship as array for API (read-only)
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?array $_currencies = null;

    /**
     * Expose default locale for API with custom name
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?object $defaultLocaleData = null;

    /**
     * Expose base currency for API with custom name
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?object $baseCurrencyData = null;
}



