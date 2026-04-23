<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\CancelOrderInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CancelOrder;
use Webkul\Sales\Repositories\OrderRepository;

/**
 * CancelOrderProcessor — Handles the cancel order mutation
 *
 * Delegates to Bagisto's OrderRepository::cancel() which:
 * - Checks $order->canCancel() (items with qty_to_cancel > 0, status not closed/fraud)
 * - Dispatches sales.order.cancel.before / after events
 * - Returns inventory to stock
 * - Updates order status
 */
class CancelOrderProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly OrderRepository $orderRepository,
    ) {}

    /**
     * Process the cancel order operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CancelOrderInput) {
            $this->hydrateInputFromContext($data, $context);

            return $this->handleCancel($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Cancel the order for the authenticated customer
     */
    private function handleCancel(CancelOrderInput $input): CancelOrder
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (empty($input->orderId)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cancel-order.order-id-required'));
        }

        /** Find order scoped to the authenticated customer */
        $order = $customer->orders()->find($input->orderId);

        if (! $order) {
            throw new ResourceNotFoundException(
                __('bagistoapi::app.graphql.cancel-order.not-found', ['id' => $input->orderId])
            );
        }

        /** Delegate to Bagisto's core cancel logic */
        $result = $this->orderRepository->cancel($order);

        /** Refresh the order to get updated status */
        $order->refresh();

        if ($result) {
            return new CancelOrder(
                success: true,
                message: __('bagistoapi::app.graphql.cancel-order.cancel-success'),
                orderId: $order->id,
                status: $order->status,
            );
        }

        return new CancelOrder(
            success: false,
            message: __('bagistoapi::app.graphql.cancel-order.cancel-failed'),
            orderId: $order->id,
            status: $order->status,
        );
    }

    /**
     * GraphQL input can reach the processor in slightly different shapes depending on
     * whether the client sends variables or an inline literal. Normalize those shapes
     * into the DTO before validation.
     */
    private function hydrateInputFromContext(CancelOrderInput $data, array $context): void
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
