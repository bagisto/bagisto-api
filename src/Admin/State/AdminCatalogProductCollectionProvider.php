<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProduct;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for the admin Catalog → Products datagrid endpoint.
 *
 * REST: GET /api/admin/catalog/products
 * GraphQL: adminCatalogProducts query
 *
 * Mirrors Webkul\Admin\DataGrids\Catalog\ProductDataGrid 1:1 — same DB joins,
 * same Elasticsearch branch gated by core config.
 */
class AdminCatalogProductCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * Override provide() to check the Elasticsearch branch before delegating
     * to the DB-backed parent implementation.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if ($this->shouldUseElasticsearch()) {
            // Filled in Task 8. For now, fall through to DB path.
        }

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return [
            'product_id', 'name', 'sku', 'attribute_family', 'price',
            'quantity', 'status', 'type', 'channel',
        ];
    }

    protected function buildQuery(array $args)
    {
        $tablePrefix = DB::getTablePrefix();
        $locale = $args['locale'] ?? app()->getLocale();
        $channel = $args['channel'] ?? core()->getCurrentChannel()->code;

        return DB::table('product_flat')
            ->distinct()
            ->leftJoin('attribute_families as af', 'product_flat.attribute_family_id', '=', 'af.id')
            ->leftJoin('product_inventories', 'product_flat.product_id', '=', 'product_inventories.product_id')
            ->leftJoin('product_images', 'product_flat.product_id', '=', 'product_images.product_id')
            ->leftJoin('product_categories as pc', 'product_flat.product_id', '=', 'pc.product_id')
            ->leftJoin('category_translations as ct', function ($leftJoin) use ($locale) {
                $leftJoin->on('pc.category_id', '=', 'ct.category_id')
                    ->where('ct.locale', $locale);
            })
            ->select(
                'product_flat.product_id',
                'product_flat.sku',
                'product_flat.name',
                'product_flat.type',
                'product_flat.status',
                'product_flat.price',
                'product_flat.url_key',
                'product_flat.visible_individually',
                'product_flat.locale',
                'product_flat.channel',
                'product_flat.attribute_family_id',
                'af.name as attribute_family',
                'product_images.path as base_image',
                'pc.category_id',
                'ct.name as category_name',
            )
            ->addSelect(DB::raw('SUM(DISTINCT '.$tablePrefix.'product_inventories.qty) as quantity'))
            ->addSelect(DB::raw('COUNT(DISTINCT '.$tablePrefix.'product_images.id) as images_count'))
            ->where('product_flat.locale', $locale)
            ->where('product_flat.channel', $channel)
            ->groupBy('product_flat.product_id');
    }

    protected function applyFilters($query, array $args): void
    {
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

        // channel and locale are already enforced via where() in buildQuery.

        [$priceFrom, $priceTo] = $this->resolvePriceRange($args);
        if ($priceFrom !== null) {
            $query->where('product_flat.price', '>=', $priceFrom);
        }
        if ($priceTo !== null) {
            $query->where('product_flat.price', '<=', $priceTo);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'name'             => 'product_flat.name',
            'sku'              => 'product_flat.sku',
            'attribute_family' => 'af.id',
            'price'            => 'product_flat.price',
            'quantity'         => 'quantity',
            'product_id'       => 'product_flat.product_id',
            'status'           => 'product_flat.status',
            'type'             => 'product_flat.type',
            'channel'          => 'product_flat.channel',
        ];

        $orderColumn = $columnMap[$column] ?? 'product_flat.product_id';

        if ($column === 'quantity') {
            $query->orderBy(DB::raw('SUM(DISTINCT '.DB::getTablePrefix().'product_inventories.qty)'), $direction);

            return;
        }

        $query->orderBy($orderColumn, $direction);
    }

    protected function mapRow(object $row): AdminCatalogProduct
    {
        $dto = new AdminCatalogProduct;

        $dto->id = (int) $row->product_id;
        $dto->sku = $row->sku;
        $dto->name = $row->name;
        $dto->type = $row->type;
        $dto->status = (int) $row->status;
        $dto->price = $row->price !== null ? (string) $row->price : null;
        $dto->formattedPrice = $row->price !== null ? core()->formatPrice((float) $row->price) : null;
        $dto->quantity = $row->quantity !== null ? (int) $row->quantity : 0;
        $dto->baseImageUrl = $row->base_image ? Storage::url($row->base_image) : null;
        $dto->imagesCount = (int) ($row->images_count ?? 0);
        $dto->categoryId = $row->category_id !== null ? (int) $row->category_id : null;
        $dto->categoryName = $row->category_name;
        $dto->channel = $row->channel;
        $dto->locale = $row->locale;
        $dto->attributeFamilyId = $row->attribute_family_id !== null ? (int) $row->attribute_family_id : null;
        $dto->attributeFamilyName = $row->attribute_family;
        $dto->urlKey = $row->url_key;
        $dto->visibleIndividually = (bool) $row->visible_individually;

        return $dto;
    }

    protected function resolvePriceRange(array $args): array
    {
        $from = $args['price_from'] ?? null;
        $to = $args['price_to'] ?? null;

        if (($from === null || $to === null) && ! empty($args['price'])) {
            $parts = is_array($args['price']) ? $args['price'] : explode(',', (string) $args['price']);
            $from = $from ?? ($parts[0] ?? null);
            $to = $to ?? ($parts[1] ?? null);
        }

        $from = is_numeric($from) ? (float) $from : null;
        $to = is_numeric($to) ? (float) $to : null;

        return [$from, $to];
    }

    protected function shouldUseElasticsearch(): bool
    {
        return core()->getConfigData('catalog.products.search.engine') === 'elastic'
            && core()->getConfigData('catalog.products.search.admin_mode') === 'elastic';
    }
}
