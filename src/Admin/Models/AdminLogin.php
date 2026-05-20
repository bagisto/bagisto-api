<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Webkul\BagistoApi\Admin\Dto\AdminLoginInput;
use Webkul\BagistoApi\Admin\State\AdminLoginProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminLogin',
    operations: [
        new Post(
            uriTemplate: '/login',
            description: 'Authenticate an admin and retrieve an API token.',
            input: AdminLoginInput::class,
            output: self::class,
            processor: AdminLoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: ['skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Admin login',
                description: 'Authenticate an admin user with email and password. Returns a Bearer token for subsequent admin API calls.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['email', 'password'],
                                'properties' => [
                                    'email'    => ['type' => 'string', 'format' => 'email', 'example' => 'admin@example.com'],
                                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'admin123'],
                                ],
                            ],
                            'example' => [
                                'email'    => 'admin@example.com',
                                'password' => 'admin123',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Admin authenticated; Bearer token issued.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id'      => 1,
                                    'name'    => 'Example Admin',
                                    'email'   => 'admin@example.com',
                                    'token'   => '12|ks06JCndg5FHb8WbfF6ZR8jGq23168m9gm37J9Cmz4xah8',
                                    'success' => true,
                                    'message' => 'Logged in successfully.',
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
            input: AdminLoginInput::class,
            output: self::class,
            processor: AdminLoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class AdminLogin
{
    #[ApiProperty(identifier: false, writable: false, readable: true, required: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true, required: false)]
    public ?int $_id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $name = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $email = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $token = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
