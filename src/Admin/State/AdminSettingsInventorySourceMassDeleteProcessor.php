<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsInventorySourceMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsInventorySourceMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Inventory\Models\InventorySource;
use Webkul\Inventory\Repositories\InventorySourceRepository;

/**
 * POST /api/admin/settings/inventory-sources/mass-delete +
 * createAdminSettingsInventorySourceMassDelete.
 *
 * Pre-validates the whole batch — refuses if deleting all of them would leave
 * zero sources, and refuses if any of the provided IDs is referenced by
 * product_inventories.
 */
class AdminSettingsInventorySourceMassDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        protected InventorySourceRepository $inventorySourceRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.inventory_sources.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.inventory-source.mass-delete-indices-required'), 422);
        }

        // Normalise to ints.
        $indices = array_values(array_unique(array_map('intval', $indices)));

        // Existing IDs only.
        $existingIds = InventorySource::whereIn('id', $indices)->pluck('id')->map(fn ($i) => (int) $i)->all();

        if (empty($existingIds)) {
            // Mirror happy path with empty deleted[] — every id was already gone.
            $result = new AdminSettingsInventorySourceMassDelete;
            $result->id = 1;
            $result->deleted = [];
            $result->message = __('bagistoapi::app.admin.settings.inventory-source.mass-delete-success');

            return $result;
        }

        // Guard: would not leave at least one source remaining.
        $totalSources = InventorySource::count();
        if ($totalSources - count($existingIds) < 1) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.inventory-source.last-delete-error'),
                400,
            );
        }

        // Guard: any of these in use by product_inventories.
        if (Schema::hasTable('product_inventories')
            && DB::table('product_inventories')->whereIn('inventory_source_id', $existingIds)->exists()) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.inventory-source.in-use'),
                400,
            );
        }

        $deleted = [];

        foreach ($existingIds as $id) {
            try {
                Event::dispatch('inventory.inventory_source.delete.before', $id);

                $this->inventorySourceRepository->delete($id);

                Event::dispatch('inventory.inventory_source.delete.after', $id);

                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.inventory-source.delete-failed'),
                    500,
                );
            }
        }

        $result = new AdminSettingsInventorySourceMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.settings.inventory-source.mass-delete-success');

        return $result;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.inventory-source.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.inventory-source.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminSettingsInventorySourceMassDeleteInput && ! empty($data->indices)) {
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
