<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

/**
 * CSV export for the admin Settings → Tax Rates datagrid (the Export button).
 *
 * REST only (binary stream); honours the same filters as the listing.
 * Columns mirror the admin TaxRateDataGrid: ID, Identifier, State, Country,
 * Zip Code, Zip From, Zip To, Tax Rate.
 */
class AdminSettingsTaxRateExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'settings.taxes.tax_rates';
    }

    protected function exportFilename(): string
    {
        return 'tax-rates';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Identifier', 'State', 'Country', 'Zip Code', 'Zip From', 'Zip To', 'Tax Rate'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->id,
            $row->identifier,
            $row->state,
            $row->country,
            $row->zip_code,
            $row->zip_from,
            $row->zip_to,
            $row->tax_rate,
        ];
    }

    protected function exportQuery(array $args)
    {
        $query = DB::table('tax_rates')
            ->select('id', 'identifier', 'state', 'country', 'zip_code', 'zip_from', 'zip_to', 'tax_rate')
            ->orderByDesc('id');

        if (! empty($args['identifier'])) {
            $query->where('identifier', 'like', '%'.$args['identifier'].'%');
        }

        if (! empty($args['country'])) {
            $query->where('country', $args['country']);
        }

        if (! empty($args['state'])) {
            $query->where('state', $args['state']);
        }

        if (isset($args['tax_rate_from']) && $args['tax_rate_from'] !== '') {
            $query->where('tax_rate', '>=', (float) $args['tax_rate_from']);
        }

        if (isset($args['tax_rate_to']) && $args['tax_rate_to'] !== '') {
            $query->where('tax_rate', '<=', (float) $args['tax_rate_to']);
        }

        return $query;
    }
}
