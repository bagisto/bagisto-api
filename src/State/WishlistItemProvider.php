<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\Wishlist;
use Webkul\BagistoApi\Support\CurrentCustomer;

class WishlistItemProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $id = $uriVariables['id'] ?? null;

        if (
            is_string($id)
            && str_contains($id, '/')
        ) {
            $id = basename($id);
        }

        if (! is_numeric($id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.not-found'), 404);
        }

        $wishlist = Wishlist::where('id', (int) $id)
            ->where('customer_id', $customer->id)
            ->where('channel_id', core()->getCurrentChannel()->id)
            ->with(['product' => fn ($query) => Product::withReviewSummary($query), 'customer', 'channel'])
            ->first();

        if (! $wishlist) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.not-found'), 404);
        }

        return $wishlist;
    }
}
