<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceThemeImpactQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeImpactProvider;

/**
 * What switching the named channels over to a theme would leave behind, so a client can
 * spell it out before activating.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceThemeImpact',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/appearance/themes/{code}/impact',
            provider: AdminAppearanceThemeImpactProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Themes'],
                summary: 'Report what activating a theme would leave behind',
                description: 'Lists the channels that already hold sections built for their current theme, with how many. A channel whose sections would survive is not listed.',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code being activated', true, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('channel_ids[]', 'query', 'Channel IDs the theme would be applied to', true, schema: ['type' => 'array', 'items' => ['type' => 'integer']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The impact report.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'code' => 'velocity',
                                    'impact' => [
                                        ['channelId' => 1, 'channel' => 'Default', 'currentTheme' => 'Default', 'customizations' => 6],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Theme not found.'),
                    '422' => new Model\Response(description: 'Missing or unknown channel IDs.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminAppearanceThemeImpactQueryResolver::class,
            args: [
                'code' => ['type' => 'String!'],
                'channelIds' => ['type' => '[Int!]!'],
            ],
        ),
    ],
)]
class AdminAppearanceThemeImpact
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?string $code = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $impact = [];
}
