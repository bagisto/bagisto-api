<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Models\Import;
use Webkul\DataTransfer\Repositories\ImportRepository;

/**
 * Create + update for data-transfer imports (multipart upload).
 *
 * Mirrors Webkul\Admin\Http\Controllers\Settings\DataTransfer\ImportController
 * store() / update():
 *   - POST /api/admin/settings/data-transfer/imports        (file required)
 *   - PUT  /api/admin/settings/data-transfer/imports/{id}   (file optional; resets state)
 *
 * Binary upload is REST-only — the GraphQL `create` mutation is rejected (422).
 *
 * Permission gate: settings.data_transfer.imports.create / .edit.
 */
class AdminSettingsDataTransferImportCreateProcessor implements ProcessorInterface
{
    protected const SUPPORTED_FORMATS = ['csv', 'xls', 'xlsx', 'xml'];

    public function __construct(
        protected ImportRepository $importRepository,
        protected AdminSettingsDataTransferImportItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if ($operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.image.graphql-upload-unsupported'),
                422,
            );
        }

        if ($operation instanceof Put) {
            $this->assertPermission($admin, 'settings.data_transfer.imports.edit');

            $id = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

            return $this->handleUpdate($id);
        }

        if ($operation instanceof Post) {
            $this->assertPermission($admin, 'settings.data_transfer.imports.create');

            return $this->handleCreate();
        }

        return null;
    }

    protected function handleCreate(): mixed
    {
        $payload = $this->validatePayload(true);

        Event::dispatch('data_transfer.imports.create.before');

        $file = request()->file('file');
        $filePath = $this->storeFile($file);

        $import = $this->importRepository->create(array_merge($payload, [
            'file_path' => $filePath,
        ]));

        Event::dispatch('data_transfer.imports.create.after', $import);

        return $this->itemProvider->mapToDtoPublic(Import::find($import->id));
    }

    protected function handleUpdate(int $id): mixed
    {
        $import = Import::find($id);
        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        $payload = $this->validatePayload(false);

        Event::dispatch('data_transfer.imports.update.before');

        $data = array_merge($payload, [
            'state'                => 'pending',
            'processed_rows_count' => 0,
            'invalid_rows_count'   => 0,
            'errors_count'         => 0,
            'errors'               => null,
            'error_file_path'      => null,
            'started_at'           => null,
            'completed_at'         => null,
            'summary'              => null,
        ]);

        $this->deleteFile($import->error_file_path);

        $file = request()->file('file');
        if ($file instanceof UploadedFile && $file->isValid()) {
            $this->deleteFile($import->file_path);
            $data['file_path'] = $this->storeFile($file);
        }

        $updated = $this->importRepository->update($data, $import->id);

        Event::dispatch('data_transfer.imports.update.after', $updated);

        return $this->itemProvider->mapToDtoPublic(Import::find($updated->id));
    }

    /**
     * @return array<string,mixed>
     */
    protected function validatePayload(bool $fileRequired): array
    {
        $importers = array_keys(config('importers') ?? []);

        $input = request()->only([
            'type',
            'action',
            'process_in_queue',
            'validation_strategy',
            'allowed_errors',
            'field_separator',
            'images_directory_path',
        ]);

        $fileRule = $fileRequired ? 'required|file' : 'nullable|file';

        $validator = Validator::make(
            array_merge($input, ['file' => request()->file('file')]),
            [
                'type'                => 'required|in:'.implode(',', $importers),
                'action'              => 'required|in:append,delete',
                'validation_strategy' => 'required|in:stop-on-errors,skip-errors',
                'allowed_errors'      => 'required|integer|min:0',
                'field_separator'     => 'required',
                'file'                => $fileRule,
            ],
        );

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }

        $file = request()->file('file');
        if ($file instanceof UploadedFile && $file->isValid()) {
            $ext = strtolower((string) $file->getClientOriginalExtension());
            if (! in_array($ext, self::SUPPORTED_FORMATS, true)) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.data-transfer.import.file-invalid-type'),
                    422,
                );
            }
        }

        $input['process_in_queue'] = request()->boolean('process_in_queue');

        return $input;
    }

    protected function storeFile(UploadedFile $file): string
    {
        $safeFilename = uniqid().'_'.hash('sha256', $file->getClientOriginalName());
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();

        return $file->storeAs('imports', $safeFilename.'.'.$extension, 'private');
    }

    protected function deleteFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            Storage::disk('private')->delete($path);
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
