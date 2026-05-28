<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductCustomerGroupPrice;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductCustomerGroupPrice;

/**
 * Read provider for the admin customer-group-prices sub-resource.
 *
 *  - GetCollection (REST + GraphQL QueryCollection): lists rows for the
 *    given product, joined with customer_groups for display name.
 *  - Put / Delete: returns a placeholder AdminCatalogProductCustomerGroupPrice
 *    so API Platform routes through to the processor (which reads
 *    uriVariables directly and looks the row up itself).
 */
class AdminCatalogProductCustomerGroupPriceProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if ($operation instanceof GetCollection || $operation instanceof QueryCollection) {
            return $this->listForProduct($uriVariables, $context);
        }

        if ($operation instanceof Put || $operation instanceof Delete) {
            $placeholder = new AdminCatalogProductCustomerGroupPrice;
            $placeholder->id = (int) ($uriVariables['id'] ?? 0);
            $placeholder->productId = (int) ($uriVariables['productId'] ?? 0);

            return $placeholder;
        }

        return null;
    }

    protected function listForProduct(array $uriVariables, array $context): Paginator
    {
        $productId = $this->resolveProductId($uriVariables, $context);

        if ($productId <= 0 || ! Product::where('id', $productId)->exists()) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $rows = ProductCustomerGroupPrice::query()
            ->where('product_customer_group_prices.product_id', $productId)
            ->leftJoin('customer_groups', 'customer_groups.id', '=', 'product_customer_group_prices.customer_group_id')
            ->orderBy('product_customer_group_prices.qty', 'asc')
            ->get([
                'product_customer_group_prices.id',
                'product_customer_group_prices.product_id',
                'product_customer_group_prices.qty',
                'product_customer_group_prices.value_type',
                'product_customer_group_prices.value',
                'product_customer_group_prices.customer_group_id',
                'customer_groups.name as customer_group_name',
            ])
            ->map(function ($row): AdminCatalogProductCustomerGroupPrice {
                $dto = new AdminCatalogProductCustomerGroupPrice;
                $dto->id = (int) $row->id;
                $dto->productId = (int) $row->product_id;
                $dto->qty = (int) $row->qty;
                $dto->valueType = (string) $row->value_type;
                $dto->value = $row->value !== null ? (float) $row->value : null;
                $dto->customerGroupId = $row->customer_group_id !== null ? (int) $row->customer_group_id : null;
                $dto->customerGroupName = $row->customer_group_name !== null ? (string) $row->customer_group_name : null;

                return $dto;
            })
            ->values();

        $total = $rows->count();
        $perPage = $total > 0 ? $total : 1;

        return new Paginator(
            new LengthAwarePaginator($rows, $total, $perPage, 1, ['path' => request()->url()])
        );
    }

    protected function resolveProductId(array $uriVariables, array $context): int
    {
        $raw = $uriVariables['productId']
            ?? $context['args']['productId']
            ?? request()->route('productId')
            ?? request()->input('productId')
            ?? 0;

        return (int) $raw;
    }
}
