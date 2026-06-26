<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

/**
 * Shared row-to-array mapper used by Invoice / Shipment / Refund detail
 * providers. Each repo writes the same fields on its line-item table
 * (`order_item_id`, `sku`, `name`, `qty`, `price`, `base_price`, `total`,
 * `tax_amount`, `discount_amount`, `product_id`, `product_type`, `additional`),
 * so the mapping is identical — extract once instead of three near-duplicates.
 *
 * Returns a PLAIN associative array (camelCase keys), NOT a typed DTO. A typed
 * DTO array makes API Platform render the field as a GraphQL cursor connection
 * whose nodes resolve to all-null (it re-fetches each node by IRI, but the DTO
 * has no route) and as an IRI string over REST. A plain array renders as a
 * clean inline object list in REST and a queryable Iterable leaf in GraphQL —
 * the same convention AdminOrderDetail::toItem() uses. Columns absent on a
 * given line-item table (e.g. shipment_items has no tax_amount) resolve to null
 * harmlessly.
 */
trait MapsOrderActionItems
{
    protected function mapItem($row, string $currency): array
    {
        return [
            'id'                      => (int) $row->id,
            'orderItemId'             => $row->order_item_id !== null ? (int) $row->order_item_id : null,
            'sku'                     => $row->sku,
            'name'                    => $row->name,
            'qty'                     => $row->qty !== null ? (int) $row->qty : null,
            'price'                   => $row->price !== null ? (float) $row->price : null,
            'formattedPrice'          => core()->formatPrice((float) $row->price, $currency),
            'basePrice'               => $row->base_price !== null ? (float) $row->base_price : null,
            'basePriceInclTax'        => $row->base_price_incl_tax !== null ? (float) $row->base_price_incl_tax : null,
            'total'                   => $row->total !== null ? (float) $row->total : null,
            'formattedTotal'          => core()->formatPrice((float) $row->total, $currency),
            'baseTotal'               => $row->base_total !== null ? (float) $row->base_total : null,
            'baseTotalInclTax'        => $row->base_total_incl_tax !== null ? (float) $row->base_total_incl_tax : null,
            'taxAmount'               => $row->tax_amount !== null ? (float) $row->tax_amount : null,
            'formattedTaxAmount'      => core()->formatPrice((float) $row->tax_amount, $currency),
            'discountAmount'          => $row->discount_amount !== null ? (float) $row->discount_amount : null,
            'formattedDiscountAmount' => core()->formatPrice((float) $row->discount_amount, $currency),
            'productId'               => $row->product_id !== null ? (int) $row->product_id : null,
            'productType'             => $this->resolveProductType($row),
            'baseImageUrl'            => $this->resolveItemImage($row),
            'additional'              => is_array($row->additional) ? $row->additional : null,
        ];
    }

    /**
     * The line-item table's `product_type` column is the polymorphic morph
     * class (e.g. Webkul\Product\Models\Product), not the catalog type. Resolve
     * the real catalog type (simple / configurable / bundle / …) from the order
     * item, ignoring any class-name morph value.
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
