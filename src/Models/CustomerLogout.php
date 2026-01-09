<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Dto\LogoutInput;
use Webkul\BagistoApi\State\LogoutProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'Logout',
    operations: [],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: LogoutInput::class,
            output: self::class,
            processor: LogoutProcessor::class,
            normalizationContext: [
                'groups' => ['mutation'],
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
