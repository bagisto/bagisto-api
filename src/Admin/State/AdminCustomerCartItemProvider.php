<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCartItem;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\Concerns\MapsCustomerItemProduct;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Checkout\Models\Cart;
use Webkul\Customer\Models\Customer;

class AdminCustomerCartItemProvider implements ProviderInterface
{
    use MapsCustomerItemProduct;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        AdminReadPermissions::authorize($operation, AdminCustomerCartItem::class);

        $customerId = (int) (
            $uriVariables['customerId']
            ?? $context['args']['customerId']
            ?? request()->route('customerId')
            ?? 0
        );

        if (
            $customerId <= 0
            || ! Customer::whereKey($customerId)->exists()
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $cart = Cart::where('customer_id', $customerId)
            ->where('is_active', 1)
            ->first();

        $rows = [];

        if ($cart) {
            $rows = $cart->items()
                ->with(['product.images', 'product.inventories'])
                ->whereNull('parent_id')
                ->get()
                ->map(fn ($item) => $this->toDto($item))
                ->all();
        }

        $total = count($rows);

        return new Paginator(new LengthAwarePaginator($rows, $total, max($total, 1), 1));
    }

    /**
     * To dto.
     */
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
        $dto->cartId = (int) $item->cart_id;
        $dto->priceInclTax = (float) $item->price_incl_tax;
        $dto->formattedPriceInclTax = core()->formatPrice($item->price_incl_tax);
        $dto->totalInclTax = (float) $item->total_incl_tax;
        $dto->formattedTotalInclTax = core()->formatPrice($item->total_incl_tax);
        $dto->options = array_values($item->additional['attributes'] ?? []);

        $image = $item->product?->images?->first();
        $dto->productImage = $image ? Storage::url($image->path) : null;

        $this->fillProductSummary($dto, $item->product);

        return $dto;
    }
}
