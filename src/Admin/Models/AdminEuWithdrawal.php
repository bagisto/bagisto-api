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
            openapi: new Model\Operation(tags: ['Admin Sales: EU Withdrawal'], summary: 'List EU right-of-withdrawal declarations', description: 'Filters: order_increment_id (LIKE), customer_email (LIKE), status (received|refunded|declined), channel_code, received_at_from/to, confirmation_sent_at_from/to. Sort: id (default desc), received_at, status. Permission: sales.eu_withdrawals.'),
        ),
        new Get(
            uriTemplate: '/eu-withdrawals/{id}',
            requirements: ['id' => '\d+'],
            provider: AdminEuWithdrawalItemProvider::class,
            openapi: new Model\Operation(tags: ['Admin Sales: EU Withdrawal'], summary: 'Get a withdrawal declaration (evidence + timeline)'),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals/{id}/decline',
            requirements: ['id' => '\d+'],
            input: AdminEuWithdrawalDeclineInput::class,
            provider: AdminEuWithdrawalWriteProvider::class,
            processor: AdminEuWithdrawalProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: EU Withdrawal'], summary: 'Decline a withdrawal (merchant contests entitlement)', description: 'Body `{ declined_reason }`. Sets status=declined and clears any prior refund metadata. Permission: sales.eu_withdrawals.decline.'),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals/{id}/mark-refunded',
            requirements: ['id' => '\d+'],
            input: AdminEuWithdrawalMarkRefundedInput::class,
            provider: AdminEuWithdrawalWriteProvider::class,
            processor: AdminEuWithdrawalProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: EU Withdrawal'], summary: 'Mark a withdrawal refunded (recorded out-of-band)', description: 'Body `{ refund_note? }`. Sets status=refunded and clears any prior decline metadata. Permission: sales.eu_withdrawals.mark_refunded.'),
        ),
        new Post(
            uriTemplate: '/eu-withdrawals/{id}/resend-confirmation',
            requirements: ['id' => '\d+'],
            input: AdminEuWithdrawalActionInput::class,
            provider: AdminEuWithdrawalWriteProvider::class,
            processor: AdminEuWithdrawalProcessor::class,
            openapi: new Model\Operation(tags: ['Admin Sales: EU Withdrawal'], summary: 'Resend the durable-medium confirmation email', description: 'Empty body. Re-sends the confirmation email in the declaration\'s locale. Permission: sales.eu_withdrawals.resend_confirmation.'),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminEuWithdrawalCollectionProvider::class,
            paginationType: 'cursor',
            args: [
                'order_increment_id'        => ['type' => 'String'],
                'customer_email'            => ['type' => 'String'],
                'status'                    => ['type' => 'String'],
                'channel_code'              => ['type' => 'String'],
                'received_at_from'          => ['type' => 'String'],
                'received_at_to'            => ['type' => 'String'],
                'confirmation_sent_at_from' => ['type' => 'String'],
                'confirmation_sent_at_to'   => ['type' => 'String'],
                'sort'                      => ['type' => 'String'],
                'order'                     => ['type' => 'String'],
                'first'                     => ['type' => 'Int'],
                'after'                     => ['type' => 'String'],
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
