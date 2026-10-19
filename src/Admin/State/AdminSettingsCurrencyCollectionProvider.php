<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsCurrency;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

class AdminSettingsCurrencyCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

    /**
     * Build query.
     */
    protected function buildQuery(array $args)
    {
        return DB::table('currencies')->select(
            'currencies.id',
            'currencies.code',
            'currencies.name',
            'currencies.symbol',
            'currencies.decimal',
            'currencies.group_separator',
            'currencies.decimal_separator',
            'currencies.currency_position',
            'currencies.created_at',
            'currencies.updated_at',
        );
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('currencies.code', db_grammar()->caseInsensitiveLike(), '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('currencies.name', db_grammar()->caseInsensitiveLike(), '%'.$args['name'].'%');
        }

        if (! empty($args['symbol'])) {
            $query->where('currencies.symbol', db_grammar()->caseInsensitiveLike(), '%'.$args['symbol'].'%');
        }
    }

    /**
     * Apply sort.
     */
    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id' => 'currencies.id',
            'code' => 'currencies.code',
            'name' => 'currencies.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'currencies.id', $direction);
    }

    /**
     * Map row.
     */
    protected function mapRow(object $row): AdminSettingsCurrency
    {
        $dto = new AdminSettingsCurrency;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;
        $dto->symbol = $row->symbol;
        $dto->decimal = $row->decimal !== null ? (int) $row->decimal : null;
        $dto->groupSeparator = $row->group_separator;
        $dto->decimalSeparator = $row->decimal_separator;
        $dto->currencyPosition = $row->currency_position;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
