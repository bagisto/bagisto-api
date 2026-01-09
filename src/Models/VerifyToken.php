<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\VerifyTokenInput;
use Webkul\BagistoApi\State\VerifyTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'VerifyToken',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: VerifyTokenInput::class,
            output: self::class,
            processor: VerifyTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class VerifyToken
{
    #[ApiProperty(identifier: false, writable: false, readable: true, required: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $firstName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $lastName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $email = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $isValid = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
