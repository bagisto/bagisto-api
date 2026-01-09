<?php

namespace Webkul\BagistoApi\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                   => (string) ($this->carrier.'_'.($this->method ?? rand(1000, 9999))),
            'code'                 => (string) $this->carrier,
            'label'                => (string) ($this->carrier_title ?? $this->carrier),
            'method'               => (string) $this->method,
            'method_title'         => (string) ($this->method_title ?? $this->carrier_title),
            'method_description'   => (string) ($this->method_description ?? ''),
            'price'                => (float) ($this->price ?? 0),
            'base_price'           => (float) ($this->base_price ?? 0),
            'description'          => (string) ($this->method_description ?? ''),
            'base_formatted_price' => $this->base_formatted_price ?? core()->currency($this->base_price ?? 0),
        ];
    }
}
