<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsThemeMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsThemeMassUpdateStatus;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

/**
 * POST /api/admin/settings/themes/mass-update-status +
 * createAdminSettingsThemeMassUpdateStatus.
 *
 * Delegates to ThemeCustomizationRepository::massUpdateStatus — same path as
 * the admin ThemeController::massUpdate.
 */
class AdminSettingsThemeMassUpdateStatusProcessor implements ProcessorInterface
{
    public function __construct(protected ThemeCustomizationRepository $repository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.themes.edit');

        $indices = $this->resolveIndices($data, $context);
        $value = $this->resolveValue($data, $context);

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.theme.mass-update-indices-required'), 422);
        }

        if (! in_array($value, [0, 1], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.theme.mass-update-value-invalid'), 422);
        }

        $ids = array_map('intval', $indices);

        $this->repository->massUpdateStatus(['status' => $value], $ids);

        $result = new AdminSettingsThemeMassUpdateStatus;
        $result->id = 1;
        $result->updated = $ids;
        $result->message = __('bagistoapi::app.admin.settings.theme.mass-update-success');

        return $result;
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

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.theme.no-permission'));
        }
    }

    protected function resolveIndices(mixed $data, array $context): array
    {
        if ($data instanceof AdminSettingsThemeMassUpdateStatusInput && ! empty($data->indices)) {
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

    protected function resolveValue(mixed $data, array $context): ?int
    {
        if ($data instanceof AdminSettingsThemeMassUpdateStatusInput && $data->value !== null) {
            return (int) $data->value;
        }

        $fromArgs = $context['args']['input']['value'] ?? $context['args']['value'] ?? null;
        if ($fromArgs !== null && $fromArgs !== '') {
            return (int) $fromArgs;
        }

        $fromBody = request()->input('value');
        if ($fromBody !== null && $fromBody !== '') {
            return (int) $fromBody;
        }

        return null;
    }
}
