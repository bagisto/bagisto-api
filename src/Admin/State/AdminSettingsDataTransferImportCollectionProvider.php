<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsDataTransferImport;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/data-transfer/imports + adminSettingsDataTransferImports.
 *
 * Filters: code (entity type — `imports.type` column), action, state, created_at range.
 * `type` is accepted as an alias for `code` for spec compatibility — both map to
 * the same underlying column.
 *
 * Sort: id (default desc), state, created_at.
 */
class AdminSettingsDataTransferImportCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'state', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('imports')->select('imports.*');
    }

    protected function applyFilters($query, array $args): void
    {
        $code = $args['code'] ?? $args['type'] ?? null;
        if (! empty($code)) {
            $query->where('imports.type', $code);
        }

        if (! empty($args['action'])) {
            $query->where('imports.action', $args['action']);
        }

        if (! empty($args['state'])) {
            $query->where('imports.state', $args['state']);
        }

        if (! empty($args['created_at_from'])) {
            $query->where('imports.created_at', '>=', $this->parseDate($args['created_at_from'], false));
        }

        if (! empty($args['created_at_to'])) {
            $query->where('imports.created_at', '<=', $this->parseDate($args['created_at_to'], true));
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'         => 'imports.id',
            'state'      => 'imports.state',
            'created_at' => 'imports.created_at',
        ];

        $query->orderBy($columnMap[$column] ?? 'imports.id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsDataTransferImport
    {
        $dto = new AdminSettingsDataTransferImport;

        $dto->id = (int) $row->id;
        $dto->code = $row->type ?? null;
        $dto->action = $row->action ?? null;
        $dto->state = $row->state ?? null;
        $dto->processInQueue = isset($row->process_in_queue) ? (bool) $row->process_in_queue : null;
        $dto->validationStrategy = $row->validation_strategy ?? null;
        $dto->allowedErrors = isset($row->allowed_errors) ? (int) $row->allowed_errors : null;
        $dto->processedRowsCount = isset($row->processed_rows_count) ? (int) $row->processed_rows_count : null;
        $dto->invalidRowsCount = isset($row->invalid_rows_count) ? (int) $row->invalid_rows_count : null;
        $dto->errorsCount = isset($row->errors_count) ? (int) $row->errors_count : null;
        $dto->fieldSeparator = $row->field_separator ?? null;
        $dto->filePath = $row->file_path ?? null;
        $dto->imagesDirectoryPath = $row->images_directory_path ?? null;
        $dto->errorFilePath = $row->error_file_path ?? null;
        $dto->startedAt = $row->started_at ? Carbon::parse($row->started_at)->toIso8601String() : null;
        $dto->completedAt = $row->completed_at ? Carbon::parse($row->completed_at)->toIso8601String() : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }

    private function parseDate(string $value, bool $endOfDay): string
    {
        try {
            $c = Carbon::parse($value);
        } catch (\Throwable) {
            return $value;
        }

        return $endOfDay ? $c->endOfDay()->toDateTimeString() : $c->startOfDay()->toDateTimeString();
    }
}
