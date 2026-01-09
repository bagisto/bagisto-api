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

    #[ApiProperty(writable: false, readable: true)]
    public ?string $apiToken = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $token = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;
}
