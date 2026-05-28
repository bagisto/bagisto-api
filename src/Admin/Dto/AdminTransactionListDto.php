<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Slim row for the admin Transactions listing (GET /api/admin/transactions).
 * Mirrors Webkul\Admin\DataGrids\Sales\OrderTransactionDataGrid.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminTransactionListDto
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

    public ?string $createdAt = null;
}
