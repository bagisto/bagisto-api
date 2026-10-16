<?php

namespace Webkul\BagistoApi\State\Concerns;

use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerAddressRepository;
use Webkul\Sales\Repositories\OrderRepository;

/**
 * The personal data the storefront GDPR page shows: the customer, their saved addresses and their orders.
 */
trait BuildsGdprData
{
    /**
     * @return array{customer: array<string, mixed>, addresses: array<int, array<string, mixed>>, orders: array<int, array<string, mixed>>}
     */
    protected function gdprData(Customer $customer): array
    {
        $addresses = app(CustomerAddressRepository::class)
            ->where('address_type', 'customer')
            ->where('customer_id', $customer->id)
            ->get();

        $orders = app(OrderRepository::class)->findWhere(['customer_id' => $customer->id]);

        return [
            'customer' => [
                'firstName' => $customer->first_name,
                'lastName' => $customer->last_name,
                'email' => $customer->email,
                'gender' => $customer->gender,
                'dateOfBirth' => $customer->date_of_birth,
                'phone' => $customer->phone,
            ],
            'addresses' => $addresses->map(fn ($address) => [
                'firstName' => $address->first_name,
                'lastName' => $address->last_name,
                'companyName' => $address->company_name,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'postcode' => $address->postcode,
                'phone' => $address->phone,
                'vatId' => $address->vat_id,
            ])->values()->all(),
            'orders' => $orders->map(fn ($order) => [
                'id' => $order->id,
                'incrementId' => (string) $order->increment_id,
                'status' => $order->status,
                'shippingTitle' => $order->shipping_title,
                'grandTotal' => (float) $order->grand_total,
                'formattedGrandTotal' => core()->formatPrice($order->grand_total, $order->order_currency_code),
                'createdAt' => (string) $order->created_at,
            ])->values()->all(),
        ];
    }
}
