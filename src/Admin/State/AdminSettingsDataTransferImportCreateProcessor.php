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
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Helpers\Import as ImportHelper;
use Webkul\DataTransfer\Repositories\ImportRepository;
use ZipArchive;

class AdminSettingsDataTransferImportCreateProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    /**
     * Source file formats an import accepts.
     */
    protected const SUPPORTED_FORMATS = ['csv', 'xls', 'xlsx', 'xml'];

    /**
     * Largest images archive accepted, in KB (100 MB).
     */
    protected const MAX_IMAGES_ARCHIVE_KB = 102400;

    /**
     * The most files an images archive may carry, as core allows.
     */
    protected const MAX_IMAGES_ARCHIVE_ENTRIES = 5000;

    /**
     * The largest the files in an images archive may come to once unpacked, in bytes, as core allows.
     */
    protected const MAX_IMAGES_ARCHIVE_UNPACKED_SIZE = 1073741824;

    /**
     * The extensions unpacked from an images archive, since the importer reads nothing else from it.
     */
    protected const IMAGE_ARCHIVE_EXTENSIONS = ['bmp', 'gif', 'jpeg', 'jpg', 'png', 'webp'];

    /**
     * Create a new instance.
     */
    public function __construct(
        protected ImportRepository $importRepository,
        protected AdminSettingsDataTransferImportItemProvider $itemProvider,
    ) {}

    /**
     * Route the import request to create or update; GraphQL cannot carry the files and is refused.
     */
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

    /**
     * Create an import and store the files that came with it, rolling the record back if storing fails.
     */
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
        } catch (\Throwable $exception) {
            $this->purgeImportFiles((int) $import->id);

            $this->importRepository->delete($import->id);

            throw $exception;
        }

        Event::dispatch('data_transfer.imports.create.after', $import);

        return $this->itemProvider->mapToDtoPublic($this->importRepository->find($import->id));
    }

    /**
     * Update an import, reset it to pending and replace any files that came with the request.
     */
    protected function handleUpdate(int $id): mixed
    {
        $import = $this->importRepository->find($id);

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

        if (
            $file instanceof UploadedFile
            && $file->isValid()
        ) {
            $this->deleteFile($import->file_path);
        }

        $data = array_merge($data, $this->storeImportFiles((int) $import->id));

        $updated = $this->importRepository->update($data, $import->id);

        Event::dispatch('data_transfer.imports.update.after', $updated);

        return $this->itemProvider->mapToDtoPublic($this->importRepository->find($updated->id));
    }

    /**
     * Validate the request against core's import rules and return the columns to save.
     *
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

        if (
            $file instanceof UploadedFile
            && $file->isValid()
        ) {
            $extension = strtolower((string) $file->getClientOriginalExtension());

            if (! in_array($extension, self::SUPPORTED_FORMATS, true)) {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.settings.data-transfer.import.file-invalid-type'),
                    422,
                );
            }
        }

        $archive = request()->file('upload_images');

        if (
            ImportHelper::typeSupportsImages(request()->input('type'))
            && request()->input('image_source') == ImportHelper::IMAGE_SOURCE_UPLOAD
            && $archive instanceof UploadedFile
            && $archive->isValid()
        ) {
            $this->assertArchiveWithinLimits($archive);
        }

        $input['process_in_queue'] = request()->boolean('process_in_queue');

        return $input;
    }

    /**
     * Store the source file on the private disk under a generated name.
     */
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

        if (
            $file instanceof UploadedFile
            && $file->isValid()
        ) {
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

    /**
     * Unpack the images of an uploaded archive into the import's own folder, as core does.
     */
    protected function storeImagesArchive(UploadedFile $file, int $importId): void
    {
        $disk = Storage::disk('private');

        $directory = 'imports/'.$importId.'/images';

        $archivePath = $file->storeAs($directory, Str::random(40).'.zip', 'private');

        $zip = new ZipArchive;

        if ($zip->open($disk->path($archivePath)) === true) {
            if ($this->archiveUnpacksWithinLimits($zip)) {
                $this->extractImages($zip, $disk, $directory);
            }

            $zip->close();
        }

        $disk->delete($archivePath);
    }

    /**
     * Refuse an images archive that holds more files, or unpacks to more bytes, than core allows.
     */
    protected function assertArchiveWithinLimits(UploadedFile $archive): void
    {
        $zip = new ZipArchive;

        if ($zip->open((string) $archive->getRealPath()) !== true) {
            return;
        }

        $withinLimits = $this->archiveUnpacksWithinLimits($zip);

        $zip->close();

        if (! $withinLimits) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.settings.data-transfer.import.images-archive-too-large', [
                    'entries' => self::MAX_IMAGES_ARCHIVE_ENTRIES,
                    'size' => intdiv(self::MAX_IMAGES_ARCHIVE_UNPACKED_SIZE, 1048576),
                ]),
                422,
            );
        }
    }

    /**
     * Whether an images archive stays within the file count and unpacked size an import allows.
     */
    protected function archiveUnpacksWithinLimits(ZipArchive $zip): bool
    {
        if ($zip->numFiles > self::MAX_IMAGES_ARCHIVE_ENTRIES) {
            return false;
        }

        $unpackedSize = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $unpackedSize += $zip->statIndex($index)['size'] ?? 0;

            if ($unpackedSize > self::MAX_IMAGES_ARCHIVE_UNPACKED_SIZE) {
                return false;
            }
        }

        return true;
    }

    /**
     * Unpack the entries of an images archive that are actually images, one by one.
     */
    protected function extractImages(ZipArchive $zip, mixed $disk, string $directory): void
    {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (! $this->isExtractableImage($name)) {
                continue;
            }

            $stream = $zip->getStream($name);

            if (! is_resource($stream)) {
                continue;
            }

            $disk->writeStream($directory.'/'.$name, $stream);

            fclose($stream);
        }
    }

    /**
     * Whether an archive entry is an image the importer may read, named somewhere it may write.
     */
    protected function isExtractableImage(string|false $name): bool
    {
        if (
            ! $name
            || str_ends_with($name, '/')
            || str_contains($name, '..')
            || str_contains($name, '\\')
            || str_starts_with($name, '/')
        ) {
            return false;
        }

        return in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::IMAGE_ARCHIVE_EXTENSIONS, true);
    }

    /**
     * Count the images an import currently has unpacked.
     */
    protected function countUploadedImages(int $importId): int
    {
        try {
            return count(Storage::disk('private')->files('imports/'.$importId.'/images'));
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Drop an import's unpacked images.
     */
    protected function purgeImportImages(int $importId): void
    {
        try {
            Storage::disk('private')->deleteDirectory('imports/'.$importId.'/images');
        } catch (\Throwable) {
        }
    }

    /**
     * Remove everything an import owns on the private disk.
     */
    protected function purgeImportFiles(int $importId): void
    {
        try {
            Storage::disk('private')->deleteDirectory('imports/'.$importId);
        } catch (\Throwable) {
        }
    }

    /**
     * Delete a file from the private disk, ignoring a missing one.
     */
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

    /**
     * Refuse an admin whose role lacks the given permission.
     */
    protected function assertPermission(object $admin, string $permission): void
    {
        if (! $this->adminHasPermission($admin, $permission)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.settings.data-transfer.import.no-permission'));
        }
    }
}
