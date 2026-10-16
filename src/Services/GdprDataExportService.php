<?php

namespace Webkul\BagistoApi\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Repositories\CustomerAddressRepository;
use Webkul\Sales\Repositories\OrderRepository;

class GdprDataExportService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected OrderRepository $orders,
        protected CustomerAddressRepository $addresses
    ) {}

    /**
     * Gather the authenticated customer's data in the shape core's GDPR PDF view reads.
     *
     * @return array{customerInformation: object, address: array, order: array}
     */
    public function forCurrentCustomer(): array
    {
        $customer = $this->currentCustomer();

        $orders = $this->orders->findWhere(['customer_id' => $customer->id])
            ->map(fn ($order) => collect($order->toArray())->only([
                'id', 'status', 'name', 'sku', 'qty_ordered', 'type', 'shipping_title', 'grand_total',
            ])->all())
            ->all();

        $addresses = $this->customerAddresses($customer->id)
            ->map(fn ($address) => collect($address->toArray())->only([
                'city', 'company_name', 'country', 'first_name', 'last_name', 'postcode',
                'address1', 'address2', 'phone', 'vat_id',
            ])->all())
            ->all();

        return [
            'customerInformation' => $customer,
            'address' => $addresses,
            'order' => $orders,
        ];
    }

    /**
     * Gather the authenticated customer's data for the JSON export, with the address and order
     * columns the tables hold today and every ordered item.
     *
     * @return array{customer: object, addresses: array, orders: array}
     */
    public function exportForCurrentCustomer(): array
    {
        $customer = $this->currentCustomer();

        $orders = $this->orders->with('items')
            ->findWhere(['customer_id' => $customer->id])
            ->map(fn ($order) => array_merge(collect($order->toArray())->only([
                'id', 'increment_id', 'status', 'channel_name', 'customer_email', 'customer_first_name',
                'customer_last_name', 'shipping_title', 'shipping_method', 'coupon_code', 'total_qty_ordered',
                'order_currency_code', 'grand_total', 'sub_total', 'tax_amount', 'shipping_amount',
                'discount_amount', 'created_at',
            ])->all(), [
                'items' => $order->items
                    ->map(fn ($item) => collect($item->toArray())->only([
                        'id', 'sku', 'name', 'type', 'qty_ordered', 'qty_shipped', 'qty_invoiced',
                        'qty_canceled', 'qty_refunded', 'price', 'total',
                    ])->all())
                    ->values()
                    ->all(),
            ]))
            ->all();

        $addresses = $this->customerAddresses($customer->id)
            ->map(fn ($address) => collect($address->toArray())->only([
                'first_name', 'last_name', 'gender', 'company_name', 'address', 'city', 'state',
                'country', 'postcode', 'email', 'phone', 'vat_id', 'default_address',
            ])->all())
            ->all();

        return [
            'customer' => $customer,
            'addresses' => $addresses,
            'orders' => $orders,
        ];
    }

    /**
     * Render the authenticated customer's GDPR export as a PDF.
     */
    public function pdfForCurrentCustomer(): string
    {
        $param = $this->forCurrentCustomer();

        return Pdf::loadView('shop::customers.account.gdpr.pdf', compact('param'))
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * Resolve the authenticated customer, refusing when GDPR is disabled.
     */
    private function currentCustomer(): object
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        if (! core()->getConfigData('general.gdpr.settings.enabled')) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.gdpr.disabled'));
        }

        return $customer;
    }

    /**
     * Get the customer's saved addresses.
     */
    private function customerAddresses(int $customerId): Collection
    {
        return $this->addresses
            ->where('address_type', 'customer')
            ->where('customer_id', $customerId)
            ->get();
    }
}
