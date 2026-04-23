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
 * ReorderProcessor — Handles the reorder mutation
 *
 * Re-adds items from a previous order to the customer's cart.
 * Mirrors Shop\Http\Controllers\Customer\Account\OrderController::reorder().
 *
 * Key difference from Shop controller: In API context, the Cart facade
 * must be explicitly initialized with the Sanctum-authenticated customer
 * via Cart::initCart($customer), since the default guard is session-based.
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
     * Reorder items from a previous order into the cart
     *
     * Business logic follows Bagisto's native reorder flow:
     * 1. Authenticate customer via Sanctum guard
     * 2. Find the order scoped to customer
     * 3. Initialize Cart facade for the API customer (critical for Sanctum context)
     * 4. Iterate order items and add each to cart via Cart::addProduct()
     * 5. Collect cart totals to finalize pricing
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

        /** Find order scoped to the authenticated customer */
        $order = $customer->orders()
            ->with('items.product')
            ->find($input->orderId);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.reorder.not-found', ['id' => $input->orderId])
            );
        }

        /**
         * Initialize Cart for the Sanctum-authenticated customer.
         *
         * Cart::initCart() defaults to auth()->guard()->user() (session guard),
         * which is null in API context. We must explicitly pass the customer
         * so the Cart facade finds/creates the correct cart.
         *
         * If no active cart exists, create one with the customer data so that
         * Cart::addProduct() -> createCart() picks up the right customer.
         * Without this, createCart() falls back to auth()->guard()->user()
         * and creates a guest cart.
         */
        Cart::initCart($customer);

        if (! Cart::getCart()) {
            Cart::createCart(['customer' => $customer]);
        }

        /** Re-add each order item to cart, skipping failures (same as Shop controller) */
        $itemsAdded = 0;

        foreach ($order->items as $item) {
            try {
                /** Skip items whose product no longer exists or is inactive */
                if (! $item->product || ! $item->product->status) {
                    continue;
                }

                /**
                 * Build the additional data array for Cart::addProduct().
                 * Use the original order item's `additional` if present,
                 * otherwise construct minimal required data.
                 */
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
                /** Silently skip items that can't be added (same as Shop controller) */
            }
        }

        /** Finalize cart state — recalculate totals, taxes, discounts */
        if ($itemsAdded > 0) {
            Cart::collectTotals();
        }

        if ($itemsAdded > 0) {
            return new ReorderOrder(
                success: true,
                message: __('bagistoapi::app.graphql.reorder.reorder-success', ['count' => $itemsAdded]),
                orderId: $order->id,
                itemsAddedCount: $itemsAdded,
            );
        }

        return new ReorderOrder(
            success: false,
            message: __('bagistoapi::app.graphql.reorder.no-items-added'),
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
