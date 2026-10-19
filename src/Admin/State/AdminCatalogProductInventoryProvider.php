<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\HasNextPagePaginatorInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductInventory;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;

class AdminCatalogProductInventoryProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize($operation, AdminCatalogProductInventory::class);

        $productId = $this->resolveProductId($uriVariables, $context);

        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.inventory.not-found'));
        }

        return $this->buildPayload($product);
    }

    /**
     * Build the listing payload for a product, computing totalQty for the
     * envelope normalizer. Exposed as a public method so the processor can.
     */
    public function buildPayload(Product $product): AdminCatalogProductInventoryPaginator
    {
        $product->load('inventories.inventory_source');

        $rows = $product->inventories->map(function ($inventory) {
            $dto = new AdminCatalogProductInventory;
            $dto->id = (int) $inventory->id;
            $dto->sourceId = (int) ($inventory->inventory_source_id ?? 0);
            $dto->sourceCode = $inventory->inventory_source?->code;
            $dto->sourceName = $inventory->inventory_source?->name;
            $dto->qty = (int) ($inventory->qty ?? 0);

            return $dto;
        })->values()->all();

        $total = count($rows);
        $perPage = max($total, 1);
        $totalQty = array_sum(array_map(fn ($row) => $row->qty ?? 0, $rows));

        $paginator = new LengthAwarePaginator($rows, $total, $perPage, 1);

        return new AdminCatalogProductInventoryPaginator($paginator, ['totalQty' => (int) $totalQty]);
    }

    /**
     * Resolve product id.
     */
    protected function resolveProductId(array $uriVariables, array $context): int
    {
        $raw = $uriVariables['productId']
            ?? $context['args']['input']['productId']
            ?? $context['args']['productId']
            ?? request()->route('productId')
            ?? request()->input('productId')
            ?? 0;

        return (int) $raw;
    }
}

/**
 * @internal Lightweight paginator wrapper that adds getExtraMeta() for the
 * AdminCollectionEnvelopeNormalizer (which merges extra keys into `meta`).
 */
final class AdminCatalogProductInventoryPaginator implements \IteratorAggregate, HasNextPagePaginatorInterface, PaginatorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly LengthAwarePaginator $paginator,
        private readonly array $extraMeta = [],
    ) {}

    /**
     * Count.
     */
    public function count(): int
    {
        return $this->paginator->count();
    }

    /**
     * Get last page.
     */
    public function getLastPage(): float
    {
        return (float) $this->paginator->lastPage();
    }

    /**
     * Get total items.
     */
    public function getTotalItems(): float
    {
        return (float) $this->paginator->total();
    }

    /**
     * Get current page.
     */
    public function getCurrentPage(): float
    {
        return (float) $this->paginator->currentPage();
    }

    /**
     * Get items per page.
     */
    public function getItemsPerPage(): float
    {
        return (float) $this->paginator->perPage();
    }

    /**
     * Get iterator.
     */
    public function getIterator(): \Traversable
    {
        return $this->paginator->getIterator();
    }

    /**
     * Whether next page.
     */
    public function hasNextPage(): bool
    {
        return $this->paginator->hasMorePages();
    }

    /**
     * Get extra meta.
     */
    public function getExtraMeta(): array
    {
        return $this->extraMeta;
    }
}
