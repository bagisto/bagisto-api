<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\ResetPasswordInput;
use Webkul\BagistoApi\State\ResetPasswordProcessor;

/**
 * Sets a new customer password with the token from the forgot-password email.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ResetPassword',
    operations: [
        new Post(
            uriTemplate: '/reset-passwords',
            input: ResetPasswordInput::class,
            output: self::class,
            processor: ResetPasswordProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Reset password',
                description: 'Sets a new password with the token from the forgot-password email. The token is the last segment of the reset link (`/customer/reset-password/{token}`). A token can be used once and expires after the time set in the auth config. No customer login is needed.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token', 'email', 'password', 'passwordConfirmation'],
                                'properties' => [
                                    'token' => ['type' => 'string', 'example' => '5f2b9c…'],
                                    'email' => ['type' => 'string', 'example' => 'john@example.com'],
                                    'password' => ['type' => 'string', 'minLength' => 6, 'example' => 'NewSecret123'],
                                    'passwordConfirmation' => ['type' => 'string', 'example' => 'NewSecret123'],
                                ],
                            ],
                            'example' => [
                                'token' => '5f2b9c…',
                                'email' => 'john@example.com',
                                'password' => 'NewSecret123',
                                'passwordConfirmation' => 'NewSecret123',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Password changed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Your password has been reset. You can now sign in with the new password.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Missing or invalid input, or the token is invalid or expired.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ResetPasswordInput::class,
            output: self::class,
            processor: ResetPasswordProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ],
)]
class ResetPassword
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
