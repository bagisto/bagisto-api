<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAppearanceSectionDraftInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionDraftProcessor;

/**
 * Unpublished edits for one section and locale, held until the theme is published.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionDraft',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/sections/{id}/draft',
            input: AdminAppearanceSectionDraftInput::class,
            processor: AdminAppearanceSectionDraftProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Stage edits for a section',
                description: 'Holds the options as an unpublished draft for the locale. The storefront keeps drawing the published values until the theme is published.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                    new Model\Parameter('locale', 'query', 'Locale the options belong to', false, schema: ['type' => 'string', 'example' => 'en']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['options'],
                                'properties' => [
                                    'options' => ['type' => 'object', 'example' => ['title' => 'Summer Sale']],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Draft saved.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => ['sectionId' => 3, 'locale' => 'en', 'hasDraft' => true]],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Section not found.'),
                    '422' => new Model\Response(description: 'Options missing.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminAppearanceSectionDraftInput::class,
            processor: AdminAppearanceSectionDraftProcessor::class,
        ),
    ],
)]
class AdminAppearanceSectionDraft
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $section_id = null;

    public ?string $locale = null;

    public ?bool $has_draft = null;

    public ?string $message = null;
}
