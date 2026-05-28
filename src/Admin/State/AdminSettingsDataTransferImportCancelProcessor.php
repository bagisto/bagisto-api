<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportCancelInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImportCancel;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Models\Import;

/**
 * POST /api/admin/settings/data-transfer/imports/{id}/cancel + cancel GraphQL mutation.
 *
 * Sets state to `cancelled` when the import is currently `pending` or `processing`.
 * Any other state (validated / processed / completed / linking / indexing / failed /
 * cancelled) is treated as terminal-or-active-and-locked and refused with HTTP 422.
 *
 * Permission gate: settings.data_transfer.imports.edit (Sanctum-pattern role read).
 */
class AdminSettingsDataTransferImportCancelProcessor implements ProcessorInterface
{
    /** States that may be cancelled. */
    protected const CANCELLABLE_STATES = ['pending', 'processing'];

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'settings.data_transfer.imports.edit');

        $id = $this->resolveImportId($data, $uriVariables, $context);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        $import = Import::find($id);
        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        if (! in_array((string) $import->state, self::CANCELLABLE_STATES, true)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.data-transfer.import.cannot-cancel', [
                    'state' => (string) $import->state,
                ]),
                422,
            );
        }

        $import->state = 'cancelled';
        $import->save();

        $result = new AdminSettingsDataTransferImportCancel;
        $result->id = (int) $import->id;
        $result->state = $import->state;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.settings.data-transfer.import.cancelled');

        return $result;
    }

    protected function resolveImportId(mixed $data, array $uriVariables, array $context): int
    {
        if (isset($uriVariables['id'])) {
            return (int) $uriVariables['id'];
        }

        if ($data instanceof AdminSettingsDataTransferImportCancelInput && ! empty($data->importId)) {
            return (int) $data->importId;
        }

        $fromArgs = $context['args']['input']['importId']
            ?? $context['args']['importId']
            ?? null;
        if ($fromArgs !== null) {
            return (int) $fromArgs;
        }

        $fromRoute = request()->route('id');
        if ($fromRoute !== null) {
            return (int) $fromRoute;
        }

        $fromBody = request()->input('importId');
        if ($fromBody !== null) {
            return (int) $fromBody;
        }

        return 0;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
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
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        }
    }
}
