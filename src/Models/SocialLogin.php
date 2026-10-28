<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\SocialLoginInput;
use Webkul\BagistoApi\State\SocialLoginProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'SocialLogin',
    operations: [
        new Post(
            uriTemplate: '/customers/social-login',
            description: 'Sign in (or sign up) with a Google, Facebook or LinkedIn token the client already holds.',
            input: SocialLoginInput::class,
            output: self::class,
            processor: SocialLoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Social login',
                description: 'Verify a provider token the app/web already obtained (Google Identity Services or a native SDK — no redirect), then sign the customer in, creating and linking the account on first sight. Returns a Bearer `token` for subsequent calls. Providers: `google`, `facebook`, `linkedin`.',
                requestBody: new RequestBody(
                    description: 'A provider and one of idToken / accessToken.',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['provider'],
                                'properties' => [
                                    'provider' => ['type' => 'string', 'enum' => ['google', 'facebook', 'linkedin'], 'example' => 'google'],
                                    'idToken' => ['type' => 'string', 'description' => 'Google ID token (google only).', 'example' => 'eyJhbGciOi…'],
                                    'accessToken' => ['type' => 'string', 'description' => 'OAuth access token (facebook / linkedin, or google).'],
                                    'deviceToken' => ['type' => 'string', 'description' => 'Optional FCM token to register push in the same call.'],
                                ],
                            ],
                            'example' => [
                                'provider' => 'google',
                                'idToken' => 'eyJhbGciOi…',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Signed in. Store `token` as the Bearer token. `isNewCustomer` marks a first-time sign-up.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1782,
                                    'token' => '1550|1LMsQ6…',
                                    'apiToken' => 'rjTLsuOs…',
                                    'firstName' => 'Nadia',
                                    'lastName' => 'Rahman',
                                    'email' => 'nadia@example.com',
                                    'phone' => null,
                                    'isNewCustomer' => true,
                                    'success' => true,
                                    'message' => 'Signed in successfully.',
                                    'code' => null,
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
            input: SocialLoginInput::class,
            output: self::class,
            processor: SocialLoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ],
)]
class SocialLogin implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: false, writable: false, readable: true, required: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true, required: false)]
    public ?int $_id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $token = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $api_token = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $first_name = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $last_name = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $email = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $phone = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $is_new_customer = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $code = null;
}
