<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Full payload for GET /api/admin/transactions/{id} + adminTransaction(id:)
 * GraphQL query. Returns the transaction row plus a slim summary of the
 * linked order.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminTransactionDetailDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $transactionId = null;

    public ?int $invoiceId = null;

    public ?int $orderId = null;

    public ?string $orderIncrementId = null;

    public ?float $amount = null;

    public ?string $formattedAmount = null;

    public ?string $status = null;

    public ?string $type = null;

    public ?string $paymentMethod = null;

    public ?string $paymentTitle = null;

    public ?array $data = null;

    public ?string $createdAt = null;

    public ?string $updatedAt = null;

    /** Slim summary of the order this transaction belongs to. */
    public ?array $order = null;
}
