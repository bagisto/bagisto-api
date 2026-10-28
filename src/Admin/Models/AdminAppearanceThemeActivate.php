<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceThemeActivateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceThemeActivateProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceThemeActivate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/themes/{code}/activate',
            input: AdminAppearanceThemeActivateInput::class,
            processor: AdminAppearanceThemeActivateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Themes'],
                summary: 'Activate a theme on channels',
                description: 'Points the given channels at the theme. Sections belonging to a channel\'s previous theme are left in place but stop being drawn — call the impact endpoint first to see what that affects.',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: ['type' => 'string', 'example' => 'default']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['channelIds'],
                                'properties' => [
                                    'channelIds' => ['type' => 'array', 'items' => ['type' => 'integer'], 'example' => [1]],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Theme activated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'code' => 'default',
                                    'activatedOn' => [['id' => 1, 'name' => 'Default']],
                                    'message' => 'Theme activated successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Theme not found or not installed.'),
                    '422' => new Model\Response(description: 'Missing or unknown channel IDs.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminAppearanceThemeActivateInput::class,
            processor: AdminAppearanceThemeActivateProcessor::class,
        ),
    ],
)]
class AdminAppearanceThemeActivate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?string $code = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $activated_on = [];

    public ?string $message = null;
}
