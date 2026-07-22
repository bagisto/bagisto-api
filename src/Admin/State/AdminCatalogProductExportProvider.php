<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\State\Concerns\StreamsAdminExport;

class AdminCatalogProductExportProvider implements ProviderInterface
{
    use StreamsAdminExport;

    protected function exportPermission(): string
    {
        return 'catalog.products';
    }

    protected function exportFilename(): string
    {
        return 'products';
    }

    protected function exportHeaders(): array
    {
        return ['ID', 'Name', 'SKU', 'Attribute Family', 'Price', 'Quantity', 'Status', 'Category', 'Type'];
    }

    protected function exportRow(object $row): array
    {
        return [
            $row->product_id,
            $row->name,
            $row->sku,
            $row->attribute_family,
            $this->safeFormatBasePrice($row->price),
            $row->quantity !== null ? (int) $row->quantity : 0,
            (int) $row->status === 1 ? 'Active' : 'Disabled',
            $row->category_name,
            $row->type,
        ];
    }

    protected function exportQuery(array $args)
    {
        $p = DB::getTablePrefix();
        $locale = $args['locale'] ?? app()->getLocale();
        $channel = $args['channel'] ?? core()->getCurrentChannel()->code;

        $query = DB::table('product_flat')
            ->leftJoin('attribute_families as af', 'product_flat.attribute_family_id', '=', 'af.id')
            ->select(
                'product_flat.product_id',
                'product_flat.name',
                'product_flat.sku',
                'product_flat.type',
                'product_flat.status',
                'product_flat.price',
                'af.name as attribute_family',
            )
            ->selectRaw('(SELECT COALESCE(SUM(qty), 0) FROM '.$p.'product_inventories WHERE '.$p.'product_inventories.product_id = '.$p.'product_flat.product_id) as quantity')
            ->selectRaw('(SELECT ct.name FROM '.$p.'category_translations ct INNER JOIN '.$p.'product_categories pc ON pc.category_id = ct.category_id WHERE pc.product_id = '.$p.'product_flat.product_id AND ct.locale = ? ORDER BY pc.category_id ASC LIMIT 1) as category_name', [$locale])
            ->where('product_flat.locale', $locale)
            ->where('product_flat.channel', $channel)
            ->orderByDesc('product_flat.product_id');

        if (! empty($args['product_id'])) {
            $ids = is_array($args['product_id'])
                ? $args['product_id']
                : array_filter(array_map('trim', explode(',', (string) $args['product_id'])));
            $ids = array_values(array_filter(array_map('intval', $ids)));
            if ($ids) {
                $query->whereIn('product_flat.product_id', $ids);
            }
        }
        if (! empty($args['sku'])) {
            $query->where('product_flat.sku', 'like', '%'.$args['sku'].'%');
        }
        if (! empty($args['name'])) {
            $query->where('product_flat.name', 'like', '%'.$args['name'].'%');
        }
        if (! empty($args['type'])) {
            $query->where('product_flat.type', (string) $args['type']);
        }
        if (isset($args['status']) && in_array((string) $args['status'], ['0', '1'], true)) {
            $query->where('product_flat.status', (int) $args['status']);
        }
        if (! empty($args['attribute_family'])) {
            $query->where('af.id', (int) $args['attribute_family']);
        }

        $from = $args['price_from'] ?? null;
        $to = $args['price_to'] ?? null;
        if (($from === null || $to === null) && ! empty($args['price'])) {
            $parts = is_array($args['price']) ? $args['price'] : explode(',', (string) $args['price']);
            $from = $from ?? ($parts[0] ?? null);
            $to = $to ?? ($parts[1] ?? null);
        }
        if (is_numeric($from)) {
            $query->where('product_flat.price', '>=', (float) $from);
        }
        if (is_numeric($to)) {
            $query->where('product_flat.price', '<=', (float) $to);
        }

        return $query;
    }
}
