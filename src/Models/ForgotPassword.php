<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Webkul\BagistoApi\Dto\ForgotPasswordInput;
use Webkul\BagistoApi\State\ForgotPasswordProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ForgotPassword',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ForgotPasswordInput::class,
            output: self::class,
            processor: ForgotPasswordProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class ForgotPassword
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
