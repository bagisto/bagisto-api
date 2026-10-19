<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionStatusInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionStatusProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionStatus',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/sections/{id}/status',
            input: AdminAppearanceSectionStatusInput::class,
            processor: AdminAppearanceSectionStatusProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Stage a section on/off',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['status'],

                                'properties' => ['status' => [
                                    'type' => 'boolean',
                                    'example' => true,
                                ]],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Status staged.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => [
                                'sectionId' => 3,
                                'draftStatus' => true,
                                'hasDraft' => true,
                            ]],
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
            input: AdminAppearanceSectionStatusInput::class,
            processor: AdminAppearanceSectionStatusProcessor::class,
        ),
    ],
)]
class AdminAppearanceSectionStatus
{
    use AcceptsCamelCaseWrites;

    /**
     * The section id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $section_id = null;

    /**
     * The draft status.
     */
    public ?bool $draft_status = null;

    /**
     * The has draft.
     */
    public ?bool $has_draft = null;

    /**
     * The message.
     */
    public ?string $message = null;
}
