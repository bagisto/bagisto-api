<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Http\Response;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminExportFile;

/**
 * File export for a reporting sub-page (the admin Export button). REST only.
 * Mirrors `Reporting/Controller::export()` — runs the same `?type=` stat in its.
 */
abstract class AdminReportingExportProvider implements ProviderInterface
{
    use BuildsAdminExportFile;

    /**
     * The export max rows constant.
     */
    protected const EXPORT_MAX_ROWS = 50000;

    /** Sub-page: sales | customers | products. */
    protected string $entity;

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Response
    {
        AdminReadPermissions::authorize($operation, null, $this->entity === 'overview' ? 'reporting' : 'reporting.'.$this->entity);

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

            foreach ($columns as $column) {
                $key = $column['key'] ?? null;
                $value = $key !== null ? ($record[$key] ?? '') : '';
                $row[] = is_scalar($value) || $value === null ? $value : json_encode($value);
            }

            $rows[] = $row;
        }

        return $this->buildExportResponse(
            array_map(static fn ($column) => $column['label'] ?? $column['key'] ?? '', $columns),
            $rows,
            $this->entity.'-'.($payload['type'] ?? 'report'),
            $format,
        );
    }
}
