<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Metadata\Operation;
use Illuminate\Http\Response;

trait StreamsAdminExport
{
    use BuildsAdminExportFile;
    use ChecksAdminPermission;

    /**
     * The export max rows constant.
     */
    protected const EXPORT_MAX_ROWS = 50000;

    /**
     * Export permission.
     */
    abstract protected function exportPermission(): string;

    /**
     * File name without the extension — the requested format supplies it.
     */
    abstract protected function exportFilename(): string;

    /**
     * Export headers.
     */
    abstract protected function exportHeaders(): array;

    /**
     * Export query.
     */
    abstract protected function exportQuery(array $args);

    /**
     * Export row.
     */
    abstract protected function exportRow(object $row): array;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $this->authorizedAdmin($this->exportPermission());

        $format = $this->resolveExportFormat('bagistoapi::app.admin.sales.export.format-unsupported');

        $rows = $this->exportQuery(request()->query())->limit(self::EXPORT_MAX_ROWS)->get();

        return $this->buildExportResponse(
            $this->exportHeaders(),
            $rows->map(fn ($row) => array_values($this->exportRow($row)))->all(),
            $this->exportFilename(),
            $format,
        );
    }

    /**
     * Safe format base price.
     */
    protected function safeFormatBasePrice($amount): string
    {
        if ($amount === null) {
            return '';
        }

        try {
            return core()->formatBasePrice((float) $amount);
        } catch (\Throwable) {
            return (string) $amount;
        }
    }
}
