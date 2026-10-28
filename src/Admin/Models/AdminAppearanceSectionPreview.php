<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminAppearanceSectionPreviewQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionPreviewProvider;

/**
 * A theme and channel as the editor is holding it: staged options, status and order
 * applied, so a headless storefront can render the preview the admin panel renders.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionPreview',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            uriTemplate: '/appearance/themes/{code}/sections/preview',
            provider: AdminAppearanceSectionPreviewProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Preview a theme with staged edits applied',
                description: 'The sections a channel would draw if the staged edits were published: draft options replace published ones, draft status and order are applied, sections switched off are left out, and footer links stay last.',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('channel', 'query', 'Channel ID (defaults to the current channel)', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('locale', 'query', 'Locale the options are read in', false, schema: ['type' => 'string', 'example' => 'en']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The drafted sections.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'themeCode' => 'default', 'channelId' => 1, 'locale' => 'en',
                                    'sections' => [
                                        [
                                            'id' => 3, 'name' => 'Categories Collections', 'type' => 'category_carousel',
                                            'sortOrder' => 1, 'status' => true, 'hasDraft' => true,
                                            'options' => ['title' => 'Shop by category'],
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Theme not installed.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: AdminAppearanceSectionPreviewQueryResolver::class,
            args: [
                'code' => ['type' => 'String!'],
                'channel' => ['type' => 'Int'],
                'locale' => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminAppearanceSectionPreview
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?string $theme_code = null;

    public ?int $channel_id = null;

    public ?string $locale = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $sections = [];
}
