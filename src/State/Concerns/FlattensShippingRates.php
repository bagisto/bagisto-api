<?php

namespace Webkul\BagistoApi\State\Concerns;

/**
 * Flattens the carrier-grouped result of Shipping::collectRates() into one row per rate.
 */
trait FlattensShippingRates
{
    /**
     * @param  array<string, array{carrier_title?: string, rates?: array<int, object>}>  $shippingMethods
     * @return array<int, array<string, string|float>>
     */
    protected function flattenShippingRates(array $shippingMethods): array
    {
        $rows = [];

        foreach ($shippingMethods as $carrier => $group) {
            if (! isset($group['rates']) || ! is_array($group['rates'])) {
                continue;
            }

            $carrierTitle = (string) ($group['carrier_title'] ?? $carrier);

            foreach ($group['rates'] as $rate) {
                $rows[] = [
                    'id' => (string) ($carrier.'_'.($rate->method ?? rand(1000, 9999))),
                    'code' => (string) $carrier,
                    'label' => $carrierTitle,
                    'method' => (string) ($rate->method ?? $carrier),
                    'price' => (float) ($rate->price ?? 0),
                    'formatted_price' => (string) core()->formatPrice($rate->price ?? 0),
                    'description' => (string) ($rate->method_description ?? ''),
                    'method_title' => (string) ($rate->method_title ?? $carrierTitle),
                    'method_description' => (string) ($rate->method_description ?? ''),
                    'base_price' => (float) ($rate->base_price ?? 0),
                    'base_formatted_price' => (string) ($rate->base_formatted_price ?? core()->currency($rate->base_price ?? 0)),
                    'carrier' => (string) $carrier,
                    'carrier_title' => $carrierTitle,
                ];
            }
        }

        return $rows;
    }
}
