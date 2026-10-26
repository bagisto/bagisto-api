<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator as LaravelPaginator;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm;

class ProductGraphQLProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

    /**
     * The attribute type cache.
     */
    private ?array $attributeTypeCache = null;

    /**
     * The attribute scope cache.
     */
    private ?array $attributeScopeCache = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->loadAttributeCaches();

        $args = $context['args'] ?? [];
        $locale = $args['locale'] ?? request()->attributes->get('bagisto_locale');
        $channel = $args['channel'] ?? request()->attributes->get('bagisto_channel');

        $query = Product::query()->visibleInStorefront($channel);

        $this->applySearch($query, $args['query'] ?? null);

        [$sortKey, $reverse] = isset($args['sortKey'])
            ? [strtoupper($args['sortKey']), (bool) ($args['reverse'] ?? false)]
            : $this->defaultSort();

        $sortSelects = $this->applySort(
            $query,
            $sortKey,
            $reverse ? 'desc' : 'asc',
            $locale,
            $channel,
        );

        $filters = $this->parseFilters($args['filter'] ?? null);
        $isPlainSearch = $filters === [];

        $this->applyBasicFilters($query, $filters);
        $this->applyAttributeFilters($query, $filters, $locale, $channel);

        $query->select('products.*')->distinct();

        foreach ($sortSelects as $sortSelect) {
            $query->addSelect(\DB::raw($sortSelect));
        }

        $query->with([
            'attribute_family',
            'images',
            'attribute_values',
            'product_flats',
            'price_indices',
            'super_attributes',
            'variants' => fn ($query) => $query->without(['variants', 'super_attributes', 'attribute_values', 'attribute_family']),
            'booking_products' => fn ($query) => $query->select('id', 'product_id', 'type'),
        ]);

        $total = (clone $query)
            ->reorder()
            ->distinct()
            ->count('products.id');

        [$offset, $limit] = $this->resolveCursorWindow($args, $total, $this->requestedPageSize($args));
        $isFirstPage = $offset === 0;

        $items = $limit > 0
            ? $query->distinct()->offset($offset)->limit($limit)->get()
            : $query->getModel()->newCollection();

        $this->propagateContext($items, $locale, $channel);

        if (
            $isFirstPage
            && $isPlainSearch
        ) {
            $this->recordSearchTerm($args['query'] ?? null, $total, $locale);
        }

        return new Paginator(
            new LaravelPaginator(
                $items,
                $total,
                max(1, $limit),
                $total > 0 ? (int) floor($offset / max(1, $limit)) + 1 : 1,
                ['path' => request()->url()]
            )
        );
    }

    /**
     * Record a storefront search the way core does, so the admin Search Terms list shows what
     * mobile and headless clients searched. Only a plain first-page search is recorded.
     */
    private function recordSearchTerm(mixed $term, int $total, ?string $locale): void
    {
        $term = is_string($term) ? trim($term) : '';

        if (
            $term === ''
            || mb_strlen($term) > 255
        ) {
            return;
        }

        try {
            UpdateCreateSearchTerm::dispatch([
                'term' => $term,
                'results' => $total,
                'channel_id' => core()->getCurrentChannel()->id,
                'locale' => $locale ?: app()->getLocale(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Search term was not recorded.', ['message' => $exception->getMessage()]);
        }
    }

    /**
     * Cache attribute types and scope flags once for the request.
     */
    private function loadAttributeCaches(): void
    {
        $this->attributeTypeCache = \DB::table('attributes')
            ->pluck('type', 'code')
            ->toArray();

        $this->attributeScopeCache = \DB::table('attributes')
            ->get(['code', 'value_per_locale', 'value_per_channel'])
            ->keyBy('code')
            ->toArray();
    }

    /**
     * Match the search term against the SKU and the product name.
     */
    private function applySearch($query, mixed $searchTerm): void
    {
        if (empty($searchTerm)) {
            return;
        }

        $query->where(function ($query) use ($searchTerm) {
            $query->where('sku', db_grammar()->caseInsensitiveLike(), "%{$searchTerm}%")
                ->orWhereHas('attribute_values', function ($attribute) use ($searchTerm) {
                    $attribute->where('attribute_id', 2)
                        ->where('text_value', db_grammar()->caseInsensitiveLike(), "%{$searchTerm}%");
                });
        });
    }

    /**
     * Order the query and return the expressions that must join the select list for it.
     *
     * @return string[]
     */
    private function applySort($query, string $sortKey, string $direction, $locale, $channel): array
    {
        return match ($sortKey) {
            'TITLE', 'NAME' => $this->sortByName($query, $direction, $locale, $channel),
            'CREATED_AT' => $this->sortByColumn($query, 'products.created_at', $direction),
            'UPDATED_AT' => $this->sortByColumn($query, 'products.updated_at', $direction),
            'PRICE' => $this->sortByPrice($query, $direction),
            default => $this->sortById($query, $direction),
        };
    }

    /**
     * Resolve the storefront's configured default sort as a (sortKey, reverse) pair.
     */
    private function defaultSort(): array
    {
        $value = (string) (core()->getConfigData('catalog.products.storefront.sort_by') ?: 'price-desc');

        $position = strrpos($value, '-');

        $sort = $position === false ? $value : substr($value, 0, $position);

        $order = $position === false ? 'desc' : substr($value, $position + 1);

        $sortKey = match ($sort) {
            'name' => 'NAME',
            'created_at' => 'CREATED_AT',
            default => 'PRICE',
        };

        return [$sortKey, $order !== 'asc'];
    }

    /**
     * Sort by the product name for the requested scope, falling back to the unscoped value.
     *
     * @return string[]
     */
    private function sortByName($query, string $direction, $locale, $channel): array
    {
        $prefix = \DB::getTablePrefix();

        $scope = \DB::table('attributes')
            ->where('id', 2)
            ->first(['value_per_locale', 'value_per_channel']);

        $perLocale = (bool) ($scope->value_per_locale ?? true);
        $perChannel = (bool) ($scope->value_per_channel ?? false);

        $query->leftJoin('product_attribute_values as pav_name_locale', function ($join) use ($locale, $channel, $perLocale, $perChannel) {
            $join->on('products.id', '=', 'pav_name_locale.product_id')
                ->where('pav_name_locale.attribute_id', 2);

            if (
                $locale
                && $perLocale
            ) {
                $join->where('pav_name_locale.locale', $locale);
            }

            if (
                $channel
                && $perChannel
            ) {
                $join->where('pav_name_locale.channel', $channel);
            }
        });

        $query->leftJoin('product_attribute_values as pav_name_fallback', function ($join) {
            $join->on('products.id', '=', 'pav_name_fallback.product_id')
                ->where('pav_name_fallback.attribute_id', 2)
                ->whereNull('pav_name_fallback.locale')
                ->whereNull('pav_name_fallback.channel');
        });

        $query->orderBy('sort_name', $direction)
            ->orderBy('products.id', $direction)
            ->select('products.*');

        return ["COALESCE({$prefix}pav_name_locale.text_value, {$prefix}pav_name_fallback.text_value) as sort_name"];
    }

    /**
     * Sort by a product column, with the product id as a tie-breaker.
     *
     * @return string[]
     */
    private function sortByColumn($query, string $column, string $direction): array
    {
        $query->orderBy($column, $direction)
            ->orderBy('products.id', $direction);

        return [];
    }

    /**
     * Sort by product id.
     *
     * @return string[]
     */
    private function sortById($query, string $direction): array
    {
        $query->orderBy('products.id', $direction);

        return [];
    }

    /**
     * Sort by the minimal price of the customer's group on the current channel, one index row per product.
     *
     * @return string[]
     */
    private function sortByPrice($query, string $direction): array
    {
        $customerGroup = $this->currentCustomerGroup();

        $channelId = (int) core()->getCurrentChannel()->id;

        $query->leftJoin('product_price_indices', function ($join) use ($customerGroup, $channelId) {
            $join->on('products.id', '=', 'product_price_indices.product_id')
                ->where('product_price_indices.customer_group_id', $customerGroup->id)
                ->where('product_price_indices.channel_id', $channelId);
        })
            ->orderBy('sort_price', $direction)
            ->orderBy('products.id', $direction)
            ->select('products.*');

        return ['product_price_indices.min_price as sort_price'];
    }

    /**
     * Read the filter argument, which arrives either as a JSON string or as a map.
     */
    private function parseFilters(mixed $filter): array
    {
        if (empty($filter)) {
            return [];
        }

        if (is_array($filter)) {
            return $filter;
        }

        if (! is_string($filter)) {
            return [];
        }

        $decoded = json_decode($filter, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : [];
    }

    /**
     * Apply the filters that are not attribute filters, removing each one it consumes.
     */
    private function applyBasicFilters($query, array &$filters): void
    {
        foreach (['type', 'sku'] as $column) {
            if (! empty($filters[$column])) {
                $query->where('products.'.$column, $filters[$column]);
                unset($filters[$column]);
            }
        }

        $this->applyFlatFlagFilters($query, $filters);
        $this->applyCategoryFilter($query, $filters);
        $this->applyPriceFilter($query, $filters);
    }

    /**
     * Both flags live on product_flat. Join it once, otherwise combining the
     * two filters aliases the same table twice and the query fails.
     */
    private function applyFlatFlagFilters($query, array &$filters): void
    {
        $flatFlags = array_intersect_key($filters, array_flip(['new', 'featured']));

        if (! $flatFlags) {
            return;
        }

        $query->leftJoin('product_flat', 'products.id', '=', 'product_flat.product_id')
            ->select('products.*')
            ->distinct();

        foreach ($flatFlags as $flag => $value) {
            $query->where('product_flat.'.$flag, filter_var($value, FILTER_VALIDATE_BOOLEAN));
        }

        unset($filters['new'], $filters['featured']);
    }

    /**
     * Apply category filter.
     */
    private function applyCategoryFilter($query, array &$filters): void
    {
        if (empty($filters['category_id'])) {
            return;
        }

        $categoryId = (int) $filters['category_id'];

        $query->whereHas('categories', function ($query) use ($categoryId) {
            $query->where('id', $categoryId);
        });

        unset($filters['category_id']);
    }

    /**
     * Filter by the price range of the customer's group, accepting `price` as "from,to".
     */
    private function applyPriceFilter($query, array &$filters): void
    {
        if (
            isset($filters['price'])
            && is_string($filters['price'])
            && $filters['price'] !== ''
        ) {
            $priceRange = array_map('trim', explode(',', $filters['price']));

            $filters['price_from'] ??= current($priceRange);
            $filters['price_to'] ??= end($priceRange);
        }

        $priceFrom = $filters['price_from'] ?? null;
        $priceTo = $filters['price_to'] ?? null;

        unset($filters['price'], $filters['price_from'], $filters['price_to']);

        if (
            ! is_numeric($priceFrom)
            && ! is_numeric($priceTo)
        ) {
            return;
        }

        $from = is_numeric($priceFrom) ? (float) core()->convertToBasePrice((float) $priceFrom) : null;
        $to = is_numeric($priceTo) ? (float) core()->convertToBasePrice((float) $priceTo) : null;
        $customerGroupId = $this->currentCustomerGroup()->id;

        $query->whereHas('price_indices', function ($query) use ($from, $to, $customerGroupId) {
            $query->where('customer_group_id', $customerGroupId);

            if (! is_null($from)) {
                $query->where('min_price', '>=', $from);
            }

            if (! is_null($to)) {
                $query->where('min_price', '<=', $to);
            }
        });
    }

    /**
     * Filter by attribute values, matching either the product itself or one of its variants.
     */
    private function applyAttributeFilters($query, array $filters, $locale, $channel): void
    {
        $attributeFilters = $this->collectAttributeFilters($filters);

        if (empty($attributeFilters)) {
            return;
        }

        $prefix = \DB::getTablePrefix();

        $query->leftJoin('products as variants', \DB::raw('COALESCE('.$prefix.'variants.parent_id, '.$prefix.'variants.id)'), '=', 'products.id');

        $this->joinAttributeValues($query, array_keys($attributeFilters));

        $query->where(function ($filterQuery) use ($attributeFilters, $locale, $channel) {
            $filterQuery->where($this->matchEveryAttribute($attributeFilters, 'product', $locale, $channel))
                ->orWhere($this->matchEveryAttribute($attributeFilters, 'variant', $locale, $channel));
        });
    }

    /**
     * Collect the filters that target filterable attributes, keyed by attribute code.
     *
     * @return array<string, array{term: mixed, matchType: string}>
     */
    private function collectAttributeFilters(array $filters): array
    {
        $attributeFilters = [];

        foreach ($filters as $attrCode => $spec) {
            if (
                in_array($attrCode, ['pageSize', 'first', 'last', 'after', 'before'], true)
                || ! array_key_exists($attrCode, $this->attributeTypeCache)
            ) {
                continue;
            }

            $attributeFilters[$attrCode] = [
                'term' => $spec['match'] ?? $spec,
                'matchType' => strtoupper($spec['match_type'] ?? ''),
            ];
        }

        return $attributeFilters;
    }

    /**
     * Join the attribute value table once for the product and once for its variant, per attribute.
     *
     * @param  string[]  $attributeCodes
     */
    private function joinAttributeValues($query, array $attributeCodes): void
    {
        foreach ($attributeCodes as $attrCode) {
            foreach ([
                'product' => 'products.id',
                'variant' => 'variants.id',
            ] as $side => $ownerColumn) {
                $this->joinAttributeValueSide($query, $attrCode, $side, $ownerColumn);
            }
        }
    }

    /**
     * Left join the attribute values of one attribute code for one side (product or variant).
     */
    private function joinAttributeValueSide($query, string $attrCode, string $side, string $ownerColumn): void
    {
        $alias = 'pav_'.$attrCode.'_'.$side;

        $query->leftJoin('product_attribute_values as '.$alias, function ($join) use ($alias, $attrCode, $ownerColumn) {
            $join->on($ownerColumn, '=', $alias.'.product_id')
                ->whereIn($alias.'.attribute_id', function ($subQuery) use ($attrCode) {
                    $subQuery->select('id')->from('attributes')->where('code', $attrCode);
                });
        });
    }

    /**
     * Constraint that requires every attribute filter to match on one side (product or variant).
     */
    private function matchEveryAttribute(array $attributeFilters, string $side, $locale, $channel): \Closure
    {
        return function ($groupQuery) use ($attributeFilters, $side, $locale, $channel) {
            foreach ($attributeFilters as $attrCode => $filterData) {
                $alias = 'pav_'.$attrCode.'_'.$side;
                $attributeType = $this->attributeTypeCache[$attrCode] ?? 'text';

                $groupQuery->where(function ($query) use ($filterData, $locale, $channel, $alias, $attributeType, $attrCode) {
                    $this->applyAttributeFilter($query, $filterData['term'], $filterData['matchType'], $locale, $channel, $alias, $attributeType, $attrCode);
                });
            }
        };
    }

    /**
     * The page size the first or last argument asks for, clamped, or zero for an empty page.
     */
    private function requestedPageSize(array $args): int
    {
        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $requested = $first ?? $last ?? 30;

        return $requested > 0 ? $this->clampPageSize($requested) : 0;
    }

    /**
     * Carry the locale and channel to each product so attribute values resolve in that scope.
     */
    private function propagateContext($items, $locale, $channel): void
    {
        if (
            ! $locale
            && ! $channel
        ) {
            return;
        }

        $items->each(function ($product) use ($locale, $channel) {
            if ($locale) {
                $product->locale = $locale;
            }

            if ($channel) {
                $product->channel = $channel;
            }
        });
    }

    /**
     * Resolve the customer group whose price index the listing reads.
     */
    private function currentCustomerGroup(): object
    {
        return CurrentCustomer::user()?->group ?? core()->getGuestCustomerGroup();
    }

    /**
     * Apply attribute filter logic to a query based on attribute type.
     */
    private function applyAttributeFilter($query, $term, $matchType, $locale, $channel, $alias, $attributeType = null, $attrCode = null)
    {
        if (! $attributeType) {
            $attributeType = 'text';
        }

        $scope = $attrCode ? ($this->attributeScopeCache[$attrCode] ?? null) : null;

        if (
            $locale
            && ($scope->value_per_locale ?? false)
        ) {
            $query->where($alias.'.locale', $locale);
        }

        if (
            $channel
            && ($scope->value_per_channel ?? false)
        ) {
            $query->where($alias.'.channel', $channel);
        }

        if ($matchType === 'PARTIAL') {
            $this->applyPartialFilter($query, $term, $alias, $attributeType);
        } else {
            $this->applyExactFilter($query, $term, $alias, $attributeType);
        }
    }

    /**
     * Get the correct column name based on attribute type.
     */
    private function getColumnForType($attributeType)
    {
        return match ($attributeType) {
            'text', 'textarea' => 'text_value',
            'select','multiselect','dropdown' => 'integer_value',
            'decimal', 'price' => 'float_value',
            'integer' => 'integer_value',
            'boolean' => 'boolean_value',
            'datetime' => 'datetime_value',
            'date' => 'date_value',
            'json' => 'json_value',
            default => 'text_value',
        };
    }

    /**
     * Apply partial (LIKE) filter based on attribute type.
     */
    private function applyPartialFilter($query, $term, $alias, $attributeType)
    {
        $column = $this->getColumnForType($attributeType);
        $query->where($alias.'.'.$column, db_grammar()->caseInsensitiveLike(), "%{$term}%");
    }

    /**
     * Apply an exact match filter, matching nothing when an integer column gets no numeric term.
     */
    private function applyExactFilter($query, $term, $alias, $attributeType)
    {
        $column = $this->getColumnForType($attributeType);

        $values = [$term];

        if (
            is_string($term)
            && str_contains($term, ',')
        ) {
            $values = array_filter(array_map('trim', explode(',', $term)));
        }

        if ($column === 'integer_value') {
            $values = array_filter($values, fn ($value) => is_numeric($value));
        }

        if (count($values) > 1) {
            $query->whereIn($alias.'.'.$column, $this->convertValuesForType(array_values($values), $attributeType));

            return;
        }

        if (! $values) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where($alias.'.'.$column, $this->convertValueForType(reset($values), $attributeType));
    }

    /**
     * Convert a single value based on attribute type.
     */
    private function convertValueForType($value, $attributeType)
    {
        return match ($attributeType) {
            'decimal', 'price' => (float) $value,
            'integer', 'select', 'multiselect', 'dropdown' => (int) $value,
            'boolean' => $value ? '1' : '0',
            default => $value,
        };
    }

    /**
     * Convert multiple values based on attribute type.
     */
    private function convertValuesForType($values, $attributeType)
    {
        return match ($attributeType) {
            'decimal', 'price' => array_map('floatval', $values),
            'integer', 'select', 'multiselect', 'dropdown' => array_map('intval', $values),
            'boolean' => array_map(fn ($value) => (int) filter_var($value, FILTER_VALIDATE_BOOLEAN), $values),
            default => $values,
        };
    }
}
