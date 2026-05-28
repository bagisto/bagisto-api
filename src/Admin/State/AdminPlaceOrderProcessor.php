<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminPlaceOrder;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

/**
 * POST /api/admin/orders/place/{cartId} + createAdminPlaceOrder GraphQL.
 *
 * Mirrors `Webkul\Admin\Http\Controllers\Sales\OrderController::store`:
 *   - Cart::setCart -> Cart::collectTotals -> validateOrder
 *   - payment.method must be in ['cashondelivery','moneytransfer']
 *   - OrderResource::jsonSerialize -> OrderRepository::create
 *   - Cart::removeCart
 *
 * Sequence checks (explicit, return 409 / 422 instead of monolith's 500/400):
 *   1. cart exists, draft, has items                                          409
 *   2. billing + shipping addresses saved                                     409
 *   3. shipping method saved (when stockable items)                           409
 *   4. payment method saved                                                   409
 *   5. payment method in ['cashondelivery','moneytransfer'] (monolith rule)   422
 */
class AdminPlaceOrderProcessor implements ProcessorInterface
{
    /** Monolith restriction — only these two payments may be admin-placed. */
    protected const SUPPORTED_PAYMENT_METHODS = ['cashondelivery', 'moneytransfer'];

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminPlaceOrder
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $cartId = $this->resolveCartId($uriVariables, $context, $data);

        if ($cartId === null) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.cart.not-found'));
        }

        $cart = AdminCartGuard::resolve($cartId);

        AdminCartSequenceGuard::requireItems($cart);
        AdminCartSequenceGuard::requireAddresses($cart, 'bagistoapi::app.admin.cart.place-order.addresses-required');
        AdminCartSequenceGuard::requireShippingMethod($cart, 'bagistoapi::app.admin.cart.place-order.shipping-required');
        AdminCartSequenceGuard::requirePaymentMethod($cart, 'bagistoapi::app.admin.cart.place-order.payment-required');

        Cart::setCart($cart);

        if (Cart::hasError()) {
            return $this->failed($cart, implode(': ', Cart::getErrors()) ?: __('bagistoapi::app.admin.cart.place-order.error'));
        }

        Cart::collectTotals();
        $cart = Cart::getCart() ?: $cart;

        $paymentMethod = $cart->payment?->method;
        if (! in_array($paymentMethod, self::SUPPORTED_PAYMENT_METHODS, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.place-order.payment-method-unsupported'), 422);
        }

        try {
            $orderData = (new OrderResource($cart))->jsonSerialize();

            /** @var OrderRepository $orderRepo */
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
        } catch (\Throwable $e) {
            Log::error('AdminPlaceOrder failed', [
                'cart_id' => $cart->id,
                'error'   => $e->getMessage(),
            ]);

            throw new InvalidInputException(
                $e->getMessage() ?: __('bagistoapi::app.admin.cart.place-order.failed'),
                500,
            );
        }
    }

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

        if ($raw === null || $raw === '') {
            return null;
        }

        $id = (int) basename((string) $raw);

        return $id > 0 ? $id : null;
    }

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

    protected function result(?int $orderId, ?string $incrementId, ?int $customerId, ?float $grandTotal, bool $success, string $message): AdminPlaceOrder
    {
        $r = new AdminPlaceOrder;
        $r->orderId = $orderId;
        $r->incrementId = $incrementId;
        $r->customerId = $customerId;
        $r->grandTotal = $grandTotal;
        $r->success = $success;
        $r->message = $message;

        return $r;
    }
}
