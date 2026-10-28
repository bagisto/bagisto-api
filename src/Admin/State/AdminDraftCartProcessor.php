<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Models\AdminDraftCart;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Models\Customer;

class AdminDraftCartProcessor implements ProcessorInterface
{
    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminDraftCart
    {
        AdminReadPermissions::authorize(null, null, 'sales.orders.create');

        $customerId = $this->resolveCustomerId($uriVariables, $context, $data);

        if ($customerId <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $customer = Customer::find($customerId);

        if (! $customer) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        try {
            $cart = Cart::createCart([
                'customer' => $customer,
                'is_active' => false,
            ]);
        } catch (\Throwable $exception) {
            Log::error('AdminDraftCart bootstrap failed', [
                'customer_id' => $customerId,
                'error' => $exception->getMessage(),
            ]);

            throw new InvalidInputException(
                $exception->getMessage() ?: __('bagistoapi::app.admin.cart.draft-failed'),
                422,
            );
        }

        if (
            ! $cart
            || ! $cart->id
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.draft-failed'), 422);
        }

        return $this->result(
            cartId: $cart->id,
            customerId: $customer->id,
            success: true,
            message: __('bagistoapi::app.admin.cart.draft-created'),
        );
    }

    /**
     * Resolve customer id.
     */
    protected function resolveCustomerId(array $uriVariables, array $context, mixed $data): int
    {
        $raw = $uriVariables['customerId']
            ?? $context['args']['input']['customerId']
            ?? $context['args']['customerId']
            ?? (is_object($data) ? ($data->customerId ?? null) : null)
            ?? request()->route('customerId')
            ?? request()->input('customerId')
            ?? null;

        return (int) ($raw ?? 0);
    }

    /**
     * Result.
     */
    protected function result(?int $cartId, ?int $customerId, bool $success, string $message): AdminDraftCart
    {
        $draftCart = new AdminDraftCart;
        $draftCart->cart_id = $cartId;
        $draftCart->customer_id = $customerId;
        $draftCart->success = $success;
        $draftCart->message = $message;

        return $draftCart;
    }
}
