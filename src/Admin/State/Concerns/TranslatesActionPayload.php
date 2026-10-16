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
            if (
                is_array($entry)
                && (
                    isset($entry['orderItemId'])
                    || isset($entry['order_item_id'])
                )
            ) {
                $id = (int) ($entry['orderItemId'] ?? $entry['order_item_id']);
                $qty = (int) ($entry['quantity'] ?? $entry['qty'] ?? 0);
                if (
                    $id > 0
                    && $qty > 0
                ) {
                    $out[$id] = $qty;
                }

                continue;
            }

            if (
                is_int($key)
                || ctype_digit((string) $key)
            ) {
                $qty = (int) (is_array($entry) ? ($entry['qty'] ?? 0) : $entry);
                if (
                    (int) $key > 0
                    && $qty > 0
                ) {
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
            if (
                is_array($entry)
                && (
                    isset($entry['orderItemId'])
                    || isset($entry['order_item_id'])
                )
            ) {
                $this->addShipmentEntry($out, $entry, $defaultSource);

                continue;
            }

            if (
                (
                    is_int($key)
                    || ctype_digit((string) $key)
                )
                && is_array($entry)
            ) {
                $this->addShipmentSourceQuantities($out, (int) $key, $entry);
            }
        }

        return $out;
    }

    /**
     * Add one `orderItemId` entry to the nested shipment map.
     *
     * @param  array<int, array<int, int>>  $out
     */
    protected function addShipmentEntry(array &$out, array $entry, ?int $defaultSource): void
    {
        $id = (int) ($entry['orderItemId'] ?? $entry['order_item_id']);
        $source = (int) ($entry['inventorySourceId'] ?? $entry['inventory_source_id'] ?? $defaultSource ?? 0);
        $qty = (int) ($entry['quantity'] ?? $entry['qty'] ?? 0);

        if (
            $id > 0
            && $source > 0
            && $qty > 0
        ) {
            $out[$id][$source] = ($out[$id][$source] ?? 0) + $qty;
        }
    }

    /**
     * Add a `[sourceId => qty]` map for one order item to the nested shipment map.
     *
     * @param  array<int, array<int, int>>  $out
     */
    protected function addShipmentSourceQuantities(array &$out, int $orderItemId, array $entry): void
    {
        foreach ($entry as $source => $qty) {
            $source = (int) $source;
            $qty = (int) $qty;

            if (
                $orderItemId > 0
                && $source > 0
                && $qty > 0
            ) {
                $out[$orderItemId][$source] = ($out[$orderItemId][$source] ?? 0) + $qty;
            }
        }
    }
}
