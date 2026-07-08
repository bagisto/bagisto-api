<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * CSV export for a reporting sub-page (the admin Export button). REST only.
 *
 * Mirrors `Reporting/Controller::export()` — runs the same `?type=` stat in its
 * table form ({ columns, records }) and streams it as CSV. The columns become
 * the header row; each record's column-keyed values become the data rows.
 * Reporting has no ACL permission gate, so only authentication is required.
 *
 * One concrete subclass per sub-page sets $entity (sales / customers / products).
 */
abstract class AdminReportingExportProvider implements ProviderInterface
{
    protected const EXPORT_MAX_ROWS = 50000;

    /** Sub-page: sales | customers | products. */
    protected string $entity;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $format = strtolower((string) (request()->query('format') ?? 'csv'));
        if ($format !== 'csv') {
            throw new InvalidInputException(__('bagistoapi::app.admin.reporting.export-format-unsupported'), 422);
        }

        $type = request()->query('type');

        $payload = AdminReportingProvider::buildPayload($this->entity, $type, 'table');

        $statistics = $payload['statistics'] ?? [];
        $columns = $statistics['columns'] ?? [];
        $records = $statistics['records'] ?? [];

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, array_map(static fn ($col) => $col['label'] ?? $col['key'] ?? '', $columns));

        $count = 0;
        foreach ($records as $record) {
            if ($count++ >= self::EXPORT_MAX_ROWS) {
                break;
            }

            $row = [];
            foreach ($columns as $col) {
                $key = $col['key'] ?? null;
                $value = $key !== null ? ($record[$key] ?? '') : '';
                $row[] = is_scalar($value) || $value === null ? $value : json_encode($value);
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->entity.'-'.($payload['type'] ?? 'report').'.csv"',
        ]);
    }
}
