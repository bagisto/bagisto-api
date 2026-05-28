<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsExchangeRate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/exchange-rates +
 * adminSettingsExchangeRates GraphQL query.
 *
 * Joins currency_exchange_rates × currencies so the row carries the target
 * currency code + name without a follow-up call.
 */
class AdminSettingsExchangeRateCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'target_currency', 'rate'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('currency_exchange_rates as cer')
            ->leftJoin('currencies as c', 'c.id', '=', 'cer.target_currency')
            ->select(
                'cer.id',
                'cer.target_currency',
                'cer.rate',
                'cer.created_at',
                'cer.updated_at',
                'c.code as currency_code',
                'c.name as currency_name',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['target_currency'])) {
            $query->where('cer.target_currency', (int) $args['target_currency']);
        }

        if (isset($args['rate_from']) && $args['rate_from'] !== '' && $args['rate_from'] !== null) {
            $query->where('cer.rate', '>=', (float) $args['rate_from']);
        }

        if (isset($args['rate_to']) && $args['rate_to'] !== '' && $args['rate_to'] !== null) {
            $query->where('cer.rate', '<=', (float) $args['rate_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'              => 'cer.id',
            'target_currency' => 'cer.target_currency',
            'rate'            => 'cer.rate',
        ];

        $query->orderBy($columnMap[$column] ?? 'cer.id', $direction);
    }

    protected function mapRow(object $row): AdminSettingsExchangeRate
    {
        $dto = new AdminSettingsExchangeRate;

        $dto->id = (int) $row->id;
        $dto->targetCurrency = $row->target_currency !== null ? (int) $row->target_currency : null;
        $dto->targetCurrencyCode = $row->currency_code ?? null;
        $dto->targetCurrencyName = $row->currency_name ?? null;
        $dto->rate = $row->rate !== null ? (float) $row->rate : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
