<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\ReorderInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\ReorderOrder;
use Webkul\Checkout\Facades\Cart;

/**
 * Mirrors Shop OrderController::reorder; the Cart facade is initialised with the Sanctum customer explicitly.
 */
class ReorderProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process the reorder operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof ReorderInput) {
            $this->hydrateInputFromContext($data, $context);

            return $this->handleReorder($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Re-add a previous order's items to the customer's cart, skipping booking items as the storefront does.
     */
    private function handleReorder(ReorderInput $input): ReorderOrder
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (empty($input->orderId)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.reorder.order-id-required'));
        }

        $order = $customer->orders()
            ->with('items.product')
            ->find($input->orderId);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.reorder.not-found', ['id' => $input->orderId])
            );
        }

        if (! core()->getConfigData('sales.order_settings.reorder.shop')) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.reorder.disabled-in-settings'), 422);
        }

        if (! $order->canReorder()) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.reorder.cannot-reorder'), 422);
        }

        Cart::initCart($customer);

        if (! Cart::getCart()) {
            Cart::createCart(['customer' => $customer]);
        }

        $itemsAdded = 0;

        $skippedBooking = false;

        foreach ($order->items as $item) {
            if ($item->type === 'booking') {
                $skippedBooking = true;

                continue;
            }

            try {
                if (! $item->product || ! $item->product->status) {
                    continue;
                }

                $additional = $item->additional ?? [];

                if (empty($additional['product_id'])) {
                    $additional['product_id'] = $item->product_id;
                }

                if (empty($additional['quantity'])) {
                    $additional['quantity'] = $item->qty_ordered;
                }

                Cart::addProduct($item->product, $additional);

                $itemsAdded++;
            } catch (\Exception $e) {
            }
        }

        if ($itemsAdded > 0) {
            Cart::collectTotals();
        }

        $bookingNotice = $skippedBooking
            ? ' '.__('shop::app.customers.account.orders.view.reorder-booking-skipped')
            : '';

        if ($itemsAdded > 0) {
            return new ReorderOrder(
                success: true,
                message: __('bagistoapi::app.graphql.reorder.reorder-success', ['count' => $itemsAdded]).$bookingNotice,
                orderId: $order->id,
                itemsAddedCount: $itemsAdded,
            );
        }

        return new ReorderOrder(
            success: false,
            message: __('bagistoapi::app.graphql.reorder.no-items-added').$bookingNotice,
            orderId: $order->id,
            itemsAddedCount: 0,
        );
    }

    private function hydrateInputFromContext(ReorderInput $data, array $context): void
    {
        if (! empty($data->orderId)) {
            return;
        }

        $input = $context['args']['input'] ?? $context['args'] ?? null;

        $orderId = $this->extractOrderId($input);

        if ($orderId === null) {
            $request = Request::instance();

            if ($request) {
                $orderId = $this->extractOrderId($request->input('variables.input'))
                    ?? $this->extractOrderId($request->input('input'))
                    ?? $this->extractOrderId($request->input('extensions.variables.input'))
                    ?? $this->extractOrderId($request->all());
            }
        }

        if ($orderId !== null) {
            $data->orderId = $orderId;
        }
    }

    private function extractOrderId(mixed $input): ?int
    {
        if (is_array($input)) {
            $value = $input['orderId'] ?? $input['order_id'] ?? null;

            return is_numeric($value) ? (int) $value : null;
        }

        if (is_object($input)) {
            $value = $input->orderId ?? $input->order_id ?? null;

            return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }
}
