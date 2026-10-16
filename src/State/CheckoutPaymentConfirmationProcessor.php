<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Dto\CheckoutPaymentConfirmationInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\CheckoutPaymentConfirmation;
use Webkul\BagistoApi\State\Concerns\BuildsPaypalSmartButtonOrder;
use Webkul\BagistoApi\State\Concerns\ResolvesCheckoutCart;
use Webkul\BagistoApi\State\Concerns\ValidatesCheckoutCart;
use Webkul\Checkout\Facades\Cart;
use Webkul\Paypal\Payment\SmartButton;
use Webkul\Razorpay\Payment\RazorpayPayment;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;

/**
 * Places the order after payment, mirroring RazorpayController::paymentSuccess, PayPal's
 * StandardController::success and SmartButtonController::captureOrder, which read the session cart.
 */
class CheckoutPaymentConfirmationProcessor implements ProcessorInterface
{
    use BuildsPaypalSmartButtonOrder;
    use ResolvesCheckoutCart;
    use ValidatesCheckoutCart;

    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
        protected OrderTransactionRepository $orderTransactionRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CheckoutPaymentConfirmation
    {
        $cart = $this->resolveCheckoutCart();

        $this->assertCartHasNoErrors();

        $this->validateOrderCreation($cart);

        $order = match ($cart->payment->method) {
            'razorpay' => $this->confirmRazorpay($cart, $this->input($data, $context)),
            'paypal_standard' => $this->createOrderFromCart($cart),
            'paypal_smart_button' => $this->confirmPaypalSmartButton($cart, $this->input($data, $context)['paypal_order_id']),
            default => throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-no-confirmation'), 422),
        };

        Cart::deActivateCart();

        Event::dispatch('order.created.after', $order);

        $order = $this->orderRepository->find($order->id);

        $result = new CheckoutPaymentConfirmation;
        $result->order_id = $order->id;
        $result->order_increment_id = (string) $order->increment_id;
        $result->status = $order->status;
        $result->success = true;
        $result->message = __('bagistoapi::app.graphql.checkout.payment-confirmed');

        return $result;
    }

    /**
     * @param  array{razorpay_order_id: ?string, razorpay_payment_id: ?string, razorpay_signature: ?string}  $input
     */
    private function confirmRazorpay(object $cart, array $input): Order
    {
        $razorpay = app(RazorpayPayment::class);

        if (
            ! $razorpay->hasValidCredentials()
            || ! $razorpay->verifySignature($input['razorpay_order_id'], $input['razorpay_payment_id'], $input['razorpay_signature'])
            || ! $this->razorpayOrderMatchesCart($razorpay, $input['razorpay_order_id'], $cart)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-verified'), 422);
        }

        $order = $this->createOrderFromCart($cart);

        $order->payment?->update([
            'additional' => [
                'status' => Invoice::STATUS_PAID,
                'razorpay_order_id' => $input['razorpay_order_id'],
                'razorpay_payment_id' => $input['razorpay_payment_id'],
            ],
        ]);

        $this->orderRepository->update(['status' => Order::STATUS_PROCESSING], $order->id);

        $invoiceItems = $order->items
            ->filter(fn ($item) => $item->qty_to_invoice > 0)
            ->mapWithKeys(fn ($item) => [$item->id => $item->qty_to_invoice])
            ->all();

        if ($invoiceItems) {
            $invoice = $this->invoiceRepository->create(['order_id' => $order->id, 'invoice' => ['items' => $invoiceItems]]);

            $this->orderTransactionRepository->create([
                'transaction_id' => $input['razorpay_payment_id'],
                'status' => 'captured',
                'type' => $order->payment->method,
                'payment_method' => $order->payment->method,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'amount' => $order->base_grand_total,
                'data' => json_encode($input),
            ]);
        }

        return $order;
    }

    /**
     * Captures the approved PayPal order, then places and invoices the order as core's captureOrder does.
     */
    private function confirmPaypalSmartButton(object $cart, ?string $paypalOrderId): Order
    {
        if (! $paypalOrderId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.paypal-order-id-required'), 422);
        }

        $smartButton = app(SmartButton::class);

        $this->assertSmartButtonConfigured($smartButton);

        try {
            $paypalOrder = $this->smartButtonArray($smartButton->getOrder($paypalOrderId));

            if (! $this->smartButtonOrderMatchesCart($smartButton, $paypalOrder, $cart)) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-verified'), 422);
            }

            if (($paypalOrder['status'] ?? null) !== 'COMPLETED') {
                $capture = $this->smartButtonArray($smartButton->captureOrder($paypalOrderId));

                if (($capture['status'] ?? null) !== 'COMPLETED') {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-verified'), 422);
                }
            }
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(__('bagistoapi::app.graphql.checkout.payment-not-verified'), 422);
        }

        $order = $this->createOrderFromCart($cart);

        $this->orderRepository->update(['status' => Order::STATUS_PROCESSING], $order->id);

        if ($order->canInvoice()) {
            $invoiceItems = $order->items->mapWithKeys(fn ($item) => [$item->id => $item->qty_to_invoice])->all();

            $invoice = $this->invoiceRepository->create(['order_id' => $order->id, 'invoice' => ['items' => $invoiceItems]]);

            $transactionDetails = $this->smartButtonArray($smartButton->getOrder($paypalOrderId));

            $this->orderTransactionRepository->create([
                'transaction_id' => $transactionDetails['id'] ?? $paypalOrderId,
                'status' => $transactionDetails['status'] ?? null,
                'type' => $transactionDetails['intent'] ?? null,
                'amount' => $transactionDetails['purchase_units'][0]['amount']['value'] ?? null,
                'payment_method' => $order->payment->method,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'data' => json_encode([
                    'purchase_units' => $transactionDetails['purchase_units'] ?? [],
                    'payer' => $transactionDetails['payer'] ?? [],
                ]),
            ]);
        }

        return $order;
    }

    /**
     * Core trusts the session cart; the API also proves the paid Razorpay order was created for this cart.
     */
    private function razorpayOrderMatchesCart(RazorpayPayment $razorpay, ?string $razorpayOrderId, object $cart): bool
    {
        try {
            $razorpayOrder = $razorpay->getApi()->order->fetch($razorpayOrderId);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return (string) ($razorpayOrder['notes']['cart_id'] ?? '') === (string) $cart->id
            && (int) $razorpayOrder['amount'] === (int) ($cart->base_grand_total * 100);
    }

    /**
     * @return array{razorpay_order_id: ?string, razorpay_payment_id: ?string, razorpay_signature: ?string, paypal_order_id: ?string}
     */
    private function input(mixed $data, array $context): array
    {
        $args = $context['args']['input'] ?? [];

        $value = function (string $snake, string $camel) use ($data, $args): ?string {
            $value = $data instanceof CheckoutPaymentConfirmationInput ? $data->{$snake} : null;
            $value ??= $args[$camel] ?? $args[$snake] ?? request()->input($snake) ?? request()->input($camel);

            return $this->scalar($value);
        };

        return [
            'razorpay_order_id' => $value('razorpay_order_id', 'razorpayOrderId'),
            'razorpay_payment_id' => $value('razorpay_payment_id', 'razorpayPaymentId'),
            'razorpay_signature' => $value('razorpay_signature', 'razorpaySignature'),
            'paypal_order_id' => $value('paypal_order_id', 'paypalOrderId') ?? $this->scalar(request()->input('orderID') ?? request()->input('orderId')),
        ];
    }

    private function scalar(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }
}
