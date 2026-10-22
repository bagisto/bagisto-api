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
                description: 'Makes every staged draft (content, status and order) of the theme\'s sections on one channel live on the storefront, and returns the IDs of the sections published. An unknown theme is a 404.',
                parameters: [
                    new Model\Parameter('code', 'path', 'Theme code', true, schema: [
                        'type' => 'string',
                        'example' => 'default',
                    ]),
                    new Model\Parameter('channel', 'query', 'Channel ID (defaults to the current channel)', false, schema: [
                        'type' => 'integer',
                        'example' => 1,
                    ]),
                ],
                requestBody: new Model\RequestBody(
                    description: 'Optional. The channel to act on; the current channel is used when it is omitted.',
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',

                                'properties' => [
                                    'channel' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                    ],
                                ],
                            ],

                            'example' => ['channel' => 1],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
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

    /**
     * The theme code.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?string $theme_code = null;

    /**
     * The channel id.
     */
    public ?int $channel_id = null;

    /**
     * The ids of the sections that were published.
     *
     * @var array<int, int>
     */
    public array $section_ids = [];

    /**
     * The message.
     */
    public ?string $message = null;
}
