<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingEvent;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/events + adminMarketingEvents.
 *
 * Filters: name (LIKE), date range (date_from/date_to).
 * Sort:    id (default desc), name, date.
 */
class AdminMarketingEventCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name', 'date'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('marketing_events')->select(
            'marketing_events.id',
            'marketing_events.name',
            'marketing_events.description',
            'marketing_events.date',
            'marketing_events.created_at',
            'marketing_events.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('marketing_events.name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['date_from'])) {
            $query->where('marketing_events.date', '>=', $args['date_from']);
        }

        if (! empty($args['date_to'])) {
            $query->where('marketing_events.date', '<=', $args['date_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'marketing_events.id',
            'name' => 'marketing_events.name',
            'date' => 'marketing_events.date',
        ];

        $query->orderBy($columnMap[$column] ?? 'marketing_events.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingEvent
    {
        $dto = new AdminMarketingEvent;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->date = $row->date;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
