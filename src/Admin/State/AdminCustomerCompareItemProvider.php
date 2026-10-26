<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCompareItem;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\Concerns\MapsCustomerItemProduct;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Customer;

class AdminCustomerCompareItemProvider implements ProviderInterface
{
    use MapsCustomerItemProduct;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        AdminReadPermissions::authorize($operation, AdminCustomerCompareItem::class);

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

        $rows = CompareItem::with(['product.images', 'product.inventories'])
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn ($item) => $this->toDto($item))
            ->all();

        $total = count($rows);

        return new Paginator(new LengthAwarePaginator($rows, $total, max($total, 1), 1));
    }

    /**
     * To dto.
     */
    protected function toDto($compare): AdminCustomerCompareItem
    {
        $product = $compare->product;
        $image = $product?->images?->first();

        $dto = new AdminCustomerCompareItem;
        $dto->id = $compare->id;
        $dto->productId = $compare->product_id;
        $dto->sku = $product?->sku;
        $dto->name = $product?->name;
        $dto->price = $product ? (float) $product->price : null;
        $dto->formattedPrice = $product ? core()->formatPrice($product->price) : null;
        $dto->productImage = $image ? Storage::url($image->path) : null;

        $this->fillProductSummary($dto, $product);

        return $dto;
    }
}
