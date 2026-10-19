<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductInventoryUpdateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductInventory;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductInventoryRepository;

class AdminCatalogProductInventoryProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected ProductInventoryRepository $productInventoryRepository,
        protected AdminCatalogProductInventoryProvider $listingProvider,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.products.edit');

        $productId = $this->resolveProductId($data, $uriVariables, $context);
        $inventories = $this->resolveInventories($data, $context);

        if ($productId <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.inventory.not-found'));
        }

        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.inventory.not-found'));
        }

        $this->validateInventories($inventories);

        Event::dispatch('catalog.product.update.before', $productId);

        $this->productInventoryRepository->saveInventories(
            ['inventories' => $inventories],
            $product,
        );

        Event::dispatch('catalog.product.update.after', $product);

        $fresh = $product->fresh() ?? $product;

        if ($operation instanceof \ApiPlatform\Metadata\GraphQl\Operation) {
            return $this->graphQlResult($fresh, $inventories);
        }

        return $this->listingProvider->buildPayload($fresh);
    }

    /**
     * Graph ql result.
     */
    protected function graphQlResult(Product $product, array $inventories): AdminCatalogProductInventory
    {
        $product->load('inventories.inventory_source');

        $sourceId = (int) array_key_first($inventories);

        $row = $product->inventories
            ->first(fn ($inventory) => (int) $inventory->inventory_source_id === $sourceId)
            ?? $product->inventories->first();

        $dto = new AdminCatalogProductInventory;

        if ($row) {
            $dto->id = (int) $row->id;
            $dto->sourceId = (int) ($row->inventory_source_id ?? 0);
            $dto->sourceCode = $row->inventory_source?->code;
            $dto->sourceName = $row->inventory_source?->name;
            $dto->qty = (int) ($row->qty ?? 0);
        }

        return $dto;
    }

    /**
     * Validate the inventories map: non-empty, integer source ids that
     * actually exist, non-negative integer quantities.
     */
    protected function validateInventories(array $inventories): void
    {
        if (empty($inventories)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.inventory.inventories-required'),
                422,
            );
        }

        $sourceIds = [];

        foreach ($inventories as $sourceId => $qty) {
            if (
                ! is_numeric($sourceId)
                || (int) $sourceId <= 0
            ) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.inventory.inventories-invalid'),
                    422,
                );
            }

            if (
                ! is_numeric($qty)
                || (int) $qty < 0
                || (string) (int) $qty !== (string) $qty
            ) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.inventory.qty-invalid'),
                    422,
                );
            }

            $sourceIds[] = (int) $sourceId;
        }

        $sourceIds = array_unique($sourceIds);

        $existing = DB::table('inventory_sources')
            ->whereIn('id', $sourceIds)
            ->pluck('id')
            ->map(fn ($index) => (int) $index)
            ->all();

        $missing = array_diff($sourceIds, $existing);

        if (! empty($missing)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.inventory.source-not-found', [
                    'id' => (int) reset($missing),
                ]),
                422,
            );
        }
    }

    /**
     * Assert permission.
     */
    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.inventory.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (
            ! in_array($permission, $perms, true)
            && ! in_array('*', $perms, true)
        ) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.inventory.no-permission'));
        }
    }

    /**
     * Resolve product id.
     */
    protected function resolveProductId(mixed $data, array $uriVariables, array $context): int
    {
        if (isset($uriVariables['productId'])) {
            return (int) $uriVariables['productId'];
        }

        if (
            $data instanceof AdminCatalogProductInventoryUpdateInput
            && ! empty($data->productId)
        ) {
            return (int) $data->productId;
        }

        $fromArgs = $context['args']['input']['productId']
            ?? $context['args']['productId']
            ?? null;
        if ($fromArgs !== null) {
            return (int) $fromArgs;
        }

        $fromRoute = request()->route('productId');
        if ($fromRoute !== null) {
            return (int) $fromRoute;
        }

        $fromBody = request()->input('productId');
        if ($fromBody !== null) {
            return (int) $fromBody;
        }

        return 0;
    }

    /**
     * Resolve the inventories map from either the request body (REST) or
     * the GraphQL args. Numeric-string keys are preserved.
     */
    protected function resolveInventories(mixed $data, array $context): array
    {
        $fromArgs = $context['args']['input']['inventories']
            ?? $context['args']['inventories']
            ?? null;
        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        if (
            $data instanceof AdminCatalogProductInventoryUpdateInput
            && is_array($data->inventories)
        ) {
            return $data->inventories;
        }

        $body = request()->input('inventories');

        return is_array($body) ? $body : [];
    }
}
