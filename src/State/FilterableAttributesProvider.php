<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $offset = 0;

        if ($after) {
            $decoded = base64_decode($after, true);
            $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before) {
            $decoded = base64_decode($before, true);
            $cursor = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset = max(0, $cursor - $perPage);
        }

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

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $query
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $items = $items->map(function ($item) use ($maxPrice) {
            $item->maxPrice = (float) $maxPrice;
            $item->minPrice = 0.0;

            return $item;
        });

        $currentPage = $total > 0 ? (int) floor($offset / $perPage) + 1 : 1;

        return new Paginator(
            new LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }
}
