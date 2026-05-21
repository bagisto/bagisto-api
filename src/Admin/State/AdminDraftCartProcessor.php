<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminDraftCart;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Models\Customer;

/**
 * Fresh Create-Order entry — POST /api/admin/customers/{customerId}/draft-carts
 * + GraphQL `createAdminDraftCart`.
 *
 * Bootstraps an empty admin draft cart (`is_active = false`) for the given
 * customer. Distinct from `AdminReorderProcessor` (Reorder entry); same end
 * state — a draft cart the admin can finalise via the cart-keyed write
 * endpoints (`POST /api/admin/carts/{id}/items`, etc).
 *
 * Mirrors `Webkul\Admin\Http\Controllers\Sales\CartController::store`.
 */
class AdminDraftCartProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminDraftCart
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

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
                'customer'  => $customer,
                'is_active' => false,
            ]);
        } catch (\Throwable $e) {
            Log::error('AdminDraftCart bootstrap failed', [
                'customer_id' => $customerId,
                'error'       => $e->getMessage(),
            ]);

            // Bubble up as a 422 — the request was well-formed but the
            // underlying create-cart facade refused. Never return 2xx success=false.
            throw new InvalidInputException(
                $e->getMessage() ?: __('bagistoapi::app.admin.cart.draft-failed'),
                422,
            );
        }

        if (! $cart || ! $cart->id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.cart.draft-failed'), 422);
        }

        return $this->result(
            cartId: $cart->id,
            customerId: $customer->id,
            success: true,
            message: __('bagistoapi::app.admin.cart.draft-created'),
        );
    }

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

    protected function result(?int $cartId, ?int $customerId, bool $success, string $message): AdminDraftCart
    {
        $r = new AdminDraftCart;
        $r->cartId = $cartId;
        $r->customerId = $customerId;
        $r->success = $success;
        $r->message = $message;

        return $r;
    }
}
