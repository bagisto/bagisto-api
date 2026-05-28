<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsCurrency;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/currencies + adminSettingsCurrencies.
 *
 * Mirrors Webkul\Admin\DataGrids\Settings\CurrencyDataGrid — filters on code,
 * name, symbol; sort on id, code, name.
 */
class AdminSettingsCurrencyCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name'];
    }

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

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('currencies.code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('currencies.name', 'like', '%'.$args['name'].'%');
        }

        if (! empty($args['symbol'])) {
            $query->where('currencies.symbol', 'like', '%'.$args['symbol'].'%');
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'currencies.id',
            'code' => 'currencies.code',
            'name' => 'currencies.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'currencies.id', $direction);
    }

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
