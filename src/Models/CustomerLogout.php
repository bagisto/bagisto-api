<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\LogoutInput;
use Webkul\BagistoApi\State\LogoutProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Logout',
    operations: [
        new Post(
            uriTemplate: '/customer/logout',
            input: LogoutInput::class,
            output: CustomerLogout::class,
            processor: LogoutProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            openapi: new Model\Operation(
                tags: ['Customer'],
                summary: 'Customer logout',
                description: 'Logout the authenticated customer and revoke the Bearer token.',
                requestBody: new Model\RequestBody(
                    description: 'Empty body',
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: LogoutInput::class,
            output: self::class,
            processor: LogoutProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
            ],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class CustomerLogout
{
    #[ApiProperty(identifier: false, writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    #[Groups(['mutation'])]
    public ?string $message = null;
}
