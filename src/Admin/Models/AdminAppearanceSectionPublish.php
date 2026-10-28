<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionPublishInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionPublishProcessor;

/**
 * Promotes every staged edit of a theme and channel to what the storefront draws.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionPublish',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/themes/{code}/sections/publish',
            input: AdminAppearanceSectionPublishInput::class,
            processor: AdminAppearanceSectionPublishProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Publish staged section edits',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: ['type' => 'string', 'example' => 'default']),
                    new Model\Parameter('channel', 'query', 'Channel ID (defaults to the current channel)', false, schema: ['type' => 'integer', 'example' => 1]),
                ],
                requestBody: new Model\RequestBody(
                    description: 'Optional. The channel to act on; the current channel is used when it is omitted.',
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'channel' => ['type' => 'integer', 'example' => 1],
                                ],
                            ],
                            'example' => ['channel' => 1],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Staged edits published.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'themeCode' => 'default', 'channelId' => 1, 'sectionIds' => [3, 5],
                                    'message' => 'Section changes published successfully.',
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
        new Mutation(
            name: 'create',
            input: AdminAppearanceSectionPublishInput::class,
            processor: AdminAppearanceSectionPublishProcessor::class,
        ),
    ],
)]
class AdminAppearanceSectionPublish
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?string $theme_code = null;

    public ?int $channel_id = null;

    /**
     * @var array<int, int>
     */
    public array $section_ids = [];

    public ?string $message = null;
}
