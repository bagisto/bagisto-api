<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Models\AdminCartShippingRate;
use Webkul\Checkout\Facades\Cart;
use Webkul\Shipping\Facades\Shipping;

/**
 * GET /api/admin/carts/{cartId}/shipping-methods — list available shipping
 * rates for a draft cart.
 *
 * Mirrors the rates block inside CartController::storeAddress — once
 * addresses are saved, `Shipping::collectRates()` returns the carrier-grouped
 * rate list. We flatten it into per-rate rows and wrap in the admin
 * collection envelope.
 *
 * Sequence rule: both addresses must already be saved on the cart, otherwise
 * `AdminCartSequenceGuard::requireAddresses()` throws HTTP 409.
 */
class AdminCartShippingMethodsProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $cartId = AdminCartGuard::resolveId(
            array_merge($uriVariables, ['id' => $uriVariables['cartId'] ?? $uriVariables['id'] ?? request()->route('cartId')]),
            $context,
        );

        $cart = AdminCartGuard::resolve($cartId);

        AdminCartSequenceGuard::requireItems($cart);
        AdminCartSequenceGuard::requireAddresses($cart);

        Cart::setCart($cart);

        $rows = [];

        if ($cart->haveStockableItems()) {
            $result = Shipping::collectRates();

            if (is_array($result) && ! empty($result['shippingMethods'])) {
                foreach ($result['shippingMethods'] as $carrierCode => $group) {
                    $carrierTitle = $group['carrier_title'] ?? $group['title'] ?? $carrierCode;
                    $rates = $group['rates'] ?? [];

                    foreach ($rates as $rate) {
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

                        $rows[] = $row;
                    }
                }
            }
        }

        $total = count($rows);
        $perPage = max($total, 1);

        return new Paginator(new LengthAwarePaginator($rows, $total, $perPage, 1));
    }
}
