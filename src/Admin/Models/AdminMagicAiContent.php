<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminMagicAiContentInput;
use Webkul\BagistoApi\Admin\State\AdminMagicAiProcessor;

/**
 * Magic AI text generation for admin editors: product and category descriptions, meta fields.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMagicAiContent',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/magic-ai/content',
            input: AdminMagicAiContentInput::class,
            processor: AdminMagicAiProcessor::class,
            status: 200,
            read: false,
            openapi: new Model\Operation(
                tags: ['Admin Magic AI'],
                summary: 'Generate text content',
                description: 'Generates text from a prompt with the configured AI provider, the same way the admin editor\'s Magic AI button does. Requires Magic AI and its text generation feature to be enabled in Configuration → Magic AI.',
                requestBody: new Model\RequestBody(required: true, content: new \ArrayObject([
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'required' => ['prompt'],
                            'properties' => [
                                'prompt' => ['type' => 'string'],
                                'model' => ['type' => 'string', 'nullable' => true],
                            ],
                        ],
                        'example' => [
                            'prompt' => 'Write a 60-word product description for a red cotton crew-neck t-shirt.',
                            'model' => 'gpt-4o-mini',
                        ],
                    ],
                ])),
                responses: [
                    '200' => new Model\Response(
                        description: 'The generated text.',
                        content: new \ArrayObject(['application/json' => ['example' => [
                            'content' => 'Meet your new everyday favourite: a soft, breathable red cotton tee with a classic crew neck.',
                        ]]]),
                    ),
                    '400' => new Model\Response(description: 'Magic AI text generation is disabled.'),
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
            input: AdminMagicAiContentInput::class,
            processor: AdminMagicAiProcessor::class,
            read: false,
            description: 'Generate text content with Magic AI.',
        ),
    ],
)]
class AdminMagicAiContent
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['mutation'])]
    public ?int $id = null;

    #[ApiProperty(writable: false, description: 'The generated text.')]
    #[Groups(['mutation'])]
    public ?string $content = null;
}
