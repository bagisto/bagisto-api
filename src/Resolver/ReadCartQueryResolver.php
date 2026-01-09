<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\Checkout\Repositories\CartRepository;

/**
 * ReadCartQueryResolver - Resolves the readCart query
 */
class ReadCartQueryResolver implements QueryItemResolverInterface
{
    public function __construct(
        protected CartRepository $cartRepository,
    ) {}

    public function __invoke(?CartData $item, array $context): ?CartData
    {
        $args = $context['args'] ?? [];
        $cartId = $args['cartId'] ?? null;
        $token = $args['token'] ?? null;

        if ($cartId) {
            $cart = $this->cartRepository->findById($cartId);
            if ($cart) {
                return CartData::fromModel($cart);
            }
        }

        if ($token) {
            $cart = $this->cartRepository->findWhere(['cart_token' => $token])->first();
            if ($cart) {
                return CartData::fromModel($cart);
            }
        }

        return null;
    }
}
