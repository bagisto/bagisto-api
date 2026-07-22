<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminEuWithdrawalActionInput;
use Webkul\BagistoApi\Admin\Dto\AdminEuWithdrawalDeclineInput;
use Webkul\BagistoApi\Admin\Dto\AdminEuWithdrawalMarkRefundedInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalItemProvider;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalProcessor;
use Webkul\BagistoApi\Admin\State\AdminEuWithdrawalWriteProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminEuWithdrawal',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/eu-withdrawals',
            provider: AdminEuWithdrawalCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: EU Withdrawal'],
                summary: 'List EU right-of-withdrawal declarations',
                description: 'Filters: order_increment_id (LIKE), customer_email (LIKE), status (received|refunded|declined), channel_code, received_at_from/to, confirmation_sent_at_from/to. Sort: id (default desc), received_at, status. Permission: sales.eu_withdrawals.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The withdrawal declarations.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 7,
                                            'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                            'orderId' => 12,
                                            'orderIncrementId' => '000000012',
                                            'customerId' => 5,
                                            'customerName' => 'Jane Doe',
                                            'customerEmail' => 'jane@example.com',
                                            'isGuest' => false,
                                            'channelId' => 1,
                                            'channelCode' => 'default',
                                            'locale' => 'en',
                                            'reasonText' => 'Changed my mind.',
                                            'status' => 'received',
                                            'receivedAt' => '2026-07-20T09:00:00+00:00',
                                            'confirmationSentAt' => '2026-07-20T09:00:05+00:00',
                                            'finalConfirmationSentAt' => null,
                                            'confirmationError' => null,
                                            'declinedAt' => null,
                                            'declinedReason' => null,
                                            'declinedByUserId' => null,
                                            'declinedByName' => null,
                                            'refundedAt' => null,
                                            'refundedByUserId' => null,
                                            'refundedByName' => null,
                                            'refundNote' => null,
                                            'message' => null,
                                            'createdAt' => '2026-07-20T09:00:00+00:00',
                                            'updatedAt' => '2026-07-20T09:00:05+00:00',
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
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
            provider: AdminEuWithdrawalItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: EU Withdrawal'],
                summary: 'Get a withdrawal declaration (evidence + timeline)',
                responses: [
                    '200' => new Model\Response(
                        description: 'The withdrawal declaration with its full evidence timeline.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                    'orderId' => 12,
                                    'orderIncrementId' => '000000012',
                                    'customerId' => 5,
                                    'customerName' => 'Jane Doe',
                                    'customerEmail' => 'jane@example.com',
                                    'isGuest' => false,
                                    'channelId' => 1,
                                    'channelCode' => 'default',
                                    'locale' => 'en',
                                    'reasonText' => 'Changed my mind.',
                                    'status' => 'refunded',
                                    'receivedAt' => '2026-07-20T09:00:00+00:00',
                                    'confirmationSentAt' => '2026-07-20T09:00:05+00:00',
                                    'finalConfirmationSentAt' => '2026-07-21T14:00:00+00:00',
                                    'confirmationError' => null,
                                    'declinedAt' => null,
                                    'declinedReason' => null,
                                    'declinedByUserId' => null,
                                    'declinedByName' => null,
                                    'refundedAt' => '2026-07-21T14:00:00+00:00',
                                    'refundedByUserId' => 1,
                                    'refundedByName' => 'Example Admin',
                                    'refundNote' => 'Refunded via original payment method.',
                                    'message' => null,
                                    'createdAt' => '2026-07-20T09:00:00+00:00',
                                    'updatedAt' => '2026-07-21T14:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals/{id}/decline',
            requirements: ['id' => '\d+'],
            input: AdminEuWithdrawalDeclineInput::class,
            provider: AdminEuWithdrawalWriteProvider::class,
            processor: AdminEuWithdrawalProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: EU Withdrawal'],
                summary: 'Decline a withdrawal (merchant contests entitlement)',
                description: 'Sets status=declined and clears any prior refund metadata. Permission: sales.eu_withdrawals.decline.',
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['declined_reason'],
                                'properties' => [
                                    'declined_reason' => ['type' => 'string', 'example' => 'Item was a personalised good, exempt from withdrawal.'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The declined withdrawal declaration.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                    'orderIncrementId' => '000000012',
                                    'customerEmail' => 'jane@example.com',
                                    'status' => 'declined',
                                    'declinedAt' => '2026-07-21T15:00:00+00:00',
                                    'declinedReason' => 'Item was a personalised good, exempt from withdrawal.',
                                    'declinedByUserId' => 1,
                                    'declinedByName' => 'Example Admin',
                                    'refundedAt' => null,
                                    'refundNote' => null,
                                    'message' => 'Withdrawal declined.',
                                    'updatedAt' => '2026-07-21T15:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals/{id}/mark-refunded',
            requirements: ['id' => '\d+'],
            input: AdminEuWithdrawalMarkRefundedInput::class,
            provider: AdminEuWithdrawalWriteProvider::class,
            processor: AdminEuWithdrawalProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: EU Withdrawal'],
                summary: 'Mark a withdrawal refunded (recorded out-of-band)',
                description: 'Sets status=refunded and clears any prior decline metadata. Permission: sales.eu_withdrawals.mark_refunded.',
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'refund_note' => ['type' => 'string', 'example' => 'Refunded via original payment method.'],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The refunded withdrawal declaration.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                    'orderIncrementId' => '000000012',
                                    'customerEmail' => 'jane@example.com',
                                    'status' => 'refunded',
                                    'declinedAt' => null,
                                    'declinedReason' => null,
                                    'refundedAt' => '2026-07-21T14:00:00+00:00',
                                    'refundedByUserId' => 1,
                                    'refundedByName' => 'Example Admin',
                                    'refundNote' => 'Refunded via original payment method.',
                                    'message' => 'Withdrawal marked as refunded.',
                                    'updatedAt' => '2026-07-21T14:00:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals/{id}/resend-confirmation',
            requirements: ['id' => '\d+'],
            input: AdminEuWithdrawalActionInput::class,
            provider: AdminEuWithdrawalWriteProvider::class,
            processor: AdminEuWithdrawalProcessor::class,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => new \stdClass,
                        ],
                    ]),
                ),
                tags: ['Admin Sales: EU Withdrawal'],
                summary: 'Resend the durable-medium confirmation email',
                description: 'Empty body. Re-sends the confirmation email in the declaration\'s locale. Permission: sales.eu_withdrawals.resend_confirmation.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The declaration after the confirmation email was re-sent (confirmationSentAt refreshed).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 7,
                                    'uuid' => 'b2f1c0de-5a2e-4d7a-9f2e-3c1a2b4d5e6f',
                                    'orderIncrementId' => '000000012',
                                    'customerEmail' => 'jane@example.com',
                                    'status' => 'received',
                                    'confirmationSentAt' => '2026-07-21T16:30:00+00:00',
                                    'confirmationError' => null,
                                    'message' => 'Confirmation email re-sent.',
                                    'updatedAt' => '2026-07-21T16:30:00+00:00',
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminEuWithdrawalCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'order_increment_id' => ['type' => 'String'],
                'customer_email' => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'channel_code' => ['type' => 'String'],
                'received_at_from' => ['type' => 'String'],
                'received_at_to' => ['type' => 'String'],
                'confirmation_sent_at_from' => ['type' => 'String'],
                'confirmation_sent_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
        new Query(provider: AdminEuWithdrawalItemProvider::class),
        new Mutation(name: 'decline', input: AdminEuWithdrawalDeclineInput::class, processor: AdminEuWithdrawalProcessor::class),
        new Mutation(name: 'markRefunded', input: AdminEuWithdrawalMarkRefundedInput::class, processor: AdminEuWithdrawalProcessor::class),
        new Mutation(name: 'resendConfirmation', input: AdminEuWithdrawalActionInput::class, processor: AdminEuWithdrawalProcessor::class),
    ],
)]
class AdminEuWithdrawal
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $uuid = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?int $customer_id = null;

    public ?string $customer_name = null;

    public ?string $customer_email = null;

    public ?bool $is_guest = null;

    public ?int $channel_id = null;

    public ?string $channel_code = null;

    public ?string $locale = null;

    public ?string $reason_text = null;

    public ?string $status = null;

    public ?string $received_at = null;

    public ?string $confirmation_sent_at = null;

    public ?string $final_confirmation_sent_at = null;

    public ?string $confirmation_error = null;

    public ?string $declined_at = null;

    public ?string $declined_reason = null;

    public ?int $declined_by_user_id = null;

    public ?string $declined_by_name = null;

    public ?string $refunded_at = null;

    public ?int $refunded_by_user_id = null;

    public ?string $refunded_by_name = null;

    public ?string $refund_note = null;

    public ?string $message = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;
}
