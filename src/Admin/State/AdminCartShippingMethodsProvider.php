<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Models\AdminCartShippingRate;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Facades\Shipping;

class AdminCartShippingMethodsProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        AdminReadPermissions::authorize($operation, AdminCartShippingRate::class);

        $cartId = AdminCartGuard::resolveId(
            array_merge($uriVariables, ['id' => $uriVariables['cartId'] ?? $uriVariables['id'] ?? request()->route('cartId')]),
            $context,
        );

        $cart = AdminCartGuard::resolve($cartId);

        AdminCartSequenceGuard::requireItems($cart);
        AdminCartSequenceGuard::requireAddresses($cart);

        Cart::setCart($cart);

        $rows = $cart->haveStockableItems() ? $this->collectShippingRows() : [];

        $total = count($rows);
        $perPage = max($total, 1);

        return new Paginator(new LengthAwarePaginator($rows, $total, $perPage, 1));
    }

    /**
     * Flatten the collected carrier rates into shipping rate rows.
     */
    private function collectShippingRows(): array
    {
        $result = Shipping::collectRates();

        if (
            ! is_array($result)
            || empty($result['shippingMethods'])
        ) {
            return [];
        }

        $rows = [];

        foreach ($result['shippingMethods'] as $carrierCode => $group) {
            $carrierTitle = $group['carrier_title'] ?? $group['title'] ?? $carrierCode;

            foreach ($group['rates'] ?? [] as $rate) {
                $rows[] = $this->mapShippingRate($carrierCode, $carrierTitle, $rate);
            }
        }

        return $rows;
    }

    /**
     * Map a carrier rate to a shipping rate row.
     */
    private function mapShippingRate(string|int $carrierCode, mixed $carrierTitle, mixed $rate): AdminCartShippingRate
    {
        $method = is_object($rate) ? ($rate->method ?? null) : ($rate['method'] ?? null);
        $methodTitle = is_object($rate) ? ($rate->method_title ?? null) : ($rate['method_title'] ?? null);
        $price = is_object($rate) ? ($rate->price ?? 0) : ($rate['price'] ?? 0);
        $baseTotal = is_object($rate) ? ($rate->base_total ?? $price) : ($rate['base_total'] ?? $price);

        $row = new AdminCartShippingRate;
        $row->method = $method;
        $row->carrierCode = $carrierCode;
        $row->carrierTitle = $carrierTitle;
        $row->methodTitle = $methodTitle;
        $row->price = $price !== null ? (float) $price : null;
        $row->formattedPrice = core()->formatPrice($price ?? 0);
        $row->baseTotal = $baseTotal !== null ? (float) $baseTotal : null;
        $row->formattedBaseTotal = core()->formatPrice($baseTotal ?? 0);

        return $row;
    }
}
