<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\HasNextPagePaginatorInterface;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductInventory;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;

/**
 * Listing provider for `GET /api/admin/catalog/products/{productId}/inventories`
 * (and `adminCatalogProductInventories` GraphQL query).
 *
 * Also reused as the Put-operation `provider:` so API Platform can resolve
 * the parent product before handing off to the processor, and so the
 * processor can return a refreshed listing payload via `buildPayload()`.
 *
 * Returns a paginator carrying:
 *   - data rows (one per inventory_source the product has a row for)
 *   - meta.totalQty — sum of all rows' qty (used by the envelope normalizer).
 */
class AdminCatalogProductInventoryProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $productId = $this->resolveProductId($uriVariables, $context);

        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.inventory.not-found'));
        }

        return $this->buildPayload($product);
    }

    /**
     * Build the listing payload for a product, computing totalQty for the
     * envelope normalizer. Exposed as a public method so the processor can
     * reuse it after a write.
     */
    public function buildPayload(Product $product): AdminCatalogProductInventoryPaginator
    {
        $product->load('inventories.inventory_source');

        $rows = $product->inventories->map(function ($inv) {
            $dto = new AdminCatalogProductInventory;
            $dto->id = (int) $inv->id;
            $dto->sourceId = (int) ($inv->inventory_source_id ?? 0);
            $dto->sourceCode = $inv->inventory_source?->code;
            $dto->sourceName = $inv->inventory_source?->name;
            $dto->qty = (int) ($inv->qty ?? 0);

            return $dto;
        })->values()->all();

        $total = count($rows);
        $perPage = max($total, 1);
        $totalQty = array_sum(array_map(fn ($r) => $r->qty ?? 0, $rows));

        $lap = new LengthAwarePaginator($rows, $total, $perPage, 1);

        return new AdminCatalogProductInventoryPaginator($lap, ['totalQty' => (int) $totalQty]);
    }

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
    public function __construct(
        private readonly LengthAwarePaginator $paginator,
        private readonly array $extraMeta = [],
    ) {}

    public function count(): int
    {
        return $this->paginator->count();
    }

    public function getLastPage(): float
    {
        return (float) $this->paginator->lastPage();
    }

    public function getTotalItems(): float
    {
        return (float) $this->paginator->total();
    }

    public function getCurrentPage(): float
    {
        return (float) $this->paginator->currentPage();
    }

    public function getItemsPerPage(): float
    {
        return (float) $this->paginator->perPage();
    }

    public function getIterator(): \Traversable
    {
        return $this->paginator->getIterator();
    }

    public function hasNextPage(): bool
    {
        return $this->paginator->hasMorePages();
    }

    public function getExtraMeta(): array
    {
        return $this->extraMeta;
    }
}
