<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\VerifyAccountInput;
use Webkul\BagistoApi\State\VerifyAccountProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'VerifyAccount',
    operations: [
        new Post(
            uriTemplate: '/verify-accounts',
            processor: VerifyAccountProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Verify customer account with email token',
                description: 'Verifies customer registration using the email verification token sent upon sign-up.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token'],

                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'example' => '64-char-token-here',
                                    ],
                                ],
                            ],

                            'example' => [
                                'token' => '64-char-token-here',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Verification result',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Your account has been verified successfully.',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: VerifyAccountInput::class,
            output: self::class,
            processor: VerifyAccountProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class VerifyAccount
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
