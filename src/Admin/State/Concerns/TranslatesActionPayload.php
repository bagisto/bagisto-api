<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

/**
 * Shared helpers for translating the API's clean array body shape into the
 * nested-map shape the monolith repositories consume.
 *
 * Invoice / Refund: `items: [{orderItemId, quantity}]` -> `[itemId => qty]`.
 * Shipment       : `items: [{orderItemId, inventorySourceId, quantity}]` ->
 *                  `[itemId => [sourceId => qty]]`.
 */
trait TranslatesActionPayload
{
    /**
     * Flat `[orderItemId => qty]` map (Invoice / Refund body shape).
     *
     * Accepts an already-flat assoc map as well — passes it through unchanged
     * so callers that came from the REST body (denormalized via DTO) and those
     * that came from `request()->input('items')` (raw assoc) both work.
     */
    protected function flatItemsMap(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $out = [];

        foreach ($items as $key => $entry) {
            if (is_array($entry) && (isset($entry['orderItemId']) || isset($entry['order_item_id']))) {
                $id = (int) ($entry['orderItemId'] ?? $entry['order_item_id']);
                $qty = (int) ($entry['quantity'] ?? $entry['qty'] ?? 0);
                if ($id > 0 && $qty > 0) {
                    $out[$id] = $qty;
                }

                continue;
            }

            if (is_int($key) || ctype_digit((string) $key)) {
                $qty = (int) (is_array($entry) ? ($entry['qty'] ?? 0) : $entry);
                if ((int) $key > 0 && $qty > 0) {
                    $out[(int) $key] = $qty;
                }
            }
        }

        return $out;
    }

    /**
     * Nested `[orderItemId => [sourceId => qty]]` map (Shipment body shape).
     */
    protected function nestedShipmentItemsMap(mixed $items, ?int $defaultSource = null): array
    {
        if (! is_array($items)) {
            return [];
        }

        $out = [];

        foreach ($items as $key => $entry) {
            if (is_array($entry) && (isset($entry['orderItemId']) || isset($entry['order_item_id']))) {
                $id = (int) ($entry['orderItemId'] ?? $entry['order_item_id']);
                $src = (int) ($entry['inventorySourceId'] ?? $entry['inventory_source_id'] ?? $defaultSource ?? 0);
                $qty = (int) ($entry['quantity'] ?? $entry['qty'] ?? 0);
                if ($id > 0 && $src > 0 && $qty > 0) {
                    $out[$id][$src] = ($out[$id][$src] ?? 0) + $qty;
                }

                continue;
            }

            if ((is_int($key) || ctype_digit((string) $key)) && is_array($entry)) {
                foreach ($entry as $src => $qty) {
                    $src = (int) $src;
                    $qty = (int) $qty;
                    if ((int) $key > 0 && $src > 0 && $qty > 0) {
                        $out[(int) $key][$src] = ($out[(int) $key][$src] ?? 0) + $qty;
                    }
                }
            }
        }

        return $out;
    }
}
