<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Filter\Attribute;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\State\Concerns\ClampsPageSize;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\Support\CurrentCustomer;
use Webkul\Category\Repositories\CategoryRepository;

class FilterableAttributesProvider implements ProviderInterface
{
    use ClampsPageSize;
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    /**
     * Provide the filterable attributes, as a plain list over REST and a cursor page over GraphQL.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $isRest = $operation instanceof HttpOperation;

        $args = $isRest ? request()->query() : ($context['args'] ?? []);

        $categoryId = $this->resolveCategoryId($args);

        [$minPrice, $maxPrice] = $this->priceBounds($categoryId);

        $query = $this->attributeQuery($categoryId);

        if ($isRest) {
            return $this->withPrices($query->get(), $minPrice, $maxPrice)->all();
        }

        $requested = $this->requestedCursorLimit($args, 30);

        $limit = $requested > 0 ? $this->clampPageSize($requested) : 0;

        $page = $this->paginateCursorWindow($query, $args, $limit)
            ->through(fn (Attribute $item) => $this->withPrice($item, $minPrice, $maxPrice));

        return new Paginator($page);
    }

    /**
     * The category from `categoryId` / `category_id`, refused when unknown, else from `categorySlug` / `category_slug`.
     */
    private function resolveCategoryId(array $args): ?int
    {
        $categoryId = $args['categoryId'] ?? $args['category_id'] ?? null;

        if (
            $categoryId !== null
            && $categoryId !== ''
        ) {
            if (! $this->categoryRepository->find((int) $categoryId)) {
                throw new ResourceNotFoundException(__('bagistoapi::app.graphql.category.not-found'));
            }

            return (int) $categoryId;
        }

        $categorySlug = $args['categorySlug'] ?? $args['category_slug'] ?? null;

        if (! is_string($categorySlug) || $categorySlug === '') {
            return null;
        }

        return $this->categoryRepository->findBySlug($categorySlug)?->id;
    }

    /**
     * The category's own filterable attributes, or the store-wide filterable set when no category is given.
     */
    private function attributeQuery(?int $categoryId): Builder
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
     * The lowest and highest product price for the customer's group, in the requested currency.
     *
     * @return array{0: float, 1: float}
     */
    private function priceBounds(?int $categoryId): array
    {
        $customer = CurrentCustomer::user();

        $customerGroup = $customer?->group ?: core()->getGuestCustomerGroup();

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

    /**
     * Attach the price range to every attribute of the list.
     */
    private function withPrices(Collection $items, float $minPrice, float $maxPrice): Collection
    {
        return $items->map(fn (Attribute $item) => $this->withPrice($item, $minPrice, $maxPrice));
    }

    /**
     * Attach the price range to one attribute.
     */
    private function withPrice(Attribute $item, float $minPrice, float $maxPrice): Attribute
    {
        $item->minPrice = $minPrice;
        $item->maxPrice = $maxPrice;

        return $item;
    }
}
