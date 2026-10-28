<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

class AdminSettingsTaxRateExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    /**
     * Export permission.
     */
    protected function exportPermission(): string
    {
        return 'settings.taxes.tax_rates';
    }

    /**
     * Export filename.
     */
    protected function exportFilename(): string
    {
        return 'tax-rates';
    }

    /**
     * Export headers.
     */
    protected function exportHeaders(): array
    {
        return ['ID', 'Identifier', 'State', 'Country', 'Zip Code', 'Zip From', 'Zip To', 'Tax Rate'];
    }

    /**
     * Export row.
     */
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

    /**
     * Export query.
     */
    protected function exportQuery(array $args)
    {
        $query = DB::table('tax_rates')
            ->select('id', 'identifier', 'state', 'country', 'zip_code', 'zip_from', 'zip_to', 'tax_rate')
            ->orderByDesc('id');

        if (! empty($args['identifier'])) {
            $query->where('identifier', db_grammar()->caseInsensitiveLike(), '%'.$args['identifier'].'%');
        }

        if (! empty($args['country'])) {
            $query->where('country', $args['country']);
        }

        if (! empty($args['state'])) {
            $query->where('state', $args['state']);
        }

        if (
            isset($args['tax_rate_from'])
            && $args['tax_rate_from'] !== ''
        ) {
            $query->where('tax_rate', '>=', (float) $args['tax_rate_from']);
        }

        if (
            isset($args['tax_rate_to'])
            && $args['tax_rate_to'] !== ''
        ) {
            $query->where('tax_rate', '<=', (float) $args['tax_rate_to']);
        }

        return $query;
    }
}
