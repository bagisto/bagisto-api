<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminExportFile;
use Webkul\BagistoApi\Exception\AuthenticationException;

/**
 * File export for a reporting sub-page (the admin Export button). REST only.
 *
 * Mirrors `Reporting/Controller::export()` — runs the same `?type=` stat in its
 * table form ({ columns, records }) and streams it as csv, xls or xlsx. The columns
 * become the header row; each record's column-keyed values become the data rows.
 * Reporting has no ACL permission gate, so only authentication is required.
 *
 * One concrete subclass per sub-page sets $entity (sales / customers / products).
 */
abstract class AdminReportingExportProvider implements ProviderInterface
{
    use BuildsAdminExportFile;

    protected const EXPORT_MAX_ROWS = 50000;

    /** Sub-page: sales | customers | products. */
    protected string $entity;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $format = $this->resolveExportFormat('bagistoapi::app.admin.reporting.export-format-unsupported');

        $type = request()->query('type');

        $payload = AdminReportingProvider::buildPayload($this->entity, $type, 'table');

        $statistics = $payload['statistics'] ?? [];
        $columns = $statistics['columns'] ?? [];
        $records = $statistics['records'] ?? [];

        $rows = [];

        foreach ($records as $record) {
            if (count($rows) >= self::EXPORT_MAX_ROWS) {
                break;
            }

            $row = [];

            foreach ($columns as $col) {
                $key = $col['key'] ?? null;
                $value = $key !== null ? ($record[$key] ?? '') : '';
                $row[] = is_scalar($value) || $value === null ? $value : json_encode($value);
            }

            $rows[] = $row;
        }

        return $this->buildExportResponse(
            array_map(static fn ($col) => $col['label'] ?? $col['key'] ?? '', $columns),
            $rows,
            $this->entity.'-'.($payload['type'] ?? 'report'),
            $format,
        );
    }
}
