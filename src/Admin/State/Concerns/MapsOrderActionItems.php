<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

trait MapsOrderActionItems
{
    /**
     * Map item.
     */
    protected function mapItem($row, string $currency): array
    {
        return [
            'id' => (int) $row->id,
            'orderItemId' => $row->order_item_id !== null ? (int) $row->order_item_id : null,
            'sku' => $row->sku,
            'name' => $row->name,
            'qty' => $row->qty !== null ? (int) $row->qty : null,
            'price' => $row->price !== null ? (float) $row->price : null,
            'formattedPrice' => core()->formatPrice((float) $row->price, $currency),
            'basePrice' => $row->base_price !== null ? (float) $row->base_price : null,
            'basePriceInclTax' => $row->base_price_incl_tax !== null ? (float) $row->base_price_incl_tax : null,
            'total' => $row->total !== null ? (float) $row->total : null,
            'formattedTotal' => core()->formatPrice((float) $row->total, $currency),
            'baseTotal' => $row->base_total !== null ? (float) $row->base_total : null,
            'baseTotalInclTax' => $row->base_total_incl_tax !== null ? (float) $row->base_total_incl_tax : null,
            'taxAmount' => $row->tax_amount !== null ? (float) $row->tax_amount : null,
            'formattedTaxAmount' => core()->formatPrice((float) $row->tax_amount, $currency),
            'discountAmount' => $row->discount_amount !== null ? (float) $row->discount_amount : null,
            'formattedDiscountAmount' => core()->formatPrice((float) $row->discount_amount, $currency),
            'productId' => $row->product_id !== null ? (int) $row->product_id : null,
            'productType' => $this->resolveProductType($row),
            'baseImageUrl' => $this->resolveItemImage($row),
            'additional' => is_array($row->additional) ? $row->additional : null,
        ];
    }

    /**
     * The line-item table's `product_type` column is the polymorphic morph
     * class (e.g. Webkul\Product\Models\Product), not the catalog type. Resolve.
     */
    private function resolveProductType($row): ?string
    {
        try {
            if ($type = $row->order_item?->type) {
                return $type;
            }
        } catch (\Throwable) {
        }

        $productType = $row->product_type ?? null;

        return ($productType && ! str_contains((string) $productType, '\\')) ? $productType : null;
    }

    /**
     * Product base image — the admin invoice/refund view renders it beside each line item.
     */
    private function resolveItemImage($row): ?string
    {
        try {
            return $row->product?->base_image_url;
        } catch (\Throwable) {
            return null;
        }
    }
}
