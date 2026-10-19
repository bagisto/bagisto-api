<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\GuestEuWithdrawalConfirmationQueryResolver;
use Webkul\BagistoApi\State\GuestEuWithdrawalConfirmationProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GuestEuWithdrawalConfirmation',
    operations: [
        new Get(
            uriTemplate: '/eu-withdrawals/guest/{uuid}',
            provider: GuestEuWithdrawalConfirmationProvider::class,
            output: self::class,
            openapi: new Model\Operation(
                tags: ['EU Withdrawal'],
                summary: 'Read a guest withdrawal confirmation',
                description: 'Requires both the withdrawal UUID and the email address used for the guest order. A generic 404 is returned for invalid ownership proof, missing records, or disabled EU withdrawal.',
                parameters: [
                    new Model\Parameter('uuid', 'path', 'Guest withdrawal UUID', true, schema: [
                        'type' => 'string',
                        'format' => 'uuid',
                    ]),
                    new Model\Parameter('email', 'query', 'Email address used on the guest order', true, schema: [
                        'type' => 'string',
                        'format' => 'email',
                    ]),
                ],
                responses: [
                    200 => new Model\Response(description: 'The guest withdrawal confirmation receipt.'),
                    404 => new Model\Response(description: 'Not found or ownership could not be verified.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: GuestEuWithdrawalConfirmationQueryResolver::class,
            output: self::class,
            args: [
                'uuid' => ['type' => 'String!'],
                'email' => ['type' => 'String!'],
            ],
            description: 'Read a guest withdrawal confirmation using the withdrawal UUID and guest order email.',
        ),
    ],
)]
class GuestEuWithdrawalConfirmation
{
    /**
     * The uuid.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $uuid = null;

    /**
     * The order increment id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $orderIncrementId = null;

    /**
     * The status.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $status = null;

    /**
     * The received at.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $receivedAt = null;
}
