<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Metadata\Operation;
use Illuminate\Http\Response;

trait StreamsAdminExport
{
    use BuildsAdminExportFile;
    use ChecksAdminPermission;

    protected const EXPORT_MAX_ROWS = 50000;

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

    abstract protected function exportPermission(): string;

    /**
     * File name without the extension — the requested format supplies it.
     */
    abstract protected function exportFilename(): string;

    abstract protected function exportHeaders(): array;

    abstract protected function exportQuery(array $args);

    abstract protected function exportRow(object $row): array;

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
