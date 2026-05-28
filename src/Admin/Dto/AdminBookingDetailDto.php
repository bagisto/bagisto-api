<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;

/**
 * Full payload for GET /api/admin/bookings/{id} + adminBooking(id:).
 * Includes the booking row + booking product reference + booking type
 * (default/appointment/event/rental/table) and a slim summary of the
 * order it belongs to.
 */
#[ApiResource(operations: [], graphQlOperations: [], normalizationContext: ['skip_null_values' => false])]
class AdminBookingDetailDto
{
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    public ?int $orderId = null;

    public ?string $orderIncrementId = null;

    public ?int $orderItemId = null;

    public ?int $productId = null;

    public ?string $productSku = null;

    public ?string $productName = null;

    /** Booking product sub-type: default / appointment / event / rental / table. */
    public ?string $bookingType = null;

    public ?int $qty = null;

    public ?int $from = null;

    public ?int $to = null;

    public ?string $fromFormatted = null;

    public ?string $toFormatted = null;

    /** Linked event-ticket id when bookingType = event. */
    public ?int $bookingProductEventTicketId = null;

    /** Slim order summary so the caller can render without a follow-up fetch. */
    public ?array $order = null;

    /** Slim order-item summary (sku/name/qty) for the parent line item. */
    public ?array $orderItem = null;

    public ?string $createdAt = null;
}
