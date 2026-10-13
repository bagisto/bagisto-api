<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Models\AdminCartPaymentMethod;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\Checkout\Facades\Cart;
use Webkul\Payment\Facades\Payment;

/**
 * GET /api/admin/carts/{cartId}/payment-methods — list supported payment
 * methods for a draft cart.
 *
 * Mirrors `Payment::getSupportedPaymentMethods()`. The monolith returns these
 * after the shipping method is picked; we enforce the same ordering with
 * HTTP 409 if the cart still lacks a shipping selection.
 */
class AdminCartPaymentMethodsProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        AdminReadPermissions::authorize($operation, AdminCartPaymentMethod::class);

        $cartId = AdminCartGuard::resolveId(
            array_merge($uriVariables, ['id' => $uriVariables['cartId'] ?? $uriVariables['id'] ?? request()->route('cartId')]),
            $context,
        );

        $cart = AdminCartGuard::resolve($cartId);

        AdminCartSequenceGuard::requireItems($cart);
        AdminCartSequenceGuard::requireAddresses($cart);
        AdminCartSequenceGuard::requireShippingMethod($cart);

        Cart::setCart($cart);

        $result = Payment::getSupportedPaymentMethods();
        $methods = $result['payment_methods'] ?? [];

        $rows = [];

        foreach ($methods as $method) {
            $row = new AdminCartPaymentMethod;
            $row->method = $method['method'] ?? null;
            $row->methodTitle = $method['method_title'] ?? null;
            $row->description = $method['description'] ?? null;
            $row->sort = isset($method['sort']) ? (int) $method['sort'] : null;
            $row->image = $method['image'] ?? null;
            $rows[] = $row;
        }

        $total = count($rows);
        $perPage = max($total, 1);

        return new Paginator(new LengthAwarePaginator($rows, $total, $perPage, 1));
    }
}
