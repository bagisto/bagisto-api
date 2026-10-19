<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

trait MapsOrderAddress
{
    /**
     * Map address.
     */
    protected function mapAddress($address): ?array
    {
        if (! $address) {
            return null;
        }

        return [
            'id' => (int) $address->id,
            'addressType' => $address->address_type,
            'firstName' => $address->first_name,
            'lastName' => $address->last_name,
            'companyName' => $address->company_name,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postcode' => $address->postcode,
            'email' => $address->email,
            'phone' => $address->phone,
        ];
    }
}
