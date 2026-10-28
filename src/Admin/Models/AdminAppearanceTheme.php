<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceThemeQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeItemProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceTheme',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/appearance/themes',
            provider: AdminAppearanceThemeCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Themes'],
                summary: 'List themes',
                description: 'Every theme this installation knows about — installed ones and the ones available to install — with the channels each is active on. Active themes come first.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The theme gallery.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'code' => 'default',
                                        'name' => 'Default',
                                        'author' => 'Bagisto',
                                        'version' => '2.4.10',
                                        'url' => null,
                                        'demoUrl' => null,
                                        'screenshot' => 'http://localhost:8000/themes/admin/default/images/default.png',
                                        'rating' => null,
                                        'tags' => [],
                                        'description' => 'The theme Bagisto ships with.',
                                        'isInstalled' => true,
                                        'status' => 'active',
                                        'activeOn' => [['id' => 1, 'name' => 'Default']],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/appearance/themes/{code}',
            provider: AdminAppearanceThemeItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Themes'],
                summary: 'Get a theme',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: ['type' => 'string', 'example' => 'default']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The theme.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'code' => 'default',
                                    'name' => 'Default',
                                    'author' => 'Bagisto',
                                    'version' => '2.4.10',
                                    'url' => null,
                                    'demoUrl' => null,
                                    'screenshot' => 'http://localhost:8000/themes/admin/default/images/default.png',
                                    'rating' => null,
                                    'tags' => [],
                                    'description' => 'The theme Bagisto ships with.',
                                    'isInstalled' => true,
                                    'status' => 'active',
                                    'activeOn' => [['id' => 1, 'name' => 'Default']],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Theme not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminAppearanceThemeQueryResolver::class,
            args: ['code' => ['type' => 'String!']],
        ),
        new QueryCollection(
            provider: AdminAppearanceThemeCollectionProvider::class,
            paginationEnabled: false,
        ),
    ],
)]
class AdminAppearanceTheme
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?string $code = null;

    public ?string $name = null;

    public ?string $author = null;

    public ?string $version = null;

    public ?string $url = null;

    public ?string $demo_url = null;

    public ?string $screenshot = null;

    public ?string $rating = null;

    /**
     * @var array<int, string>
     */
    public array $tags = [];

    public ?string $description = null;

    public ?bool $is_installed = null;

    /**
     * active, installed or available.
     */
    public ?string $status = null;

    /**
     * Channels currently running this theme.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $active_on = [];

    public ?string $message = null;
}
