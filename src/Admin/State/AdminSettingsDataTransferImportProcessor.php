<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsDataTransferImportDeleteInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Models\Import;

/**
 * Handles DELETE on AdminSettingsDataTransferImport.
 *
 * Permission resolution mirrors AdminSettingsChannelProcessor — reads
 * role->permission_type / role->permissions directly. No bouncer() calls.
 */
class AdminSettingsDataTransferImportProcessor implements ProcessorInterface
{
    public function __construct(
        protected AdminSettingsDataTransferImportItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;

        if ($isGraphQL && $data instanceof AdminSettingsDataTransferImportDeleteInput) {
            $this->assertPermission($admin, 'settings.data_transfer.imports.delete');
            $id = (int) basename((string) ($data->id ?? $context['args']['input']['id'] ?? $context['args']['id'] ?? '0'));

            return $this->handleDelete($id, true);
        }

        if ($operation instanceof Delete) {
            $this->assertPermission($admin, 'settings.data_transfer.imports.delete');
            $id = (int) ($uriVariables['id'] ?? 0);

            return $this->handleDelete($id);
        }

        return null;
    }

    protected function handleDelete(int $id, bool $asResource = false): array|AdminSettingsDataTransferImport
    {
        $import = Import::find($id);
        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        $this->deleteFile($import->file_path);
        $this->deleteFile($import->error_file_path);

        try {
            $import->delete();
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.data-transfer.import.delete-failed'),
                500,
            );
        }

        if ($asResource) {
            $snapshot = $this->itemProvider->mapToDtoPublic($import);
            $snapshot->message = __('bagistoapi::app.admin.settings.data-transfer.import.deleted');

            return $snapshot;
        }

        return ['message' => __('bagistoapi::app.admin.settings.data-transfer.import.deleted')];
    }

    protected function deleteFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable) {
        }
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
