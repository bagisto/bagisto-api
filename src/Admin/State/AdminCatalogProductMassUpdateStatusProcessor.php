<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductMassUpdateStatus;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Product\Repositories\ProductRepository;

/**
 * POST /api/admin/catalog/products/mass-update-status + createAdminCatalogProductMassUpdateStatus.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\ProductController::massUpdate:
 *   - For each ID, fire catalog.product.update.before, call repo update with
 *     ['status' => $value] (attributes whitelist ['status']), fire after.
 *   - Best-effort: no try/catch in core; missing IDs are not pre-validated.
 *     We only validate the request payload shape itself.
 */
class AdminCatalogProductMassUpdateStatusProcessor implements ProcessorInterface
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

        $this->assertPermission($admin, 'catalog.products.edit');

        $indices = $this->resolveArray($data, $context, 'indices');
        $value = $this->resolveScalar($data, $context, 'value');

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.indices-required'), 400);
        }

        if ($value === null || ! in_array((int) $value, [0, 1], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.value-invalid'), 400);
        }

        $value = (int) $value;
        $updated = [];

        foreach ($indices as $index) {
            $id = (int) $index;

            Event::dispatch('catalog.product.update.before', $id);

            $product = $this->productRepository->update(
                ['status' => $value],
                $id,
                ['status'],
            );

            Event::dispatch('catalog.product.update.after', $product);

            $updated[] = $id;
        }

        $result = new AdminCatalogProductMassUpdateStatus;
        $result->id = 1;
        $result->updated = $updated;
        $result->message = __('bagistoapi::app.admin.product.mass-update-status-success');

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

    protected function resolveArray(mixed $data, array $context, string $key): array
    {
        if ($data instanceof AdminCatalogProductMassUpdateStatusInput && is_array($data->{$key} ?? null)) {
            return $data->{$key};
        }

        $fromArgs = $context['args']['input'][$key] ?? $context['args'][$key] ?? null;
        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input($key);
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }

    protected function resolveScalar(mixed $data, array $context, string $key): mixed
    {
        if ($data instanceof AdminCatalogProductMassUpdateStatusInput && $data->{$key} !== null) {
            return $data->{$key};
        }

        return $context['args']['input'][$key]
            ?? $context['args'][$key]
            ?? request()->input($key);
    }
}
