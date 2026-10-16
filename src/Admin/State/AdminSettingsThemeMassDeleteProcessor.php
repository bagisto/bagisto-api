<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsThemeMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Models\ThemeCustomization;

/**
 * POST /api/admin/settings/themes/mass-delete + createAdminSettingsThemeMassDelete.
 */
class AdminSettingsThemeMassDeleteProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.themes.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.theme.mass-delete-indices-required'), 422);
        }

        $deleted = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $row = ThemeCustomization::find($id);
            if (! $row) {
                continue;
            }

            $this->deleteRow($row, $id);

            $deleted[] = $id;
        }

        $result = new AdminSettingsThemeMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->message = __('bagistoapi::app.admin.settings.theme.mass-delete-success');

        return $result;
    }

    /**
     * Delete one theme row and its stored files, firing the delete events.
     */
    protected function deleteRow(ThemeCustomization $row, int $id): void
    {
        try {
            Event::dispatch('theme_customization.delete.before', $id);
            $row->delete();
            $this->deleteStoredFiles($id);
            Event::dispatch('theme_customization.delete.after', $id);
        } catch (\Throwable $exception) {
            report($exception);
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.theme.delete-failed'), 500);
        }
    }

    /**
     * Remove the theme directory, reporting any failure without aborting.
     */
    protected function deleteStoredFiles(int $id): void
    {
        try {
            Storage::deleteDirectory('theme/'.$id);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.theme.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.theme.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if (
            $data instanceof AdminSettingsThemeMassDeleteInput
            && ! empty($data->indices)
        ) {
            return $data->indices;
        }

        $fromArgs = $context['args']['input']['indices'] ?? $context['args']['indices'] ?? null;
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
