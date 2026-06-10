<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\DataTransfer\Models\Import;

/**
 * Binary download endpoints (REST-only — binary streams aren't transportable
 * over JSON GraphQL):
 *   GET /settings/data-transfer/imports/{id}/download
 *   GET /settings/data-transfer/imports/{id}/download-error-report
 *   GET /settings/data-transfer/imports/sample/{type}/{format}
 *
 * The concrete download is selected by the operation shortName.
 * Returns an Illuminate\Http\Response so API Platform's SerializeProcessor
 * short-circuits (mirrors the CSV-export / invoice-print pattern).
 */
class AdminSettingsDataTransferImportDownloadProvider implements ProviderInterface
{
    protected const SUPPORTED_FORMATS = ['csv', 'xls', 'xlsx', 'xml'];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $template = method_exists($operation, 'getUriTemplate')
            ? (string) $operation->getUriTemplate()
            : '';
        $path = (string) request()->path();

        if (str_contains($template, '/sample/') || str_contains($path, '/imports/sample/')) {
            return $this->downloadSample($uriVariables);
        }

        if (str_contains($template, 'download-error-report') || str_contains($path, 'download-error-report')) {
            return $this->downloadErrorReport($uriVariables);
        }

        return $this->downloadFile($uriVariables);
    }

    protected function downloadFile(array $uriVariables): Response
    {
        $import = $this->resolveImport($uriVariables);

        if (! $import->file_path || ! Storage::disk('private')->exists($import->file_path)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.file-not-found'));
        }

        return $this->stream(Storage::disk('private')->path($import->file_path), basename($import->file_path));
    }

    protected function downloadErrorReport(array $uriVariables): Response
    {
        $import = $this->resolveImport($uriVariables);

        if (! $import->error_file_path || ! Storage::disk('private')->exists($import->error_file_path)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.error-report-not-found'));
        }

        return $this->stream(Storage::disk('private')->path($import->error_file_path), basename($import->error_file_path));
    }

    protected function downloadSample(array $uriVariables): Response
    {
        $type = (string) ($uriVariables['type'] ?? request()->route('type') ?? '');
        $format = strtolower((string) ($uriVariables['format'] ?? request()->route('format') ?? ''));

        if (! in_array($format, self::SUPPORTED_FORMATS, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.settings.data-transfer.import.sample-format-invalid'), 422);
        }

        $samplePath = config("importers.{$type}.sample_paths.{$format}");

        if (! $samplePath || ! Storage::exists($samplePath)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.sample-not-found'));
        }

        return $this->stream(Storage::path($samplePath), basename($samplePath));
    }

    protected function resolveImport(array $uriVariables): Import
    {
        $id = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

        $import = Import::find($id);
        if (! $import) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.data-transfer.import.not-found'));
        }

        return $import;
    }

    protected function stream(string $absolutePath, string $filename): Response
    {
        $contents = file_get_contents($absolutePath);

        return new Response((string) $contents, 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
