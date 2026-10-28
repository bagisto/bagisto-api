<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\CreateGuestEuWithdrawalInput;
use Webkul\BagistoApi\State\EuWithdrawalProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'GuestEuWithdrawal',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/eu-withdrawals/guest',
            processor: EuWithdrawalProcessor::class,
            read: false,
            openapi: new Model\Operation(
                tags: ['EU Withdrawal'],
                summary: 'File an EU right-of-withdrawal declaration (guest)',
                description: 'Step 2 of a guest withdrawal. Send the signed `link` emailed by `POST /api/shop/eu-withdrawals/guest/lookup`; it proves the guest owns the order email, as on the storefront. The link is valid for 24 hours. Idempotent: sending the same link again returns the existing declaration with its current status. Triggers the durable-medium confirmation email. Limited to 20 requests a minute per IP.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['link'],
                                'properties' => [
                                    'link' => ['type' => 'string', 'example' => 'https://shop.example.com/withdraw/34/create?expires=1784539800&signature=3f1c…'],
                                    'reason_text' => ['type' => 'string', 'example' => 'Changed my mind.'],
                                ],
                            ],
                            'example' => [
                                'link' => 'https://shop.example.com/withdraw/34/create?expires=1784539800&signature=3f1c…',
                                'reason_text' => 'Changed my mind.',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created (or existing, idempotent) guest withdrawal declaration.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 9,
                                    'uuid' => 'a1c2e3f4-6b7c-4d8e-9a0b-1c2d3e4f5a6b',
                                    'orderId' => 34,
                                    'orderIncrementId' => '1000123',
                                    'isGuest' => true,
                                    'customerEmail' => 'guest@example.com',
                                    'status' => 'received',
                                    'reasonText' => 'Changed my mind.',
                                    'receivedAt' => '2026-07-20T09:30:00+00:00',
                                    'confirmationSentAt' => '2026-07-20T09:30:05+00:00',
                                    'createdAt' => '2026-07-20T09:30:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'The order is not a guest order, or EU withdrawal is off for its channel.'),
                    '422' => new Model\Response(description: 'The link is missing, invalid or expired.'),
                    '429' => new Model\Response(description: 'Too many requests from this IP.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(name: 'create', input: CreateGuestEuWithdrawalInput::class, processor: EuWithdrawalProcessor::class),
    ],
)]
class GuestEuWithdrawal implements SnakeCaseFieldsResource
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $uuid = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?bool $is_guest = null;

    public ?string $customer_email = null;

    public ?string $status = null;

    public ?string $reason_text = null;

    public ?string $received_at = null;

    public ?string $confirmation_sent_at = null;

    public ?string $created_at = null;
}
