<?php

namespace Webkul\BagistoApi\State\Concerns;

use ApiPlatform\Metadata\Post;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BookingProduct\Helpers\Booking;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductBundleOptionProductRepository;
use Webkul\Product\Repositories\ProductBundleOptionRepository;

trait HandlesCartItems
{
    /**
     * Handle adding a product to cart
     */
    private function handleAddProduct(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $data->productId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.product-id-required'));
        }

        /**
         * Quantity is required by most product types, but for some types (e.g. booking appointment/event)
         * the storefront does not allow changing quantity. Default to 1 when omitted.
         */
        if ($data->quantity === null) {
            $data->quantity = 1;
        }

        if ($data->quantity < 1) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.invalid-quantity'));
        }

        $product = Product::find($data->productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.product-not-found'));
        }

        if (! $product->status) {
            throw new InvalidInputException(__('shop::app.checkout.cart.inactive-add'));
        }

        $groupedQty = $this->normalizeJsonFieldToArray($data->groupedQty, 'groupedQty')
            ?? (is_array($data->qty) ? $data->qty : null);

        if ($product->type === 'grouped' && ! is_array($groupedQty)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.grouped-qty-required'));
        }

        if ($product->type === 'grouped') {
            $product->loadMissing(
                'grouped_products',
                'grouped_products.associated_product'
            );

            $associatedProductIds = $product->grouped_products
                ->pluck('associated_product_id')
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all();

            $providedKeys = array_map(
                static fn ($key) => (string) $key,
                array_keys($groupedQty ?? [])
            );

            $missingIds = array_values(array_diff($associatedProductIds, $providedKeys));
            if (! empty($missingIds)) {
                throw new InvalidInputException(
                    __('bagistoapi::app.graphql.cart.grouped-qty-must-include-all', [
                        'ids' => implode(', ', $missingIds),
                    ])
                );
            }

            $unexpectedIds = array_values(array_diff($providedKeys, $associatedProductIds));
            if (! empty($unexpectedIds)) {
                throw new InvalidInputException(
                    __('bagistoapi::app.graphql.cart.grouped-qty-invalid-associated', [
                        'ids' => implode(', ', $unexpectedIds),
                    ])
                );
            }

            foreach (($groupedQty ?? []) as $associatedId => $qty) {
                if ($qty === null || $qty === '') {
                    continue;
                }

                if (! is_numeric($qty) || (int) $qty < 0) {
                    throw new InvalidInputException(
                        __('bagistoapi::app.graphql.cart.grouped-qty-invalid-quantity', [
                            'id' => (string) $associatedId,
                        ])
                    );
                }
            }
        }

        $redirectUri = null;
        $guestCartTokenDetail = null;

        $usedTokens = $this->resolveCustomizableOptions($product, $customer, $data);

        try {
            if (! empty($data->isBuyNow)) {
                if ($cart) {
                    CartFacade::setCart($cart);

                    CartFacade::deActivateCart();

                    $cart = null;
                }

                $redirectUri = route('shop.checkout.onepage.index');
            }

            if (! $cart) {
                $cart = CartTokenFacade::createCart($customer);

                if (! $customer) {
                    $guestCartTokenDetail = $this->guestCartTokensRepository->createToken($cart->id);
                }
            }

            Event::dispatch('cart.before.add', ['cartItem' => null]);

            CartFacade::setCart($cart);

            $bundleOptions = $this->normalizeJsonFieldToArray($data->bundleOptions, 'bundleOptions');
            $bundleOptionQty = $this->normalizeJsonFieldToArray($data->bundleOptionQty, 'bundleOptionQty');
            $booking = $this->normalizeJsonFieldToArray($data->booking, 'booking');

            // For bundle products, enforce admin-defined quantities.
            // Only allow user-provided qty where is_user_defined is true on the bundle option product.
            if ($product->type === 'bundle' && is_array($bundleOptions) && is_array($bundleOptionQty)) {
                $bundleOptionQty = $this->sanitizeBundleOptionQty($bundleOptions, $bundleOptionQty);
            }

            $cartData = [
                'quantity' => $data->quantity,
                'product_id' => $product->id,
                'is_buy_now' => $data->isBuyNow ?? 0,
                ...(is_array($data->options) ? $data->options : []),
                ...(is_array($bundleOptions) ? ['bundle_options' => $bundleOptions] : []),
                ...(is_array($bundleOptionQty) ? ['bundle_option_qty' => $bundleOptionQty] : []),
                ...(isset($data->selectedConfigurableOption) ? ['selected_configurable_option' => $data->selectedConfigurableOption] : []),
                ...(is_array($data->superAttribute) ? ['super_attribute' => $data->superAttribute] : []),
                ...(is_array($groupedQty) ? ['qty' => $groupedQty] : []),
                ...(is_array($data->links) ? ['links' => $data->links] : []),
                ...(is_array($data->customizableOptions) ? ['customizable_options' => $data->customizableOptions] : []),
                ...(is_array($data->additional) ? $data->additional : []),
            ];

            if (is_array($booking)) {
                $note = null;

                if (is_string($data->specialNote) && trim($data->specialNote) !== '') {
                    $note = $data->specialNote;
                } elseif (is_string($data->bookingNote) && trim($data->bookingNote) !== '') {
                    $note = $data->bookingNote;
                }

                if ($note !== null) {
                    $booking['note'] = $note;
                }

                if ($product->type === 'booking') {
                    $booking = $this->normalizeBookingForCart($booking, (int) $product->id);
                }

                $cartData['booking'] = $booking;
            }

            CartFacade::addProduct($product, $cartData);

            CartFacade::collectTotals();

            $updatedCart = CartFacade::getCart();

            Event::dispatch('cart.after.add', ['cart' => $updatedCart]);
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }

        if (! $updatedCart) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.cart.add-product-failed'));
        }

        foreach ($usedTokens as $usedToken => $stagedPath) {
            $this->optionFileStaging->forget($usedToken);
            $this->optionFileStaging->deleteStaged($stagedPath);
        }

        $responseData = CartData::fromModel($updatedCart);

        $responseData->success = true;
        $responseData->cartToken = $guestCartTokenDetail?->token ?? $responseData->cartToken;

        $responseData->message = __('bagistoapi::app.graphql.cart.product-added-successfully');

        // Add redirect URI for buy now
        if ($redirectUri) {
            $responseData->redirectUri = $redirectUri;
        }

        return (array) $responseData;
    }

    /**
     * Enforce required customizable options and resolve file-option upload tokens
     * into UploadedFile instances that core stores at carts/{cartId}.
     * Returns a map of used token => staged path for post-add cleanup.
     */
    private function resolveCustomizableOptions(Product $product, ?Customer $customer, CartInput $data): array
    {
        $usedTokens = [];

        $options = $product->customizable_options;

        if ($options->isEmpty()) {
            return $usedTokens;
        }

        $submitted = is_array($data->customizableOptions) ? $data->customizableOptions : [];

        foreach ($options as $option) {
            $value = $submitted[$option->id] ?? $submitted[(string) $option->id] ?? null;

            $isEmpty = empty($value)
                || (is_array($value) && count(array_filter($value, fn ($value) => $value !== '' && $value !== null)) === 0);

            if ((int) $option->is_required === 1 && $isEmpty) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.cart.customizable-option-required', [
                    'label' => $this->customizableOptionLabel($option),
                ]));
            }
        }

        if (empty($submitted)) {
            return $usedTokens;
        }

        $byId = $options->keyBy('id');
        $owner = $this->currentOwnerId($customer);

        foreach ($submitted as $optionId => $values) {
            $option = $byId->get((int) $optionId);

            if (! $option || $option->type !== 'file') {
                continue;
            }

            $resolved = [];

            foreach ((array) $values as $tokenValue) {
                $payload = $this->optionFileStaging->resolve((string) $tokenValue);

                if (! $payload) {
                    throw new InvalidInputException(__('bagistoapi::app.graphql.cart.file-token-invalid'));
                }

                if (
                    (string) $payload['owner_id'] !== (string) $owner
                    || (int) $payload['option_id'] !== (int) $optionId
                    || (int) $payload['product_id'] !== (int) $product->id
                ) {
                    throw new AuthorizationException(__('bagistoapi::app.graphql.cart.file-token-forbidden'));
                }

                $resolved[] = $this->optionFileStaging->stagedUploadedFile($payload);

                $usedTokens[(string) $tokenValue] = $payload['path'];
            }

            $submitted[$optionId] = $resolved;
        }

        $data->customizableOptions = $submitted;

        return $usedTokens;
    }

    private function currentOwnerId(?Customer $customer): string
    {
        if ($customer) {
            return 'customer:'.$customer->id;
        }

        return 'cart:'.(string) request()->bearerToken();
    }

    private function customizableOptionLabel($option): string
    {
        try {
            $label = optional($option->translate(app()->getLocale()))->label;

            if (! $label) {
                $label = $option->translations->pluck('label')->filter()->first();
            }

            return $label ?: (string) $option->id;
        } catch (\Throwable $exception) {
            return (string) $option->id;
        }
    }

    /**
     * Accept both array inputs (REST) and JSON-string inputs (GraphQL-friendly) and normalize to array.
     */
    private function normalizeJsonFieldToArray(mixed $value, string $fieldName): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            throw new InvalidInputException(sprintf('Invalid "%s" value. Expected JSON string or array.', $fieldName));
        }

        try {
            $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new InvalidInputException(sprintf('Invalid "%s" JSON string.', $fieldName));
        }

        if (! is_array($decoded)) {
            throw new InvalidInputException(sprintf('Invalid "%s" JSON string. Expected an object/array.', $fieldName));
        }

        return $decoded;
    }

    /**
     * Validate and sanitize bundle option quantities.
     *
     * Checkbox/Multiselect options: qty is fixed by admin — throws error if customer tries to change
     * Radio/Select options: qty can be changed by customer
     *
     * @param  array  $bundleOptions  [optionId => [optionProductId, ...]]
     * @param  array  $bundleOptionQty  [optionId => qty]
     * @return array Validated bundle option quantities
     *
     * @throws InvalidInputException
     */
    private function sanitizeBundleOptionQty(array $bundleOptions, array $bundleOptionQty): array
    {
        $optionRepo = app(ProductBundleOptionRepository::class);
        $optionProductRepo = app(ProductBundleOptionProductRepository::class);

        $sanitized = [];

        foreach ($bundleOptions as $optionId => $optionProductIds) {
            $bundleOption = is_array($optionProductIds) ? $optionRepo->find($optionId) : null;

            if (! $bundleOption) {
                continue;
            }

            foreach ($optionProductIds as $optionProductId) {
                $optionProduct = $optionProductId ? $optionProductRepo->find($optionProductId) : null;

                if (! $optionProduct) {
                    continue;
                }

                $sanitized[$optionId] = $this->resolveBundleQty($bundleOption, $optionProduct, $bundleOptionQty[$optionId] ?? null);
            }
        }

        return $sanitized;
    }

    /**
     * Radio and select options let the customer change the quantity; checkbox and multiselect use the admin's.
     *
     * @throws InvalidInputException
     */
    private function resolveBundleQty($bundleOption, $optionProduct, mixed $userQty): mixed
    {
        if (in_array($bundleOption->type, ['radio', 'select'])) {
            return $userQty ?? $optionProduct->qty;
        }

        if ($userQty !== null && (int) $userQty !== (int) $optionProduct->qty) {
            throw new InvalidInputException(
                __('bagistoapi::app.graphql.cart.bundle-qty-not-changeable', [
                    'option' => $bundleOption->label,
                    'qty' => $optionProduct->qty,
                ])
            );
        }

        return $optionProduct->qty;
    }

    /**
     * Handle updating cart item quantity
     */
    private function handleUpdateItem(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->cartItemId || ! $data->quantity) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-id-quantity-required'));
        }

        if ($customer && $cart->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.cart.unauthorized-access'));
        }

        // Prevent quantity update for event and appointment booking products
        $this->guardBookingCartItemUpdate($cart, (int) $data->cartItemId);

        CartFacade::setCart($cart);

        Event::dispatch('cart.item.before.update', ['cartItem' => $data->cartItemId]);

        try {
            CartFacade::updateItems([
                'qty' => [
                    $data->cartItemId => $data->quantity,
                ],
            ]);

            CartFacade::collectTotals();

            Event::dispatch('cart.item.after.update', ['cartItem' => $data->cartItemId]);
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }

        $cart = CartFacade::getCart();

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        $cartData = CartData::fromModel($cart);
        $cartData->success = true;
        $cartData->message = __('bagistoapi::app.graphql.cart.cart-item-updated-successfully');

        return (array) $cartData;
    }

    /**
     * Handle removing item from cart
     */
    private function handleRemoveItem(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->cartItemId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-id-required'));
        }

        if ($customer && $cart->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.cart.unauthorized-access'));
        }

        CartFacade::setCart($cart);

        Event::dispatch('cart.item.before.remove', ['cartItem' => $data->cartItemId]);

        try {
            $removed = CartFacade::removeItem($data->cartItemId);

            if (! $removed) {
                throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-not-found'));
            }

            CartFacade::collectTotals();

            Event::dispatch('cart.item.after.remove', ['cartItem' => $data->cartItemId]);
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }

        $cartId = $cart->id;
        $cart = CartModel::find($cartId);

        if (! $cart) {
            $cartData = new CartData;
            $cartData->id = $cartId;
            $cartData->itemsCount = 0;
            $cartData->subtotal = 0;
            $cartData->grandTotal = 0;
            $cartData->taxAmount = 0;
            $cartData->discountAmount = 0;
            $cartData->shippingAmount = 0;
            $cartData->items = [];

            return (array) $cartData;
        }

        return (array) CartData::fromModel($cart);
    }

    /**
     * Handle removing multiple items from cart
     */
    private function handleRemoveItems(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->itemIds || ! is_array($data->itemIds) || empty($data->itemIds)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.item-ids-required'));
        }

        CartFacade::setCart($cart);

        foreach ($data->itemIds as $itemId) {
            try {
                CartFacade::removeItem($itemId);
            } catch (\Exception $exception) {

                throw new OperationFailedException($exception->getMessage(), 0, $exception);
            }
        }

        CartFacade::collectTotals();

        $cartId = $cart->id;
        $cart = CartModel::find($cartId);

        if (! $cart) {
            $cartData = new CartData;
            $cartData->id = $cartId;
            $cartData->itemsCount = 0;
            $cartData->subtotal = 0;
            $cartData->grandTotal = 0;
            $cartData->taxAmount = 0;
            $cartData->discountAmount = 0;
            $cartData->shippingAmount = 0;
            $cartData->items = [];

            return (array) $cartData;
        }

        return (array) CartData::fromModel($cart);
    }

    /**
     * Prevent quantity updates for booking products that don't allow it.
     *
     * - Event booking: quantity is determined by ticket selection, not changeable after add-to-cart.
     * - Appointment booking: always quantity 1, cannot be changed.
     */
    private function guardBookingCartItemUpdate(CartModel $cart, int $cartItemId): void
    {
        $cartItem = $cart->items->firstWhere('id', $cartItemId);

        if (! $cartItem || $cartItem->type !== 'booking') {
            return;
        }

        // Check additional.booking.type first, then fall back to DB lookup
        $bookingType = $cartItem->additional['booking']['type'] ?? null;

        if (! $bookingType) {
            $bookingType = BookingProduct::query()
                ->where('product_id', $cartItem->product_id)
                ->value('type');
        }

        if ($bookingType === 'event') {
            throw new InvalidInputException(
                __('bagistoapi::app.graphql.cart.event-booking-quantity-not-changeable')
            );
        }

        if ($bookingType === 'appointment') {
            throw new InvalidInputException(
                __('bagistoapi::app.graphql.cart.appointment-booking-quantity-not-changeable')
            );
        }
    }
}
