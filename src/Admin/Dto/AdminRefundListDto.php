<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Slim row for the admin Refunds listing (GET /api/admin/refunds).
 * Mirrors Webkul\Admin\DataGrids\Sales\OrderRefundDataGrid.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminRefundListDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $orderIncrementId = null;

    public ?string $state = null;

    public ?float $baseGrandTotal = null;

    public ?string $formattedBaseGrandTotal = null;

    public ?string $billedTo = null;

    public ?string $createdAt = null;
}
