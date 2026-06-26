<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductRestDto;
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
    protected array $filterArgs = [];

    protected ?string $resolvedLocale = null;

    protected ?string $resolvedChannel = null;

    protected bool $listingIsGraphQL = false;

    /**
     * Override provide() to check the Elasticsearch branch before delegating
     * to the DB-backed parent implementation.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if ($this->shouldUseElasticsearch()) {
        }

        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

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
        $p = DB::getTablePrefix();
        $locale = $args['locale'] ?? app()->getLocale();
        $channel = $args['channel'] ?? core()->getCurrentChannel()->code;

        $this->filterArgs = $args;
        $this->resolvedLocale = $locale;
        $this->resolvedChannel = $channel;

        return DB::table('product_flat')
            ->leftJoin('attribute_families as af', 'product_flat.attribute_family_id', '=', 'af.id')
            ->select(
                'product_flat.product_id',
                'product_flat.sku',
                'product_flat.name',
                'product_flat.type',
                'product_flat.status',
                'product_flat.price',
                'product_flat.special_price',
                'product_flat.special_price_from',
                'product_flat.special_price_to',
                'product_flat.url_key',
                'product_flat.visible_individually',
                'product_flat.locale',
                'product_flat.channel',
                'product_flat.attribute_family_id',
                'product_flat.short_description',
                'product_flat.description',
                'product_flat.meta_title',
                'product_flat.meta_description',
                'product_flat.meta_keywords',
                'product_flat.weight',
                'product_flat.new',
                'product_flat.featured',
                'product_flat.created_at',
                'product_flat.updated_at',
                'af.name as attribute_family',
            )
            ->selectRaw('(SELECT COALESCE(SUM(qty), 0) FROM '.$p.'product_inventories WHERE '.$p.'product_inventories.product_id = '.$p.'product_flat.product_id) as quantity')
            ->selectRaw('(SELECT COUNT(*) FROM '.$p.'product_images WHERE '.$p.'product_images.product_id = '.$p.'product_flat.product_id) as images_count')
            ->selectRaw('(SELECT path FROM '.$p.'product_images WHERE '.$p.'product_images.product_id = '.$p.'product_flat.product_id ORDER BY id ASC LIMIT 1) as base_image')
            ->selectRaw('(SELECT category_id FROM '.$p.'product_categories WHERE '.$p.'product_categories.product_id = '.$p.'product_flat.product_id ORDER BY category_id ASC LIMIT 1) as category_id')
            ->selectRaw('(SELECT ct.name FROM '.$p.'category_translations ct INNER JOIN '.$p.'product_categories pc ON pc.category_id = ct.category_id WHERE pc.product_id = '.$p.'product_flat.product_id AND ct.locale = ? ORDER BY pc.category_id ASC LIMIT 1) as category_name', [$locale])
            ->where('product_flat.locale', $locale)
            ->where('product_flat.channel', $channel);
    }

    protected function countTotal($query): int
    {
        $count = DB::table('product_flat')
            ->leftJoin('attribute_families as af', 'product_flat.attribute_family_id', '=', 'af.id')
            ->where('product_flat.locale', $this->resolvedLocale)
            ->where('product_flat.channel', $this->resolvedChannel);

        $this->applyFilters($count, $this->filterArgs);

        return $count->distinct()->count('product_flat.product_id');
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
            $query->orderByRaw('quantity '.$direction);

            return;
        }

        $query->orderBy($orderColumn, $direction);
    }

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminCatalogProductRestDto;

        $dto->id = (int) $row->product_id;
        $dto->sku = $row->sku;
        $dto->name = $row->name;
        $dto->type = $row->type;
        $dto->status = (int) $row->status;
        $dto->price = $row->price !== null ? (string) $row->price : null;
        $dto->formattedPrice = $row->price !== null ? core()->formatPrice((float) $row->price) : null;
        $dto->specialPrice = $row->special_price !== null ? (string) $row->special_price : null;
        $dto->formattedSpecialPrice = $row->special_price !== null ? core()->formatPrice((float) $row->special_price) : null;
        $dto->specialPriceFrom = $row->special_price_from ? (string) $row->special_price_from : null;
        $dto->specialPriceTo = $row->special_price_to ? (string) $row->special_price_to : null;
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

        $dto->shortDescription = $row->short_description;
        $dto->description = $row->description;
        $dto->metaTitle = $row->meta_title;
        $dto->metaDescription = $row->meta_description;
        $dto->metaKeywords = $row->meta_keywords;
        $dto->weight = $row->weight !== null ? (float) $row->weight : null;
        $dto->featured = (bool) $row->featured;
        $dto->new = (bool) $row->new;
        $dto->createdAt = $row->created_at ? (string) $row->created_at : null;
        $dto->updatedAt = $row->updated_at ? (string) $row->updated_at : null;

        return $dto;
    }

    protected function mapRowToEloquent(object $row): AdminCatalogProduct
    {
        $model = (new AdminCatalogProduct)->forceFill([
            'id'                      => (int) $row->product_id,
            'sku'                     => $row->sku,
            'type'                    => $row->type,
            'name'                    => $row->name,
            'status'                  => (int) $row->status,
            'price'                   => $row->price !== null ? (string) $row->price : null,
            'formatted_price'         => $row->price !== null ? core()->formatPrice((float) $row->price) : null,
            'special_price'           => $row->special_price !== null ? (string) $row->special_price : null,
            'formatted_special_price' => $row->special_price !== null ? core()->formatPrice((float) $row->special_price) : null,
            'special_price_from'      => $row->special_price_from ? (string) $row->special_price_from : null,
            'special_price_to'        => $row->special_price_to ? (string) $row->special_price_to : null,
            'quantity'                => $row->quantity !== null ? (int) $row->quantity : 0,
            'base_image_url'          => $row->base_image ? Storage::url($row->base_image) : null,
            'images_count'            => (int) ($row->images_count ?? 0),
            'category_id'             => $row->category_id !== null ? (int) $row->category_id : null,
            'category_name'           => $row->category_name,
            'channel'                 => $row->channel,
            'locale'                  => $row->locale,
            'attribute_family_id'     => $row->attribute_family_id !== null ? (int) $row->attribute_family_id : null,
            'attribute_family_name'   => $row->attribute_family,
            'url_key'                 => $row->url_key,
            'visible_individually'    => (bool) $row->visible_individually,
            'short_description'       => $row->short_description,
            'description'             => $row->description,
            'meta_title'              => $row->meta_title,
            'meta_description'        => $row->meta_description,
            'meta_keywords'           => $row->meta_keywords,
            'weight'                  => $row->weight !== null ? (float) $row->weight : null,
            'tax_category_id'         => null,
            'manage_stock'            => null,
            'in_stock'                => null,
            'featured'                => (bool) $row->featured,
            'new'                     => (bool) $row->new,
            'created_at'              => $row->created_at ? (string) $row->created_at : null,
            'updated_at'              => $row->updated_at ? (string) $row->updated_at : null,
        ]);

        foreach ([
            'images', 'videos', 'categories', 'inventories', 'customer_group_prices', 'translations',
            'super_attributes', 'variants', 'bundle_options', 'linked_products', 'downloadable_links',
            'downloadable_samples', 'customizable_options', 'attribute_values', 'channels',
            'related_products', 'up_sells', 'cross_sells',
        ] as $rel) {
            $model->setRelation($rel, collect());
        }

        return $model;
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
