<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Dto\DeleteAllWishlistsInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\DeleteAllWishlists;
use Webkul\BagistoApi\Models\Wishlist;

/**
 * DeleteAllWishlistsProcessor - Deletes all wishlist items for the authenticated customer
 */
class DeleteAllWishlistsProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process delete all wishlists operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof DeleteAllWishlistsInput) {
            return $this->handleDeleteAll();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Delete all wishlist items for the authenticated customer
     */
    private function handleDeleteAll(): DeleteAllWishlists
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        Event::dispatch('customer.wishlist.delete-all.before', $user->id);

        $deletedCount = Wishlist::where('customer_id', $user->id)->count();

        Wishlist::where('customer_id', $user->id)->delete();

        Event::dispatch('customer.wishlist.delete-all.after', $user->id);

        return new DeleteAllWishlists(
            __('bagistoapi::app.graphql.wishlist.delete-all-success'),
            $deletedCount,
            $user->id
        );
    }
}
