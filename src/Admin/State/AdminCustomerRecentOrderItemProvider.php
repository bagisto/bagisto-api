<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerRecentOrderItem;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\OrderItem;

/**
 * Up to 5 most-recent distinct order items for a customer — sidebar panel.
 *
 * Mirrors the monolith query: distinct product_id from order_items joined to
 * orders for the customer, parent_id null, ordered by orders.created_at desc.
 */
class AdminCustomerRecentOrderItemProvider implements ProviderInterface
{
    protected const MAX_ITEMS = 5;

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

        $items = OrderItem::query()
            ->select('order_items.*')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNull('order_items.parent_id')
            ->where('orders.customer_id', $customerId)
            ->orderByDesc('orders.created_at')
            ->limit(self::MAX_ITEMS * 4)
            ->with('product.images')
            ->get();

        $seen = [];
        $rows = [];
        foreach ($items as $item) {
            if ($item->product_id === null || isset($seen[$item->product_id])) {
                continue;
            }
            $seen[$item->product_id] = true;
            $rows[] = $this->toDto($item);
            if (count($rows) >= self::MAX_ITEMS) {
                break;
            }
        }

        $total = count($rows);

        return new Paginator(new LengthAwarePaginator($rows, $total, max($total, 1), 1));
    }

    protected function toDto($item): AdminCustomerRecentOrderItem
    {
        $product = $item->product;
        $image = $product?->images?->first();

        $dto = new AdminCustomerRecentOrderItem;
        $dto->id = $item->id;
        $dto->productId = $item->product_id;
        $dto->sku = $item->sku;
        $dto->type = $item->type;
        $dto->name = $item->name;
        $dto->price = (float) $item->price;
        $dto->formattedPrice = core()->formatPrice($item->price);
        $dto->productImage = $image ? Storage::url($image->path) : null;
        $dto->additional = is_array($item->additional) ? $item->additional : null;

        return $dto;
    }
}
