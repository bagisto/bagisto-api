<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
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
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Models\Import;
use Webkul\DataTransfer\Repositories\ImportRepository;
use ZipArchive;

/**
 * Create + update for data-transfer imports (multipart upload).
 */
class AdminSettingsDataTransferImportCreateProcessor implements ProcessorInterface
{
    protected const SUPPORTED_FORMATS = ['csv', 'xls', 'xlsx', 'xml'];

    protected const MAX_IMAGES_ARCHIVE_KB = 102400;

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

        if ($operation instanceof Mutation) {
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

        $import = $this->importRepository->create(array_merge($payload, ['file_path' => '']));

        try {
            $import = $this->importRepository->update(
                $this->storeImportFiles((int) $import->id),
                $import->id,
            );
        } catch (\Throwable $e) {
            $this->purgeImportFiles((int) $import->id);

            $this->importRepository->delete($import->id);

            throw $e;
        }

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
            'state' => 'pending',
            'processed_rows_count' => 0,
            'invalid_rows_count' => 0,
            'errors_count' => 0,
            'errors' => null,
            'error_file_path' => null,
            'started_at' => null,
            'completed_at' => null,
            'summary' => null,
        ]);

        $this->deleteFile($import->error_file_path);

        $file = request()->file('file');
        if ($file instanceof UploadedFile && $file->isValid()) {
            $this->deleteFile($import->file_path);
        }

        $data = array_merge($data, $this->storeImportFiles((int) $import->id));

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
        ]);

        $input = array_merge($input, $this->imageSourceData(request()->input('type')));

        $fileRule = $fileRequired ? 'required|file' : 'nullable|file';

        $validator = Validator::make(
            array_merge($input, [
                'file' => request()->file('file'),
                'upload_images' => request()->file('upload_images'),
            ]),
            [
                'type' => 'required|in:'.implode(',', $importers),
                'action' => 'required|in:append,delete',
                'validation_strategy' => 'required|in:stop-on-errors,skip-errors',
                'allowed_errors' => 'required|integer|min:0',
                'field_separator' => 'required',
                'file' => $fileRule,
            ] + $this->imageRules(
                request()->input('type'),
                $fileRequired ? null : (int) (request()->route('id') ?? 0),
            ),
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

    protected function storeFile(UploadedFile $file, int $importId): string
    {
        $safeFilename = uniqid().'_'.hash('sha256', $file->getClientOriginalName());
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();

        return $file->storeAs('imports/'.$importId, $safeFilename.'.'.$extension, 'private');
    }

    /**
     * Validation rules for the image fields, or none for an import type that has no
     * images — a customer or tax-rate import would otherwise be asked for a directory.
     *
     * @return array<string, string>
     */
    protected function imageRules(?string $type, ?int $importId = null): array
    {
        if (! ImportHelper::typeSupportsImages($type)) {
            return [];
        }

        $archiveRequired = $importId && $this->countUploadedImages($importId)
            ? ''
            : 'required_if:image_source,'.ImportHelper::IMAGE_SOURCE_UPLOAD.'|';

        return [
            'image_source' => 'nullable|in:url,upload,directory',
            'upload_images' => $archiveRequired.'nullable|file|mimes:zip|max:'.self::MAX_IMAGES_ARCHIVE_KB,
            'images_directory_path' => 'required_if:image_source,'.ImportHelper::IMAGE_SOURCE_DIRECTORY.'|nullable|string',
        ];
    }

    /**
     * The image columns to save, or none at all for a type that has no images.
     *
     * @return array<string, mixed>
     */
    protected function imageSourceData(?string $type): array
    {
        if (! ImportHelper::typeSupportsImages($type)) {
            return [];
        }

        return [
            'image_source' => request()->input('image_source', ImportHelper::IMAGE_SOURCE_DIRECTORY),
            'images_directory_path' => request()->input('images_directory_path'),
        ];
    }

    /**
     * Store the files that arrived with the request and return the columns they set.
     *
     * @return array<string, mixed>
     */
    protected function storeImportFiles(int $importId): array
    {
        $data = [];

        $file = request()->file('file');

        if ($file instanceof UploadedFile && $file->isValid()) {
            $data['file_path'] = $this->storeFile($file, $importId);
        }

        $archive = request()->file('upload_images');

        if (
            request()->input('image_source') == ImportHelper::IMAGE_SOURCE_UPLOAD
            && $archive instanceof UploadedFile
            && $archive->isValid()
        ) {
            $this->purgeImportImages($importId);

            $this->storeImagesArchive($archive, $importId);

            $data['images_archive_name'] = $archive->getClientOriginalName();
        }

        return $data;
    }

    protected function storeImagesArchive(UploadedFile $file, int $importId): void
    {
        $disk = Storage::disk('private');

        $directory = 'imports/'.$importId.'/images';

        $archivePath = $file->storeAs($directory, $file->getClientOriginalName(), 'private');

        $zip = new ZipArchive;

        if ($zip->open($disk->path($archivePath)) === true) {
            $zip->extractTo($disk->path($directory));

            $zip->close();
        }

        $disk->delete($archivePath);
    }

    protected function countUploadedImages(int $importId): int
    {
        try {
            return count(Storage::disk('private')->files('imports/'.$importId.'/images'));
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function purgeImportImages(int $importId): void
    {
        try {
            Storage::disk('private')->deleteDirectory('imports/'.$importId.'/images');
        } catch (\Throwable) {
        }
    }

    protected function purgeImportFiles(int $importId): void
    {
        try {
            Storage::disk('private')->deleteDirectory('imports/'.$importId);
        } catch (\Throwable) {
        }
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
