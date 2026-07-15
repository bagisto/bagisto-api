<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

/**
 * Maps a Bagisto order address model to the plain associative-array shape used
 * across the admin Sales detail responses (Invoice / Shipment / Refund).
 *
 * Plain array (not a typed DTO) per the project-wide admin-detail nested-object
 * convention — avoids API Platform's IRI-serialization trap and renders the
 * same in REST and GraphQL.
 */
trait MapsOrderAddress
{
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
