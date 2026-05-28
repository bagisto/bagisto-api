<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Product\Repositories\ProductRepository;

/**
 * POST /api/admin/catalog/products/mass-delete + createAdminCatalogProductMassDelete.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\ProductController::massDestroy:
 *   - For each ID, find → delete → fire catalog.product.delete.before/after.
 *   - Non-existent IDs are silently skipped.
 *   - If a single delete throws, surface HTTP 500 with the underlying message.
 *
 * No pre-validation batch guard (unlike Categories, which rejects channel-root IDs
 * up front). Products has no equivalent "undeletable" rule — plain best-effort.
 */
class AdminCatalogProductMassDeleteProcessor implements ProcessorInterface
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

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.indices-required'), 400);
        }

        $deleted = [];

        try {
            foreach ($indices as $index) {
                $id = (int) $index;
                $product = $this->productRepository->find($id);

                if (! isset($product)) {
                    continue; // silently skip non-existent
                }

                Event::dispatch('catalog.product.delete.before', $id);

                $this->productRepository->delete($id);

                Event::dispatch('catalog.product.delete.after', $id);

                $deleted[] = $id;
            }
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                $e->getMessage() ?: __('bagistoapi::app.admin.product.mass-delete-failed'),
                500,
            );
        }

        $result = new AdminCatalogProductMassDelete;
        $result->id = 1; // placeholder — serialiser needs a non-null id for IRI generation
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.product.mass-delete-success');

        return $result;
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

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminCatalogProductMassDeleteInput && ! empty($data->indices)) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices']
            ?? $context['args']['indices']
            ?? null;

        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input('indices');
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }
}
