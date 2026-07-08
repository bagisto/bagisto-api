<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use ApiPlatform\Metadata\Operation;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Exception\InvalidInputException;

trait StreamsAdminCsvExport
{
    use ChecksAdminPermission;

    protected const EXPORT_MAX_ROWS = 50000;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        $this->authorizedAdmin($this->exportPermission());

        $format = strtolower((string) (request()->query('format') ?? 'csv'));
        if ($format !== 'csv') {
            throw new InvalidInputException(__('bagistoapi::app.admin.sales.export.format-unsupported'), 422);
        }

        $rows = $this->exportQuery(request()->query())->limit(self::EXPORT_MAX_ROWS)->get();

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $this->exportHeaders());

        foreach ($rows as $row) {
            fputcsv($handle, $this->exportRow($row));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->exportFilename().'"',
        ]);
    }

    abstract protected function exportPermission(): string;

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
