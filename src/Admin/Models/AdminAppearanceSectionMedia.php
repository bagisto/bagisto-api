<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAppearanceSectionMediaProcessor;

/**
 * One uploaded image or video for a section. The returned path is what a client records
 * in the section's options.
 *
 * REST only — a binary upload is not transportable over JSON GraphQL.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAppearanceSectionMedia',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/appearance/sections/{id}/media',
            inputFormats: ['multipart' => ['multipart/form-data']],
            deserialize: false,
            read: false,
            validate: false,
            processor: AdminAppearanceSectionMediaProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Appearance: Sections'],
                summary: 'Upload media for a section',
                description: 'Stores one image or video against the section and returns the path to record in its options.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Section ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['file'],
                                'properties' => [
                                    'file' => ['type' => 'string', 'format' => 'binary'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Media stored.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => ['sectionId' => 3, 'path' => 'storage/theme/3/banner.webp', 'type' => 'image'],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Section not found.'),
                    '422' => new Model\Response(description: 'No file supplied.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [],
)]
class AdminAppearanceSectionMedia
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $section_id = null;

    public ?string $path = null;

    /**
     * image or video.
     */
    public ?string $type = null;

    public ?string $message = null;
}
