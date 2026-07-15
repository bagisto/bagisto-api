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
                description: 'Records a withdrawal for a guest order, proving ownership with the order increment id + email. Idempotent. Triggers the durable-medium confirmation email. 404 (constant response) when the order/email do not match a guest order on an EU-enabled channel.',
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
                                    'reason_text' => ['type' => 'string', 'example' => 'Changed my mind.'],
                                ],
                            ],
                        ],
                    ]),
                ),
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
