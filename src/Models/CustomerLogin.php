<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\LoginInput;
use Webkul\BagistoApi\State\LoginProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerLogin',
    operations: [
        new \ApiPlatform\Metadata\Post(
            uriTemplate: '/customer/login',
            description: 'Authenticate a customer and retrieve an API token.',
            input: LoginInput::class,
            output: self::class,
            processor: LoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer'],
                summary: 'Customer login',
                description: 'Authenticate a customer with email and password. Returns a Bearer token for subsequent API calls.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'Customer login credentials',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['email', 'password'],
                                'properties' => [
                                    'email'    => ['type' => 'string', 'format' => 'email', 'example' => 'john@example.com'],
                                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'Password123!'],
                                ],
                            ],
                            'example' => [
                                'email'    => 'john@example.com',
                                'password' => 'Password123!',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Authenticated. Use `token` as the Bearer token in the Authorization header for subsequent calls.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'       => 1529,
                                    '_id'      => 1529,
                                    'apiToken' => 'PPKI0VNiX5qehio2q1cMeMd6TCaIKofwQ4mb7y2zcy0japYwsyc',
                                    'token'    => '3762|wT7yGIo82N7T3r2YFc3iEcugycFjPQgG338DKnPXeaa96bad',
                                    'success'  => true,
                                    'message'  => 'You have logged in successfully',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new \ApiPlatform\OpenApi\Model\Response(description: 'Invalid email or password.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: LoginInput::class,
            output: self::class,
            processor: LoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class CustomerLogin
{
    #[ApiProperty(identifier: false, writable: false, readable: true, required: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true, required: false)]
    public ?int $_id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $apiToken = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $token = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;
}
