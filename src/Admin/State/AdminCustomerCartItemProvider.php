<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCartItem;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;

/**
 * Returns top-level items from the customer's OWN active cart
 * (`carts.is_active = 1`). Distinct from the admin draft cart.
 */
class AdminCustomerCartItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $customerId = (int) (
            $uriVariables['customerId']
            ?? $context['args']['customerId']
            ?? request()->route('customerId')
            ?? 0
        );

        if ($customerId <= 0 || ! Customer::whereKey($customerId)->exists()) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $cart = Cart::where('customer_id', $customerId)
            ->where('is_active', 1)
            ->first();

        $rows = [];

        if ($cart) {
            $rows = $cart->items()
                ->whereNull('parent_id')
                ->get()
                ->map(fn ($item) => $this->toDto($item))
                ->all();
        }

        $total = count($rows);

        return new Paginator(new LengthAwarePaginator($rows, $total, max($total, 1), 1));
    }

    protected function toDto($item): AdminCustomerCartItem
    {
        $dto = new AdminCustomerCartItem;

        $dto->id = $item->id;
        $dto->productId = $item->product_id;
        $dto->sku = $item->sku;
        $dto->type = $item->type;
        $dto->name = $item->name;
        $dto->quantity = (int) $item->quantity;
        $dto->price = (float) $item->price;
        $dto->formattedPrice = core()->formatPrice($item->price);
        $dto->total = (float) $item->total;
        $dto->formattedTotal = core()->formatPrice($item->total);
        $dto->additional = is_array($item->additional) ? $item->additional : null;

        return $dto;
    }
}
