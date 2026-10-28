<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Filter\Attribute;
use Webkul\BagistoApi\Models\Product;

class FilterableAttributesProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $isRest = $operation instanceof HttpOperation;
        $args = $isRest ? request()->query() : ($context['args'] ?? []);

        $categoryId = $this->resolveCategoryId($args);

        $query = $this->attributeQuery($categoryId);

        [$minPrice, $maxPrice] = $this->priceBounds($categoryId);

        if ($isRest) {
            return $this->withPrices($query->get(), $minPrice, $maxPrice)->all();
        }

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? 30;
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

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $this->withPrices($query->offset($offset)->limit($perPage)->get(), $minPrice, $maxPrice);

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

    /**
     * Resolve the category from `categoryId` / `category_id` (404 when unknown) or from `categorySlug`.
     */
    private function resolveCategoryId(array $args): ?int
    {
        $categoryId = $args['categoryId'] ?? $args['category_id'] ?? null;

        if ($categoryId !== null && $categoryId !== '') {
            if (! DB::table('categories')->where('id', (int) $categoryId)->exists()) {
                throw new ResourceNotFoundException(__('bagistoapi::app.graphql.category.not-found'));
            }

            return (int) $categoryId;
        }

        $categorySlug = $args['categorySlug'] ?? $args['category_slug'] ?? null;

        if (! $categorySlug) {
            return null;
        }

        $id = DB::table('category_translations')->where('slug', $categorySlug)->value('category_id');

        return $id ? (int) $id : null;
    }

    /**
     * The category's own filterable attributes, or the global filterable set when no category is given.
     */
    private function attributeQuery(?int $categoryId)
    {
        $query = Attribute::query()
            ->select('attributes.*')
            ->with(['options', 'translations', 'options.translations'])
            ->orderBy('attributes.id', 'asc');

        if ($categoryId) {
            return $query
                ->join('category_filterable_attributes as cfa', 'cfa.attribute_id', '=', 'attributes.id')
                ->where('cfa.category_id', $categoryId);
        }

        return $query->where('is_filterable', 1);
    }

    /**
     * Min and max product price for the customer's group, converted to the requested currency.
     */
    private function priceBounds(?int $categoryId): array
    {
        $customer = Auth::guard('sanctum')->user();

        $customerGroup = ($customer && $customer->group)
            ? $customer->group
            : core()->getGuestCustomerGroup();

        $priceQuery = Product::query()
            ->leftJoin('product_price_indices', 'products.id', 'product_price_indices.product_id')
            ->leftJoin('product_categories', 'products.id', 'product_categories.product_id')
            ->where('product_price_indices.customer_group_id', $customerGroup->id);

        if ($categoryId) {
            $priceQuery->where('product_categories.category_id', $categoryId);
        }

        return [
            (float) core()->convertPrice((float) ((clone $priceQuery)->min('min_price') ?? 0)),
            (float) core()->convertPrice((float) ((clone $priceQuery)->max('min_price') ?? 0)),
        ];
    }

    private function withPrices($items, float $minPrice, float $maxPrice)
    {
        return $items->map(function ($item) use ($maxPrice, $minPrice) {
            $item->maxPrice = $maxPrice;
            $item->minPrice = $minPrice;

            return $item;
        });
    }
}
