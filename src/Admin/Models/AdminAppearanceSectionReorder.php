<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionReorderInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionReorderProcessor;

/**
 * Render order is staged like any other edit. Footer links are always drawn last, so
 * they are moved back to the end of whatever order is sent.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionReorder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/sections/reorder',
            input: AdminAppearanceSectionReorderInput::class,
            processor: AdminAppearanceSectionReorderProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Stage a new section order',
                requestBody: new Model\RequestBody(
                    description: 'Every section of the theme and channel, in the order they should render. A partial list is rejected, because the sections left out would be renumbered over the ones sent. Read the current set from GET /appearance/themes/{code}/sections first. The footer is pinned last whatever position it is given.',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['sectionIds'],
                                'properties' => [
                                    'sectionIds' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'integer'],
                                        'description' => 'Every section id of the theme and channel.',
                                    ],
                                ],
                            ],
                            'example' => ['sectionIds' => [5, 3, 2, 1, 4, 6]],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Order staged.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'sectionIds' => [5, 3, 2],
                                    'hasDraft' => ['5' => true, '3' => true, '2' => true],
                                    'message' => 'Section order staged successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing or unknown section IDs.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminAppearanceSectionReorderInput::class,
            processor: AdminAppearanceSectionReorderProcessor::class,
        ),
    ],
)]
class AdminAppearanceSectionReorder
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?string $id = 'reorder';

    /**
     * @var array<int, int>
     */
    public array $section_ids = [];

    /**
     * @var array<string, bool>
     */
    public array $has_draft = [];

    public ?string $message = null;
}
