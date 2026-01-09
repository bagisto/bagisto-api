<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Models\Filter\Attribute;
use Webkul\BagistoApi\Models\Product;

class FilterableAttributesProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $source = $context['source'] ?? null;
        $args = $context['args'] ?? [];
        $info = $context['info'] ?? null;

        $categorySlug = $args['categorySlug'] ?? null;

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $defaultPerPage = 30;
        $perPage = $first ?? $last ?? $defaultPerPage;

        $afterId = $after ? (int) base64_decode($after) : null;
        $beforeId = $before ? (int) base64_decode($before) : null;

        $query = Attribute::query();

        $query->select('attributes.*');

        $categoryId = $categorySlug
            ? DB::table('category_translations')->where('slug', $categorySlug)->select('category_id')->pluck('category_id')->first()
            : null;

        if ($categoryId) {
            $query
                ->leftJoin('category_filterable_attributes as cfa', 'cfa.attribute_id', '=', 'attributes.id')
                ->where('cfa.category_id', $categoryId);
        } else {
            $query->where('is_filterable', 1);
        }

        if ($after) {
            $query->where('attributes.id', '>', $afterId);
        }
        if ($before) {
            $query->where('attributes.id', '<', $beforeId);
        }

        $query->with(['options', 'translations', 'options.translations']);
        $query->orderBy('attributes.id', 'asc');

        // TODO: change to use customer group from active customer when auth is implemented
        $customerGroup = core()->getGuestCustomerGroup();

        $maxPriceQuery = Product::query()
            ->leftJoin('product_price_indices', 'products.id', 'product_price_indices.product_id')
            ->leftJoin('product_categories', 'products.id', 'product_categories.product_id')
            ->where('product_price_indices.customer_group_id', $customerGroup->id);

        if ($categoryId) {
            $maxPriceQuery->where('product_categories.category_id', $categoryId);
        }

        $maxPrice = $maxPriceQuery->max('min_price') ?? 0;

        if ($last !== null) {
            $reverse = Attribute::query();

            if ($categorySlug) {
                $reverse
                    ->leftJoin('category_filterable_attributes as cfa', 'cfa.attribute_id', '=', 'attributes.id')
                    ->where('cfa.category_id', $categoryId);
            } else {
                $reverse->where('is_filterable', 1);
            }

            if ($before) {
                $reverse->where('attributes.id', '<', $beforeId);
            }

            $reverse->with(['options', 'translations', 'options.translations']);
            $reverse->orderBy('attributes.id', 'desc');

            $totalCount = $reverse->count();

            $items = $reverse->take($last)->get()->reverse()->values();

            $items = $items->map(function ($item) use ($maxPrice) {
                $item->maxPrice = (float) $maxPrice;
                $item->minPrice = 0.0;

                return $this->applyPriceValues($item);
            });

            $laravelPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $totalCount,
                $last,
                1,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );

            return new Paginator($laravelPaginator);
        }

        $laravelPaginator = $first !== null ? $query->paginate($first) : $query->paginate($defaultPerPage);

        $laravelPaginator->through(function ($item) use ($maxPrice) {
            $item->maxPrice = (float) $maxPrice;
            $item->minPrice = 0.0;

            return $item;
        });

        return new Paginator($laravelPaginator);
    }
}
