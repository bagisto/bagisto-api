<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsLocaleMassDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsLocaleMassDelete;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Models\Locale;

/**
 * POST /api/admin/settings/locales/mass-delete +
 * createAdminSettingsLocaleMassDelete.
 *
 * Deletes each provided ID, firing the before/after events. Non-existent IDs
 * are silently skipped. Per-id guards (last-locale, channel-default) skip the
 * id and append a reason in the `skipped` response key.
 */
class AdminSettingsLocaleMassDeleteProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.locales.delete');

        $indices = $this->resolveIndices($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.locale.mass-delete-indices-required'), 422);
        }

        $deleted = [];
        $skipped = [];

        foreach ($indices as $index) {
            $id = (int) $index;
            $row = Locale::find($id);

            if (! $row) {
                continue;
            }

            if (Locale::count() <= 1) {
                $skipped[] = ['id' => $id, 'reason' => __('bagistoapi::app.admin.settings.locale.cannot-delete-last')];

                continue;
            }

            if (DB::table('channels')->where('default_locale_id', $id)->exists()) {
                $skipped[] = ['id' => $id, 'reason' => __('bagistoapi::app.admin.settings.locale.cannot-delete-channel-default')];

                continue;
            }

            try {
                Event::dispatch('core.locale.delete.before', $id);

                $row->delete();

                Event::dispatch('core.locale.delete.after', $id);

                $deleted[] = $id;
            } catch (\Throwable $e) {
                report($e);
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.locale.delete-failed'),
                    500,
                );
            }
        }

        $result = new AdminSettingsLocaleMassDelete;
        $result->id = 1;
        $result->deleted = $deleted;
        $result->skipped = $skipped;
        $result->message = __('bagistoapi::app.admin.settings.locale.mass-delete-success');

        return $result;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.locale.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.locale.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminSettingsLocaleMassDeleteInput && ! empty($data->indices)) {
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
