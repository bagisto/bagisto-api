<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Illuminate\Http\Response;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Webkul\BagistoApi\Admin\Exports\AdminGridExport;
use Webkul\BagistoApi\Exception\InvalidInputException;

trait BuildsAdminExportFile
{
    protected const EXPORT_FORMATS = ['csv', 'xls', 'xlsx'];

    protected const EXPORT_MIME_TYPES = [
        'csv' => 'text/csv; charset=UTF-8',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    protected function resolveExportFormat(string $unsupportedLangKey): string
    {
        $format = strtolower((string) (request()->query('format') ?? 'csv'));

        if (! in_array($format, self::EXPORT_FORMATS, true)) {
            throw new InvalidInputException(__($unsupportedLangKey), 422);
        }

        return $format;
    }

    protected function buildExportResponse(array $headings, array $rows, string $baseName, string $format): Response
    {
        $headings = array_map(fn ($value) => $this->sanitizeExportValue($value), $headings);
        $rows = array_map(fn ($row) => array_map(fn ($value) => $this->sanitizeExportValue($value), $row), $rows);

        $body = match ($format) {
            'xls' => Excel::raw(new AdminGridExport($headings, $rows), ExcelWriter::XLS),
            'xlsx' => Excel::raw(new AdminGridExport($headings, $rows), ExcelWriter::XLSX),
            default => $this->toCsvString($headings, $rows),
        };

        return new Response($body, 200, [
            'Content-Type' => self::EXPORT_MIME_TYPES[$format],
            'Content-Disposition' => 'attachment; filename="'.$baseName.'.'.$format.'"',
        ]);
    }

    protected function toCsvString(array $headings, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $headings);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Guard against spreadsheet formula injection, mirroring the core datagrid export.
     */
    protected function sanitizeExportValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = ltrim($value);

        if ($trimmed === '') {
            return $value;
        }

        if (preg_match('/^[=+\-@|%\t\r\n]/u', $trimmed)) {
            return "'".$value;
        }

        return $value;
    }
}
