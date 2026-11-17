<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\UrlResolverQueryResolver;
use Webkul\BagistoApi\State\UrlResolverProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'UrlResolver',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/url-resolver',
            provider: UrlResolverProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Operation(
                tags: ['Catalog'],
                summary: 'Resolve a storefront URL',
                description: 'Tells a headless storefront what a path is, in the order the storefront checks it: a category slug, a product URL key (only a visible, enabled product), the last segment of a nested category path (a 301), then a URL rewrite. `type` is `category`, `product`, `redirect` or `home`. A path nothing matches answers 404. Returned as a list of one entry.',
                parameters: [
                    new Parameter('path', 'query', 'The path, without the domain, for example `men/shirts` or `/nike-shoes`.', true, schema: ['type' => 'string', 'example' => 'nike-shoes']),
                ],
                responses: [
                    '200' => new Response(
                        description: 'What the path is.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'path' => 'old-shoes',
                                    'type' => 'redirect',
                                    'entityId' => null,
                                    'targetPath' => 'nike-shoes',
                                    'redirectType' => 301,
                                ]],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Nothing matches the path.'),
                    '422' => new Response(description: 'The path is missing.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: UrlResolverQueryResolver::class,
            args: ['path' => ['type' => 'String!']],
            normalizationContext: ['groups' => ['query']],
            description: 'Resolve a storefront path to a category, a product or a redirect.',
        ),
    ],
)]
class UrlResolver
{
    /**
     * The path that was resolved, without leading and trailing slashes.
     */
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?string $path = null;

    /**
     * What the path is: category, product, redirect or home.
     */
    #[ApiProperty(writable: false, description: 'category, product, redirect or home.')]
    #[Groups(['query'])]
    public ?string $type = null;

    /**
     * The category or product id, for those types.
     */
    #[ApiProperty(writable: false, description: 'The category or product id, for those types.')]
    #[Groups(['query'])]
    public ?int $entity_id = null;

    /**
     * Where to redirect, for the redirect type.
     */
    #[ApiProperty(writable: false, description: 'Where to redirect, for the redirect type.')]
    #[Groups(['query'])]
    public ?string $target_path = null;

    /**
     * The HTTP status of the redirect, 301 or 302.
     */
    #[ApiProperty(writable: false, description: 'HTTP status of the redirect, 301 or 302.')]
    #[Groups(['query'])]
    public ?int $redirect_type = null;
}
