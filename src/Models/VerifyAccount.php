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

/**
 * Verifies a customer account with the token from the verification email.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'VerifyAccount',
    operations: [
        new Post(
            uriTemplate: '/verify-accounts',
            input: VerifyAccountInput::class,
            output: self::class,
            processor: VerifyAccountProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Verify account',
                description: 'Verifies a customer account with the token from the verification email, so the customer can sign in. The token is the last segment of the link in that email (`/customer/verify-account/{token}`). Sent only when Configuration → Customer → Settings → Email Verification is on. No customer login is needed. The emailed link opens the storefront verify page; an app that wants to verify in-app should open that link as a deep link and call this endpoint.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token'],
                                'properties' => [
                                    'token' => ['type' => 'string', 'example' => '5d41402abc4b2a76b9719d911017c592'],
                                ],
                            ],
                            'example' => ['token' => '5d41402abc4b2a76b9719d911017c592'],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Account verified.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Your account has been verified. You can now sign in.',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(description: 'Missing token, or no account matches it.'),
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
    ],
)]
class VerifyAccount
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
