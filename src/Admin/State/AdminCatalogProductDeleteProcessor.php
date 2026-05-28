<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Admin Catalog Product delete (Phase 5.10).
 *
 * No in-order guard (matches monolith ProductController::destroy). Just:
 *   - permission: catalog.products.delete
 *   - fire catalog.product.delete.before
 *   - repo->delete($id)
 *   - fire catalog.product.delete.after
 *   - return 204 (REST) / { success, message } (GraphQL)
 *
 * For configurable products, cascading deletion of variants is handled by the
 * core Bagisto product type instance.
 */
class AdminCatalogProductDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected ProductRepository $productRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.products.delete');

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        $id = (int) ($uriVariables['id'] ?? 0);
        if (! $id && $isGraphQL) {
            $rawId = $context['args']['input']['id'] ?? $context['args']['id'] ?? null;
            if ($rawId) {
                $id = (int) basename((string) $rawId);
            }
        }

        if (! $id) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.update.id-required'), 422);
        }

        if (! Product::where('id', $id)->exists()) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        try {
            Event::dispatch('catalog.product.delete.before', $id);

            $this->productRepository->delete($id);

            Event::dispatch('catalog.product.delete.after', $id);
        } catch (\Throwable $e) {
            report($e);

            throw new InvalidInputException(
                $e->getMessage() ?: __('bagistoapi::app.admin.product.delete.delete-failed'),
                500,
            );
        }

        if ($isGraphQL) {
            $result = new AdminCatalogProduct;
            $result->id = $id;

            return $result;
        }

        // REST Delete: status 204 already set by the operation — return null
        // so API Platform emits an empty body.
        return null;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.no-permission'));
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

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.no-permission'));
        }
    }
}
