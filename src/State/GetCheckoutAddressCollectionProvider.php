<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use ApiPlatform\Laravel\Eloquent\State\Options;
use ApiPlatform\Metadata\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\Util\StateOptionsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use Psr\Container\ContainerInterface;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;

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

        $request = Request::instance() ?? ($context['request'] ?? null);

        // Extract Bearer token from Authorization header
        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

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

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $perPage = $first ?? $last ?? 10;
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

        $query->orderBy('id', 'asc');

        $total = (clone $query)->count();

        if ($offset > $total) {
            $offset = max(0, $total - $perPage);
        }

        $items = $query
            ->offset($offset)
            ->limit($perPage)
            ->get();

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
