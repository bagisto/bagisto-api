<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxRate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/tax-rates + adminSettingsTaxRates.
 */
class AdminSettingsTaxRateCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'identifier', 'tax_rate'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('tax_rates')->select(
            'id', 'identifier', 'is_zip', 'zip_code', 'zip_from', 'zip_to',
            'state', 'country', 'tax_rate', 'created_at', 'updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['identifier'])) {
            $query->where('identifier', 'like', '%'.$args['identifier'].'%');
        }
        if (! empty($args['country'])) {
            $query->where('country', $args['country']);
        }
        if (! empty($args['state'])) {
            $query->where('state', $args['state']);
        }
        if (isset($args['tax_rate_from']) && $args['tax_rate_from'] !== '' && $args['tax_rate_from'] !== null) {
            $query->where('tax_rate', '>=', (float) $args['tax_rate_from']);
        }
        if (isset($args['tax_rate_to']) && $args['tax_rate_to'] !== '' && $args['tax_rate_to'] !== null) {
            $query->where('tax_rate', '<=', (float) $args['tax_rate_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);
        $query->orderBy($column, $direction);
    }

    protected function mapRow(object $row): AdminSettingsTaxRate
    {
        $dto = new AdminSettingsTaxRate;
        $dto->id = (int) $row->id;
        $dto->identifier = $row->identifier;
        $dto->isZip = (bool) $row->is_zip;
        $dto->zipCode = $row->zip_code;
        $dto->zipFrom = $row->zip_from;
        $dto->zipTo = $row->zip_to;
        $dto->state = $row->state;
        $dto->country = $row->country;
        $dto->taxRate = $row->tax_rate !== null ? (float) $row->tax_rate : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
