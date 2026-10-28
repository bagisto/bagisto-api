<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\GraphQl\Operation as GraphQlOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\ArrayPaginator;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\ProductDetail\ProductSummaryDto;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\Product\Repositories\ProductRepository;

class CartCrossSellProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected Pagination $pagination,
    ) {}

    /**
     * Provide configured cross-sell products for the current cart, as a cursor page over GraphQL.
     *
     * @return ProductSummaryDto[]|ArrayPaginator
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ArrayPaginator
    {
        $rows = $this->crossSells();

        if (! $operation instanceof GraphQlOperation) {
            return $rows;
        }

        $context['count'] = count($rows);

        return new ArrayPaginator(
            $rows,
            $this->pagination->getOffset($operation, $context),
            $this->pagination->getLimit($operation, $context),
        );
    }

    /**
     * The cross-sell product summaries of the bearer cart, leaving out products already in it.
     *
     * @return ProductSummaryDto[]
     */
    protected function crossSells(): array
    {
        $request = Request::instance();
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        $productIds = $cart->items->pluck('product_id')->all();

        if (! $productIds) {
            return [];
        }

        $crossSellIds = $this->productRepository
            ->with('cross_sells')
            ->findWhereIn('id', $productIds)
            ->flatMap(fn ($product) => $product->cross_sells->pluck('id'))
            ->diff($productIds)
            ->unique()
            ->values()
            ->all();

        return $this->productRepository
            ->whereIn('products.id', $crossSellIds)
            ->take(core()->getConfigData('catalog.products.cart_view_page.no_of_cross_sells_products'))
            ->get()
            ->map(fn ($product) => new ProductSummaryDto(
                id: (int) $product->id,
                sku: $product->sku,
                name: $product->name,
                price: $product->price !== null ? (float) $product->price : null,
                formatted_price: $product->formatted_price ?? null,
                base_image_url: $product->getBaseImageUrlAttribute(),
            ))
            ->values()
            ->all();
    }
}
