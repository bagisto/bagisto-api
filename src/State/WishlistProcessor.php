<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request as RequestFacade;
use Webkul\BagistoApi\Dto\CreateWishlistInput;
use Webkul\BagistoApi\Dto\DeleteWishlistInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\Wishlist;

/**
 * WishlistProcessor - Handles create/delete operations for wishlist items
 */
class WishlistProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private ?Request $request = null
    ) {}

    /**
     * Process wishlist item operations
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $operationName = $operation->getName();

        if (in_array($operationName, ['toggle']) && $data instanceof CreateWishlistInput) {
            $this->hydrateCreateInputFromContext($data, $context);

            return $this->handleToggle($data, $uriVariables, $context);
        }

        if ($data instanceof CreateWishlistInput) {
            $this->hydrateCreateInputFromContext($data, $context);

            return $this->handleCreate($data, $context);
        }

        /** Handle REST POST — model received instead of DTO */
        if ($data instanceof Wishlist && $operation instanceof \ApiPlatform\Metadata\Post) {
            $input = new CreateWishlistInput;
            $input->product_id = request()->input('product_id') ?? request()->input('productId');

            return $this->handleCreate($input, $context);
        }

        if ($data instanceof DeleteWishlistInput) {
            $this->hydrateDeleteInputFromContext($data, $context);

            return $this->handleDeleteFromInput($data, $context);
        }

        if ($operation instanceof \ApiPlatform\Metadata\Delete || in_array($operationName, ['delete', 'destroy'])) {
            return $this->handleDelete($data, $uriVariables, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Handle create operation for wishlist items
     */
    private function handleCreate(CreateWishlistInput $input, array $context = []): Wishlist
    {
        if (empty($input->product_id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.product-id-required'));
        }

        $product = Product::find($input->product_id);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.wishlist.product-not-found'));
        }

        if (! $product->status) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.product-disabled'));
        }

        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $customerId = $user->id;
        $channelId = core()->getCurrentChannel()->id;

        $existingItem = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $input->product_id)
            ->where('channel_id', $channelId)
            ->first();

        if ($existingItem) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.already-exists'));
        }

        Event::dispatch('customer.wishlist.create.before', $input->product_id);

        $wishlistItem = Wishlist::create([
            'product_id'  => $input->product_id,
            'customer_id' => $customerId,
            'channel_id'  => $channelId,
        ]);

        Event::dispatch('customer.wishlist.create.after', $wishlistItem);

        return $wishlistItem;
    }

    private function handleToggle(CreateWishlistInput $input, array $context = []): Wishlist
    {
        if (empty($input->product_id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.product-id-required'));
        }

        $product = Product::find($input->product_id);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.wishlist.product-not-found'));
        }

        if (! $product->status) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.product-disabled'));
        }

        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $customerId = $user->id;
        $channelId = core()->getCurrentChannel()->id;

        $existingItem = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $input->product_id)
            ->where('channel_id', $channelId)
            ->first();

        if ($existingItem) {
            $existingItem->delete();

            Event::dispatch('customer.wishlist.delete.after', $existingItem);

            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.removed'));
        }

        Event::dispatch('customer.wishlist.create.before', $input->product_id);

        $wishlistItem = Wishlist::create([
            'product_id'  => $input->product_id,
            'customer_id' => $customerId,
            'channel_id'  => $channelId,
        ]);

        Event::dispatch('customer.wishlist.create.after', $wishlistItem);

        return $wishlistItem;
    }

    private function hydrateCreateInputFromContext(CreateWishlistInput $input, array $context): void
    {
        if (! empty($input->product_id)) {
            return;
        }

        $productId = $this->extractProductId($context['args']['input'] ?? $context['args'] ?? null);

        if ($productId === null) {
            $request = $this->request ?? RequestFacade::instance();

            if ($request) {
                $productId = $this->extractProductId($request->input('variables.input'))
                    ?? $this->extractProductId($request->input('input'))
                    ?? $this->extractProductId($request->input('extensions.variables.input'));
            }
        }

        if ($productId !== null) {
            $input->product_id = $productId;
        }
    }

    private function hydrateDeleteInputFromContext(DeleteWishlistInput $input, array $context): void
    {
        if (! empty($input->id)) {
            return;
        }

        $id = $this->extractId($context['args']['input'] ?? $context['args'] ?? null);

        if ($id === null) {
            $request = $this->request ?? RequestFacade::instance();

            if ($request) {
                $id = $this->extractId($request->input('variables.input'))
                    ?? $this->extractId($request->input('input'))
                    ?? $this->extractId($request->input('extensions.variables.input'));
            }
        }

        if ($id !== null) {
            $input->id = $id;
        }
    }

    private function extractProductId(mixed $input): ?int
    {
        if (is_array($input)) {
            $value = $input['product_id'] ?? $input['productId'] ?? null;

            return is_numeric($value) ? (int) $value : null;
        }

        if (is_object($input)) {
            $value = $input->product_id ?? $input->productId ?? null;

            return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }

    private function extractId(mixed $input): ?string
    {
        if (is_array($input)) {
            $value = $input['id'] ?? null;

            return $value !== null && $value !== '' ? (string) $value : null;
        }

        if (is_object($input)) {
            $value = $input->id ?? null;

            return $value !== null && $value !== '' ? (string) $value : null;
        }

        return null;
    }

    /**
     * Handle delete operation from GraphQL mutation input
     */
    private function handleDeleteFromInput(DeleteWishlistInput $input, array $context): Wishlist
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (empty($input->id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.id-required'));
        }

        // Extract the numeric ID from the URI (format: /api/shop/wishlists/123)
        $wishlistItemId = basename($input->id);

        $wishlistItem = Wishlist::find($wishlistItemId);

        if (! $wishlistItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.wishlist.not-found'));
        }

        if ($wishlistItem->customer_id !== $user->id) {
            throw new AuthorizationException(__('bagistoapi::app.auth.cannot-update-other-profile'));
        }

        Event::dispatch('customer.wishlist.delete.before', $wishlistItemId);

        $wishlistItem->delete();

        Event::dispatch('customer.wishlist.delete.after', $wishlistItemId);

        return $wishlistItem;
    }

    /**
     * Handle delete operation for wishlist items with authorization
     */
    private function handleDelete(mixed $data, array $uriVariables, array $context): null
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $wishlistItemId = $uriVariables['id'] ?? null;

        if (! $wishlistItemId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.wishlist.id-required'));
        }

        $wishlistItem = Wishlist::find($wishlistItemId);

        if (! $wishlistItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.wishlist.not-found'));
        }

        if ($wishlistItem->customer_id !== $user->id) {
            throw new AuthorizationException(__('bagistoapi::app.auth.cannot-update-other-profile'));
        }

        Event::dispatch('customer.wishlist.delete.before', $wishlistItemId);

        $wishlistItem->delete();

        Event::dispatch('customer.wishlist.delete.after', $wishlistItemId);

        return null;
    }
}
