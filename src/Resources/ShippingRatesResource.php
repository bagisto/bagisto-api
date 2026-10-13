<?php

namespace Webkul\BagistoApi\Resources;

class ShippingRatesResource
{
    /**
     * Create a new resource instance.
     */
    public function __construct(protected array $data) {}

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray()
    {
        $shippingMethods = [];

        foreach ($this->data['shippingMethods'] ?? [] as $carrier => $group) {
            $shippingMethods[] = [
                'carrier' => $carrier,
                'carrier_title' => $group['carrier_title'] ?? $carrier,
                'rates' => $this->mapRates($carrier, $group),
            ];
        }

        return [
            'shippingMethods' => $shippingMethods,
        ];
    }

    /**
     * Map the rates of one carrier group.
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapRates(mixed $carrier, array $group): array
    {
        if (! isset($group['rates']) || ! is_array($group['rates'])) {
            return [];
        }

        $rates = [];

        foreach ($group['rates'] as $rate) {
            $rates[] = [
                'id' => (string) ($carrier.'_'.($rate->method ?? rand(1000, 9999))),
                'code' => (string) $carrier,
                'label' => (string) ($group['carrier_title'] ?? $carrier),
                'method' => (string) ($rate->method ?? $carrier),
                'method_title' => (string) ($rate->method_title ?? $group['carrier_title'] ?? $carrier),
                'method_description' => (string) ($rate->method_description ?? ''),
                'price' => (float) ($rate->price ?? 0),
                'base_price' => (float) ($rate->base_price ?? 0),
                'description' => (string) ($rate->method_description ?? ''),
                'base_formatted_price' => $rate->base_formatted_price ?? core()->currency($rate->base_price ?? 0),
            ];
        }

        return $rates;
    }

    /**
     * Resolve the resource to array.
     *
     * @return array
     */
    public function resolve()
    {
        return $this->toArray();
    }
}
