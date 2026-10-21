<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminPlaceOrder;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class AdminPlaceOrderProcessor implements ProcessorInterface
{
    /**
     * Monolith restriction — only these two payments may be admin-placed.
     */
    protected const SUPPORTED_PAYMENT_METHODS = ['cashondelivery', 'moneytransfer'];

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminPlaceOrder
    {
        AdminReadPermissions::authorize(null, null, 'sales.orders.create');

        $cartId = $this->resolveCartId($uriVariables, $context, $data);

        if ($cartId === null) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.not-found'));
        }

        $cart = AdminCartGuard::resolve($cartId);

        AdminCartSequenceGuard::requireItems($cart);

        Cart::setCart($cart);
        Cart::collectTotals();
        $cart = Cart::getCart() ?: $cart;

        if (! Cart::haveMinimumOrderAmount()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.cart.place-order.minimum-order-error', [
                    'amount' => core()->formatPrice(core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0),
                ]),
                422,
            );
        }

        if (Cart::hasError()) {
            return $this->failed($cart, implode(': ', Cart::getErrors()) ?: __('bagistoapi::app.admin.cart.place-order.error'));
        }

        AdminCartSequenceGuard::requireAddresses($cart, 'bagistoapi::app.admin.cart.place-order.addresses-required');
        AdminCartSequenceGuard::requireShippingMethod($cart, 'bagistoapi::app.admin.cart.place-order.shipping-required');
        AdminCartSequenceGuard::requirePaymentMethod($cart, 'bagistoapi::app.admin.cart.place-order.payment-required');

        $paymentMethod = $cart->payment?->method;
        if (! in_array($paymentMethod, self::SUPPORTED_PAYMENT_METHODS, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.place-order.payment-method-unsupported'), 422);
        }

        try {
            $orderData = (new OrderResource($cart))->jsonSerialize();

            $orderRepo = app(OrderRepository::class);

            $order = $orderRepo->create($orderData);

            Cart::removeCart($cart);

            return $this->result(
                orderId: $order->id,
                incrementId: $order->increment_id,
                customerId: $order->customer_id,
                grandTotal: $order->grand_total !== null ? (float) $order->grand_total : null,
                success: true,
                message: __('bagistoapi::app.admin.cart.place-order.success'),
            );
        } catch (\Throwable $exception) {
            Log::error('AdminPlaceOrder failed', [
                'cart_id' => $cart->id,
                'error' => $exception->getMessage(),
            ]);

            throw new InvalidInputException(
                $exception->getMessage() ?: __('bagistoapi::app.admin.cart.place-order.failed'),
                500,
            );
        }
    }

    /**
     * Resolve cart id.
     */
    protected function resolveCartId(array $uriVariables, array $context, mixed $data): ?int
    {
        $raw = $uriVariables['cartId']
            ?? $uriVariables['id']
            ?? $context['args']['input']['cartId']
            ?? $context['args']['cartId']
            ?? (is_object($data) ? ($data->cartId ?? null) : null)
            ?? request()->route('cartId')
            ?? request()->input('cartId')
            ?? null;

        if (
            $raw === null
            || $raw === ''
        ) {
            return null;
        }

        $id = (int) basename((string) $raw);

        return $id > 0 ? $id : null;
    }

    /**
     * Failed.
     */
    protected function failed(CartModel $cart, string $message): AdminPlaceOrder
    {
        return $this->result(
            orderId: null,
            incrementId: null,
            customerId: $cart->customer_id,
            grandTotal: null,
            success: false,
            message: $message,
        );
    }

    /**
     * Result.
     */
    protected function result(?int $orderId, ?string $incrementId, ?int $customerId, ?float $grandTotal, bool $success, string $message): AdminPlaceOrder
    {
        $placeOrder = new AdminPlaceOrder;
        $placeOrder->orderId = $orderId;
        $placeOrder->incrementId = $incrementId;
        $placeOrder->customerId = $customerId;
        $placeOrder->grandTotal = $grandTotal;
        $placeOrder->success = $success;
        $placeOrder->message = $message;

        return $placeOrder;
    }
}
