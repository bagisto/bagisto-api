<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\Wishlist;
use Webkul\BagistoApi\Service\GenericIdNormalizer;

class WishlistQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): object
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $id = $context['args']['id'] ?? null;

        if ($id === null || $id === '') {
            throw new BadRequestHttpException(__('bagistoapi::app.graphql.wishlist.id-required'));
        }

        $numericId = GenericIdNormalizer::extractNumericId($id);

        if ($numericId === null) {
            throw new BadRequestHttpException(__('bagistoapi::app.graphql.wishlist.not-found'));
        }

        $wishlist = Wishlist::where('id', (int) $numericId)
            ->where('customer_id', $customer->id)
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->with(['product', 'customer', 'channel'])
            ->first();

        if (! $wishlist) {
            throw new BadRequestHttpException(__('bagistoapi::app.graphql.wishlist.not-found'));
        }

        return $wishlist;
    }
}
