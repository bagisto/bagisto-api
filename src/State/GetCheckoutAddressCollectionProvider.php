<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\PartialPaginator;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\Options;
use ApiPlatform\Metadata\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Util\StateOptionsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerInterface;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;

/**
 * Custom collection provider for cart addresses with token-based filtering.
 */
class GetCheckoutAddressCollectionProvider implements ProviderInterface
{
    use StateOptionsTrait;

    private $linksHandler;

    private $handleLinksLocator;

    public function __construct(
        private readonly Pagination $pagination,
        ?LinksHandlerInterface $linksHandler = null,
        ?ContainerInterface $handleLinksLocator = null,
    ) {
        $this->linksHandler = $linksHandler;
        $this->handleLinksLocator = $handleLinksLocator;
    }

    /**
     * Provide paginated cart addresses for the given token.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        $args = $context['args'] ?? [];
        $token = $args['token'] ?? null;

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        $resourceClass = $this->getStateOptionsClass($operation, $operation->getClass(), Options::class);
        $model = new $resourceClass;

        if (! $model instanceof Model) {
            throw new RuntimeException(sprintf('The class "%s" is not an Eloquent model.', $resourceClass));
        }

        $query = $model->query()->where('cart_id', $cart->id);

        if ($this->pagination->isEnabled($operation, $context) === false) {
            return $query->get();
        }

        $isPartial = $operation->getPaginationPartial();
        $collection = $query
            ->{$isPartial ? 'simplePaginate' : 'paginate'}(
                perPage: $this->pagination->getLimit($operation, $context),
                page: $this->pagination->getPage($context),
            );

        if ($isPartial) {
            return new PartialPaginator($collection);
        }

        return new Paginator($collection);
    }
}
