<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Models\Filter\Attribute;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class FilterableAttributesProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

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
        $source = $context['source'] ?? null;
        $args = $context['args'] ?? [];
        $info = $context['info'] ?? null;

        $categorySlug = $args['categorySlug'] ?? null;

        $requested = $this->requestedCursorLimit($args, 30);
        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

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

        $maxPrice = (clone $priceQuery)->max('min_price') ?? 0;
        $minPrice = (clone $priceQuery)->min('min_price') ?? 0;

        $page = $this->paginateCursorWindow($query, $args, $limit)->through(function ($item) use ($maxPrice, $minPrice) {
            $item->maxPrice = (float) $maxPrice;
            $item->minPrice = (float) $minPrice;

            return $item;
        });

        return new Paginator($page);
    }
}
