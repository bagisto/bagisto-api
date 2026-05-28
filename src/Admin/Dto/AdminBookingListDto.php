<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Slim row for the admin Bookings listing (GET /api/admin/bookings).
 * Mirrors Webkul\Admin\DataGrids\Sales\BookingDataGrid — one row per
 * booking line (the underlying `bookings` table row), with the linked
 * order increment_id and the booking window.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminBookingListDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $orderIncrementId = null;

    public ?int $orderItemId = null;

    public ?int $productId = null;

    public ?string $productSku = null;

    public ?string $productName = null;

    public ?int $qty = null;

    /** Unix timestamp — start of booking window (may be null for non-time-based types). */
    public ?int $from = null;

    /** Unix timestamp — end of booking window. */
    public ?int $to = null;

    public ?string $fromFormatted = null;

    public ?string $toFormatted = null;

    public ?string $createdAt = null;
}
