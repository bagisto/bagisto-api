<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;
use Webkul\BagistoApi\Dto\CreateEuWithdrawalInput;
use Webkul\BagistoApi\State\EuWithdrawalProcessor;
use Webkul\BagistoApi\State\EuWithdrawalProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'EuWithdrawal',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/eu-withdrawals',
            provider: EuWithdrawalProvider::class,
            openapi: new Model\Operation(
                tags: ['EU Withdrawal'],
                summary: 'List the authenticated customer\'s EU right-of-withdrawal declarations',
                description: 'Returns the customer\'s own withdrawal declarations, newest first. Requires a customer Bearer token.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The customer\'s withdrawal declarations.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 7,
                                        'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                        'orderId' => 12,
                                        'orderIncrementId' => '000000012',
                                        'isGuest' => false,
                                        'customerEmail' => 'jane@example.com',
                                        'status' => 'received',
                                        'reasonText' => 'Changed my mind.',
                                        'receivedAt' => '2026-07-20T09:00:00+00:00',
                                        'confirmationSentAt' => '2026-07-20T09:00:05+00:00',
                                        'declinedAt' => null,
                                        'declinedReason' => null,
                                        'refundedAt' => null,
                                        'refundNote' => null,
                                        'createdAt' => '2026-07-20T09:00:00+00:00',
                                        'updatedAt' => '2026-07-20T09:00:05+00:00',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/eu-withdrawals/{id}',
            requirements: ['id' => '\d+'],
            provider: EuWithdrawalProvider::class,
            openapi: new Model\Operation(
                tags: ['EU Withdrawal'],
                summary: 'Get one of the customer\'s withdrawal declarations',
                description: 'Full detail of a single withdrawal owned by the authenticated customer (ownership via the underlying order). 404 if it is not the customer\'s.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The withdrawal declaration detail.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                    'orderId' => 12,
                                    'orderIncrementId' => '000000012',
                                    'isGuest' => false,
                                    'customerEmail' => 'jane@example.com',
                                    'status' => 'received',
                                    'reasonText' => 'Changed my mind.',
                                    'receivedAt' => '2026-07-20T09:00:00+00:00',
                                    'confirmationSentAt' => '2026-07-20T09:00:05+00:00',
                                    'declinedAt' => null,
                                    'declinedReason' => null,
                                    'refundedAt' => null,
                                    'refundNote' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T09:00:05+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals',
            processor: EuWithdrawalProcessor::class,
            openapi: new Model\Operation(
                tags: ['EU Withdrawal'],
                summary: 'File an EU right-of-withdrawal declaration (authenticated customer)',
                description: 'Records a withdrawal for one of the customer\'s own orders and triggers the durable-medium confirmation email. Idempotent — a second call for the same order returns the existing declaration. 404 if the order is not the customer\'s or the channel does not have EU withdrawal enabled.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['order_id'],
                                'properties' => [
                                    'order_id' => ['type' => 'integer', 'example' => 12],
                                    'reason_text' => ['type' => 'string', 'example' => 'Changed my mind.'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Model\Response(
                        description: 'The created (or existing, idempotent) withdrawal declaration.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                    'orderId' => 12,
                                    'orderIncrementId' => '000000012',
                                    'isGuest' => false,
                                    'customerEmail' => 'jane@example.com',
                                    'status' => 'received',
                                    'reasonText' => 'Changed my mind.',
                                    'receivedAt' => '2026-07-20T09:00:00+00:00',
                                    'confirmationSentAt' => '2026-07-20T09:00:05+00:00',
                                    'declinedAt' => null,
                                    'declinedReason' => null,
                                    'refundedAt' => null,
                                    'refundNote' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-20T09:00:05+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(provider: EuWithdrawalProvider::class),
        new QueryCollection(
            provider: EuWithdrawalProvider::class,
            paginationType: 'cursor',
            args: [
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Mutation(name: 'create', input: CreateEuWithdrawalInput::class, processor: EuWithdrawalProcessor::class),
    ],
)]
class EuWithdrawal implements SnakeCaseFieldsResource
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

    public ?string $declined_at = null;

    public ?string $declined_reason = null;

    public ?string $refunded_at = null;

    public ?string $refund_note = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
