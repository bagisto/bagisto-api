<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminCategoryMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCategoryMassUpdateStatus;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Category\Models\Category;

/**
 * POST /api/admin/catalog/categories/mass-update-status + createAdminCategoryMassUpdateStatus.
 *
 * Mirrors Webkul\Admin\Http\Controllers\Catalog\CategoryController::massUpdate:
 *   - For each ID, fire catalog.categories.mass-update.before/after
 *   - Set category.status = value, save
 */
class AdminCategoryMassUpdateStatusProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'catalog.categories.edit');

        $indices = $this->resolveArray($data, $context, 'indices');
        $value = $this->resolveScalar($data, $context, 'value');

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.mass-delete-indices-required'), 422);
        }

        if ($value === null || ! in_array((int) $value, [0, 1], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.mass-update-status-value-required'), 422);
        }

        $updated = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $category = Category::find($id);

            if (! $category) {
                continue;
            }

            Event::dispatch('catalog.categories.mass-update.before', $id);

            $category->status = (int) $value;
            $category->save();

            Event::dispatch('catalog.categories.mass-update.after', $category);

            $updated[] = $id;
        }

        $result = new AdminCategoryMassUpdateStatus;
        $result->id = 1; // placeholder for IRI
        $result->updated = $updated;
        $result->message = __('bagistoapi::app.admin.category.mass-update-status-success');

        return $result;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.category.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.category.no-permission'));
        }
    }

    protected function resolveArray(mixed $data, array $context, string $key): array
    {
        if ($data instanceof AdminCategoryMassUpdateStatusInput && is_array($data->{$key} ?? null)) {
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
        if ($data instanceof AdminCategoryMassUpdateStatusInput && $data->{$key} !== null) {
            return $data->{$key};
        }

        return $context['args']['input'][$key]
            ?? $context['args'][$key]
            ?? request()->input($key);
    }
}
