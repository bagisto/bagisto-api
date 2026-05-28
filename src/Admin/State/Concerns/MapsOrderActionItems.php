<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\BagistoApi\Admin\Dto\OrderActionItemDto;

/**
 * Shared row-to-DTO mapper used by Invoice / Shipment / Refund detail
 * providers. Each repo writes the same fields on its line-item table
 * (`order_item_id`, `sku`, `name`, `qty`, `price`, `base_price`, `total`,
 * `tax_amount`, `discount_amount`, `product_id`, `product_type`, `additional`),
 * so the mapping is identical — extract once instead of three near-duplicates.
 */
trait MapsOrderActionItems
{
    protected function mapItem($row, string $currency): OrderActionItemDto
    {
        $dto = new OrderActionItemDto;

        $dto->id = (int) $row->id;
        $dto->orderItemId = $row->order_item_id !== null ? (int) $row->order_item_id : null;
        $dto->sku = $row->sku;
        $dto->name = $row->name;
        $dto->qty = $row->qty !== null ? (int) $row->qty : null;
        $dto->price = $row->price !== null ? (float) $row->price : null;
        $dto->basePrice = $row->base_price !== null ? (float) $row->base_price : null;
        $dto->total = $row->total !== null ? (float) $row->total : null;
        $dto->baseTotal = $row->base_total !== null ? (float) $row->base_total : null;
        $dto->taxAmount = $row->tax_amount !== null ? (float) $row->tax_amount : null;
        $dto->discountAmount = $row->discount_amount !== null ? (float) $row->discount_amount : null;
        $dto->productId = $row->product_id !== null ? (int) $row->product_id : null;
        $dto->productType = $row->product_type;
        $dto->additional = is_array($row->additional) ? $row->additional : null;

        $dto->formattedPrice = core()->formatPrice((float) $row->price, $currency);
        $dto->formattedTotal = core()->formatPrice((float) $row->total, $currency);
        $dto->formattedTaxAmount = core()->formatPrice((float) $row->tax_amount, $currency);
        $dto->formattedDiscountAmount = core()->formatPrice((float) $row->discount_amount, $currency);

        return $dto;
    }
}
