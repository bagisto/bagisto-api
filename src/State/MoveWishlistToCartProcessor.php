<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\MoveWishlistToCartInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Models\Wishlist;
use Webkul\Checkout\Facades\Cart;

class MoveWishlistToCartProcessor implements ProcessorInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process move to cart operation.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof MoveWishlistToCartInput) {
            if ($data->wishlistItemId === null) {
                $this->hydrateInputFromContext($data, $context);
            }

            if ($data->wishlistItemId === null) {
                $data->wishlistItemId = (int) (request()->input('wishlist_item_id') ?? request()->input('wishlistItemId'));
                $data->quantity = (int) (request()->input('quantity') ?? 1);
            }

            return $this->handleMoveToCart($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Hydrate MoveWishlistToCartInput from GraphQL context args.
     * Mirrors the pattern used in WishlistProcessor::hydrateCreateInputFromContext().
     */
    private function hydrateInputFromContext(MoveWishlistToCartInput $data, array $context): void
    {
        $args = $context['args']['input'] ?? $context['args'] ?? null;

        if (is_array($args)) {
            $id = $args['wishlistItemId'] ?? $args['wishlist_item_id'] ?? null;
            if (is_numeric($id)) {
                $data->wishlistItemId = (int) $id;
            }

            $qty = $args['quantity'] ?? null;
            if (is_numeric($qty)) {
                $data->quantity = (int) $qty;
            }

            return;
        }

        $input = request()->input('variables.input');
        if (is_array($input)) {
            $id = $input['wishlistItemId'] ?? $input['wishlist_item_id'] ?? null;
            if (is_numeric($id)) {
                $data->wishlistItemId = (int) $id;
            }

            $qty = $input['quantity'] ?? null;
            if (is_numeric($qty)) {
                $data->quantity = (int) $qty;
            }
        }
    }

    /**
     * Handle move to cart operation for wishlist items
     * Returns cart data similar to handleAddProduct in CartTokenProcessor.
     */
    private function handleMoveToCart(MoveWishlistToCartInput $input): array
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if ($input->quantity < 1) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.invalid-quantity'));
        }

        $wishlistItem = Wishlist::with('product')
            ->where('id', $input->wishlistItemId)
            ->where('customer_id', $user->id)
            ->first();

        if (! $wishlistItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.wishlist.not-found'));
        }

        try {
            $cartRepository = app('Webkul\Checkout\Repositories\CartRepository');
            $cart = $cartRepository->findOneWhere([
                'customer_id' => $user->id,
                'is_active' => 1,
            ]);

            if (! $cart) {
                $cart = CartTokenFacade::createCart($user);
            }

            Cart::setCart($cart);

            Event::dispatch('customer.wishlist.move-to-cart.before', $input->wishlistItemId);

            $result = Cart::moveToCart($wishlistItem, $input->quantity);

            Event::dispatch('customer.wishlist.move-to-cart.after', $input->wishlistItemId);

            if (! $result) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.move-to-cart-missing-options'));
            }

            Cart::collectTotals();

            $updatedCart = Cart::getCart();

            if (! $updatedCart) {
                throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
            }

            $responseData = CartData::fromModel($updatedCart);
            $responseData->success = true;
            $responseData->message = __('bagistoapi::app.graphql.wishlist.moved-to-cart-success');

            return (array) $responseData;
        } catch (\Exception $exception) {
            throw new InvalidInputException($exception->getMessage());
        }
    }
}
