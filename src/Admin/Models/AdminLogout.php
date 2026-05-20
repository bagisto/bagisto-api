<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\AdminLogoutInput;
use Webkul\BagistoApi\Admin\State\AdminLogoutProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminLogout',
    operations: [
        new Post(
            uriTemplate: '/logout',
            input: AdminLogoutInput::class,
            output: self::class,
            processor: AdminLogoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: ['groups' => ['mutation'], 'skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Admin logout',
                description: 'Revoke the current admin Bearer token. Pass "all": true to revoke every token for the admin.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'all' => ['type' => 'boolean', 'example' => false],
                                ],
                            ],
                            'example' => [
                                'all' => false,
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Token revoked.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Logged out successfully.',
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
            input: AdminLogoutInput::class,
            output: self::class,
            processor: AdminLogoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: ['groups' => ['mutation']],
        ),
    ]
)]
class AdminLogout
{
    #[ApiProperty(identifier: false, writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
