<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\MoveWishlistToCartInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Wishlist;
use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

/**
 * MoveWishlistToCartProcessor - Handles moving wishlist items to cart
 */
class MoveWishlistToCartProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process move to cart operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof MoveWishlistToCartInput) {
            /**
             * REST fallback: the serializer's name converter may not populate camelCase DTO
             * properties from snake_case JSON keys. Populate from request if needed.
             */
            if ($data->wishlistItemId === null) {
                $data->wishlistItemId = (int) (request()->input('wishlist_item_id') ?? request()->input('wishlistItemId'));
                $data->quantity = (int) (request()->input('quantity') ?? 1);
            }

            return $this->handleMoveToCart($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Handle move to cart operation for wishlist items
     */
    private function handleMoveToCart(MoveWishlistToCartInput $input): \Webkul\BagistoApi\Models\MoveWishlistToCart
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if ($input->quantity < 1) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.invalid-quantity'));
        }

        $wishlistItem = Wishlist::where('id', $input->wishlistItemId)
            ->where('customer_id', $user->id)
            ->first();

        if (! $wishlistItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.wishlist.not-found'));
        }

        try {
            Event::dispatch('customer.wishlist.move-to-cart.before', $input->wishlistItemId);

            $result = Cart::moveToCart($wishlistItem, $input->quantity);

            Event::dispatch('customer.wishlist.move-to-cart.after', $input->wishlistItemId);

            if (! $result) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.move-to-cart-missing-options'));
            }

            return new \Webkul\BagistoApi\Models\MoveWishlistToCart(
                __('bagistoapi::app.graphql.wishlist.moved-to-cart-success'),
                $input->wishlistItemId
            );
        } catch (\Exception $exception) {
            throw new InvalidInputException($exception->getMessage());
        }
    }
}
