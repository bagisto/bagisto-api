<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionDuplicateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionDuplicateProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionDuplicate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/sections/{id}/duplicate',
            input: AdminAppearanceSectionDuplicateInput::class,
            processor: AdminAppearanceSectionDuplicateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Copy a section',
                description: 'Copies a section and its content into the same theme and channel. The copy is created switched off. Read the copy back with the section endpoint.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID to copy', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    description: 'No fields. The section to copy is the one in the path.',
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object', 'properties' => new \ArrayObject],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Section copied.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'sourceId' => 3, 'sectionId' => 12, 'name' => 'Categories Collections (copy)',
                                    'type' => 'category_carousel', 'message' => 'Section copied successfully.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Section not found.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminAppearanceSectionDuplicateInput::class,
            processor: AdminAppearanceSectionDuplicateProcessor::class,
        ),
    ],
)]
class AdminAppearanceSectionDuplicate
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $section_id = null;

    public ?int $source_id = null;

    public ?string $name = null;

    public ?string $type = null;

    public ?string $message = null;
}
