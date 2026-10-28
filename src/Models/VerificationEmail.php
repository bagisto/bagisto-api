<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\VerificationEmailInput;
use Webkul\BagistoApi\State\VerificationEmailProcessor;

/**
 * Sends the account verification email again.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'VerificationEmail',
    operations: [
        new Post(
            uriTemplate: '/verification-emails',
            input: VerificationEmailInput::class,
            output: self::class,
            processor: VerificationEmailProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Resend verification email',
                description: 'Generates a new verification token and emails the verification link again. The response is the same whether or not the email belongs to an account, so it cannot be used to find accounts.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'example' => 'john@example.com'],
                                ],
                            ],
                            'example' => ['email' => 'john@example.com'],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Email sent, or no account uses that address.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Verification email sent.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Missing or invalid email.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: VerificationEmailInput::class,
            output: self::class,
            processor: VerificationEmailProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ],
)]
class VerificationEmail
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
