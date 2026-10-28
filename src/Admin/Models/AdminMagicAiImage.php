<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminMagicAiImageInput;
use Webkul\BagistoApi\Admin\State\AdminMagicAiProcessor;

/**
 * Magic AI image generation for admin product media.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMagicAiImage',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/magic-ai/image',
            input: AdminMagicAiImageInput::class,
            processor: AdminMagicAiProcessor::class,
            status: 200,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Magic AI'],
                summary: 'Generate images',
                description: 'Generates one or more images from a prompt with the configured AI provider. Each image is returned as a base64 data URL in `url`. Requires Magic AI and its image generation feature to be enabled in Configuration → Magic AI.',
                requestBody: new Model\RequestBody(required: true, content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['prompt', 'size'],
                            'properties' => [
                                'prompt' => ['type' => 'string'],
                                'model' => ['type' => 'string', 'nullable' => true],
                                'n' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 10],
                                'size' => ['type' => 'string', 'enum' => ['1:1', '2:3', '3:2']],
                                'quality' => ['type' => 'string', 'enum' => ['high', 'medium', 'low']],
                            ],
                        ],
                        'example' => [
                            'prompt' => 'A studio photo of a red cotton t-shirt on a white background',
                            'model' => 'gpt-image-1',
                            'n' => 1,
                            'size' => '1:1',
                            'quality' => 'medium',
                        ],
                    ],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'The generated images.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'images' => [['url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...']],
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'Magic AI image generation is disabled.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '422' => new Model\Response(description: 'Validation failed.'),
                    '500' => new Model\Response(description: 'The AI provider returned an error; `message` carries it.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminMagicAiImageInput::class,
            processor: AdminMagicAiProcessor::class,
            read: false,
            description: 'Generate images with Magic AI.',
        ),
    ],
)]
class AdminMagicAiImage
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $id = null;

    #[ApiProperty(writable: false, description: 'Generated images, each `{ url }` with a base64 data URL.')]
    #[Groups(['mutation'])]
    public ?array $images = null;
}
