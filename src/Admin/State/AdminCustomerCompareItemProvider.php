<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerCompareItem;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Customer;

class AdminCustomerCompareItemProvider implements ProviderInterface
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

        $rows = CompareItem::with('product.images')
            ->where('customer_id', $customerId)
            ->get()
            ->map(fn ($item) => $this->toDto($item))
            ->all();

        $total = count($rows);

        return new Paginator(new LengthAwarePaginator($rows, $total, max($total, 1), 1));
    }

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

        return $dto;
    }
}
