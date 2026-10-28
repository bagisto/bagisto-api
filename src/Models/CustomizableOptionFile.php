<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CustomizableOptionFileInput;
use Webkul\BagistoApi\Dto\CustomizableOptionFileOutput;
use Webkul\BagistoApi\State\CustomizableOptionFileProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomizableOptionFile',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customizable-option-files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            processor: CustomizableOptionFileProcessor::class,
            status: 201,
            deserialize: false,
            read: false,
            validate: false,
            openapi: new Model\Operation(
                tags: ['Cart'],
                summary: 'Upload a file for a customizable option',
                description: 'Stages a file for a file-type customizable option and returns a token. Send the token on add-to-cart as customizableOptions: { "<optionId>": ["<token>"] }. Requires the storefront key and a cart/customer Bearer token.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['product_id', 'option_id', 'file'],

                                'properties' => [
                                    'product_id' => [
                                        'type' => 'integer',
                                        'example' => 2977,
                                    ],

                                    'option_id' => [
                                        'type' => 'integer',
                                        'example' => 11,
                                    ],

                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'File staged. Returns a token to send on add-to-cart.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'token' => 'q8f2…48chars',
                                    'fileName' => 'spec.pdf',
                                    'optionId' => 11,
                                ],
                            ],
                        ]),
                    ),
                    '403' => new Model\Response(description: 'The file does not belong to your cart.'),
                    '404' => new Model\Response(description: 'Product not found.'),
                    '422' => new Model\Response(description: 'Option is not a file option, or the file is missing / wrong extension / too large.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CustomizableOptionFileInput::class,
            output: CustomizableOptionFileOutput::class,
            processor: CustomizableOptionFileProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            description: 'Stage a file for a file-type customizable option and return the token. Send the file base64-encoded (raw or a data: URI) in `file`. Send the token on add-to-cart as customizableOptions: { "<optionId>": ["<token>"] }.',
        ),
    ],
)]
class CustomizableOptionFile
{
    /**
     * The id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    /**
     * The token.
     */
    #[ApiProperty(writable: false)]
    public ?string $token = null;

    /**
     * The file name.
     */
    #[ApiProperty(writable: false)]
    public ?string $fileName = null;

    /**
     * The option id.
     */
    #[ApiProperty(writable: false)]
    public ?int $optionId = null;
}
