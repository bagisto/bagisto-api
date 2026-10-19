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

class ReorderProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process the reorder operation.
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
     * Business logic follows Bagisto's native reorder flow:
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

        Cart::initCart($customer);

        if (! Cart::getCart()) {
            Cart::createCart(['customer' => $customer]);
        }

        $itemsAdded = 0;

        foreach ($order->items as $item) {
            try {
                if (
                    ! $item->product
                    || ! $item->product->status
                ) {
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
            } catch (\Exception $exception) {
            }
        }

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

    /**
     * Hydrate input from context.
     */
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

    /**
     * Extract order id.
     */
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
