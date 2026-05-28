<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Slim row for the admin Invoices listing (GET /api/admin/invoices).
 * Mirrors the columns of Webkul\Admin\DataGrids\Sales\OrderInvoiceDataGrid.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminInvoiceListDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?string $incrementId = null;

    public ?int $orderId = null;

    public ?string $orderIncrementId = null;

    public ?string $state = null;

    public ?float $baseGrandTotal = null;

    public ?string $formattedBaseGrandTotal = null;

    public ?string $createdAt = null;
}
