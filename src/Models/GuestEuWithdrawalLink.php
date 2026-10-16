<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Dto\CreateGuestEuWithdrawalLinkInput;
use Webkul\BagistoApi\State\GuestEuWithdrawalLinkProcessor;

/**
 * Step 1 of a guest withdrawal: emails a signed link to the address on the guest order.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GuestEuWithdrawalLink',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/eu-withdrawals/guest/lookup',
            input: CreateGuestEuWithdrawalLinkInput::class,
            output: self::class,
            processor: GuestEuWithdrawalLinkProcessor::class,
            read: false,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Model\Operation(
                tags: ['EU Withdrawal'],
                summary: 'Request a guest withdrawal link',
                description: 'Step 1 of a guest withdrawal. When the order number and email match a guest order on an EU-enabled channel, a signed link valid for 24 hours is emailed to that address. Pass that link to `POST /api/shop/eu-withdrawals/guest` to file the withdrawal. The response is the same whether or not the order matched, so it cannot be used to discover orders. Limited to 5 requests a minute per IP.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order_increment_id', 'email'],
                                'properties' => [
                                    'order_increment_id' => ['type' => 'string', 'example' => '1000123'],
                                    'email' => ['type' => 'string', 'example' => 'guest@example.com'],
                                ],
                            ],
                            'example' => [
                                'order_increment_id' => '1000123',
                                'email' => 'guest@example.com',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'Accepted. The same answer is returned whether or not the order matched.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'If the order number and email match a guest order, we have emailed a link to withdraw from it.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'EU withdrawal is switched off for this channel.'),
                    '422' => new Model\Response(description: 'Missing or invalid order number or email.'),
                    '429' => new Model\Response(description: 'Too many requests from this IP.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: CreateGuestEuWithdrawalLinkInput::class,
            output: self::class,
            processor: GuestEuWithdrawalLinkProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ],
)]
class GuestEuWithdrawalLink
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}
