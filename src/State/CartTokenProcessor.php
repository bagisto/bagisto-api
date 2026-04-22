<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Service\BookingSlotParser;
use Webkul\Checkout\Facades\Cart as CartFacade;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Models\Customer;

/**
 * CartTokenProcessor - Handles cart operations with token-based authentication
 *
 * Supports:
 * - Create/add product to cart
 * - Update cart item quantity
 * - Remove cart item
 * - Get single cart
 * - Get all customer carts
 * - Merge guest cart to customer cart
 */
class CartTokenProcessor implements ProcessorInterface
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository
    ) {}

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): mixed {
        $request = Request::instance() ?? ($context['request'] ?? null);
        $operationName = $this->mapOperation($operation, $data, $context);

        $data = $this->normalizeInputData($data, $context, $operationName);

        if ($operationName === 'read') {
            $data = $this->extractReadOperationParameters($data, $uriVariables, $context);
        }

        $token = $request ? TokenHeaderFacade::getAuthorizationBearerToken($request) : null;

        $this->validateOperation($operationName, $token);

        $customer = $token ? $this->getCustomerFromToken($token) : null;

        $cart = $this->resolveCart($operationName, $data, $customer, $token);

        return $this->executeOperation($operationName, $cart, $customer, $data);
    }

    /**
     * Map BagistoApi operation to internal operation name
     */
    private function mapOperation(Operation $operation, mixed $data, array $context): string
    {
        $operationName = $operation->getName();

        $resourceClass = $operation->getClass();

        $resourceClassName = $resourceClass ? class_basename($resourceClass) : '';

        $pathBasedClass = null;
        if (isset($context['request'])) {
            $path = $context['request']->getPathInfo();
            if (strpos($path, 'apply-coupon') !== false) {
                $pathBasedClass = 'ApplyCoupon';
            } elseif (strpos($path, 'remove-coupon') !== false) {
                $pathBasedClass = 'RemoveCoupon';
            } elseif (strpos($path, 'update-cart-item') !== false) {
                $pathBasedClass = 'UpdateCartItem';
            } elseif (strpos($path, 'remove-cart-items') !== false) {
                $pathBasedClass = 'RemoveCartItems';
            } elseif (strpos($path, 'remove-cart-item') !== false) {
                $pathBasedClass = 'RemoveCartItem';
            } elseif (strpos($path, 'add-product-in-cart') !== false) {
                $pathBasedClass = 'AddProductInCart';
            }
        }

        if ($pathBasedClass) {
            $resourceClassName = $pathBasedClass;
        }

        $operationMap = [
            'AddProductInCart' => 'addProduct',
            'CartToken'        => 'createOrGetCart',
            'ReadCart'         => 'read',
            'UpdateCartItem'   => 'updateItem',
            'RemoveCartItem'   => 'removeItem',
            'RemoveCartItems'  => 'removeItems',
            'ApplyCoupon'      => 'applyCoupon',
            'RemoveCoupon'     => 'removeCoupon',
            'MoveToWishlist'   => 'moveToWishlist',
            'EstimateShipping' => 'estimateShipping',
            'MergeCart'        => 'mergeGuest',
        ];

        if (isset($operationMap[$resourceClassName]) && (
            in_array($operationName, ['create', 'createCartToken'])
            || $operation instanceof \ApiPlatform\Metadata\Post
        )) {
            return $operationMap[$resourceClassName];
        }

        if ($operationName === 'readCart' && $resourceClassName === 'CartToken') {
            return 'read';
        }

        return $operationName;
    }

    /**
     * Normalize and validate input data
     */
    private function normalizeInputData(mixed $data, array $context, string $operationName): CartInput
    {
        if (! $data) {
            $data = new CartInput;
        }

        // Handle GraphQL mutation input - data is wrapped in 'input' key
        if (isset($context['args']['input']) && is_array($context['args']['input'])) {
            $inputData = $context['args']['input'];

            // Map input fields to CartInput object
            foreach ($inputData as $key => $value) {
                if (property_exists($data, $key)) {
                    $data->$key = $value;
                }
            }
        }

        // REST fallback: SnakeCaseToCamelCaseNameConverter converts camelCase JSON keys
        // to snake_case during deserialization, which doesn't match camelCase DTO properties.
        if (isset($context['request']) && $context['request']->isMethod('POST')) {
            $requestData = $context['request']->json()->all();
            if (! empty($requestData)) {
                foreach ($requestData as $key => $value) {
                    $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
                    if (property_exists($data, $camelKey) && $data->$camelKey === null) {
                        // Encode arrays to JSON for string-typed DTO properties (e.g. booking, bundleOptions)
                        if (is_array($value)) {
                            $refProp = new \ReflectionProperty($data, $camelKey);
                            $propType = $refProp->getType();
                            if ($propType instanceof \ReflectionNamedType && $propType->getName() === 'string') {
                                $value = json_encode($value);
                            }
                        }
                        $data->$camelKey = $value;
                    }
                }
            }
        }

        if ($operationName === 'read' && isset($context['args'])) {
            if (isset($context['args']['cartId'])) {
                $data->cartId = $context['args']['cartId'];
            }
        }

        return $data;
    }

    /**
     * Extract parameters for read operations from multiple sources
     */
    private function extractReadOperationParameters(CartInput $data, array $uriVariables, array $context): CartInput
    {
        if (! empty($data->cartId)) {
            return $data;
        }

        if (isset($uriVariables['id'])) {

            $id = $uriVariables['id'];

            if (is_string($id) && str_contains($id, '/')) {
                $id = (int) basename($id);
            }

            $data->cartId = (int) $id;

            return $data;
        }

        if (isset($context['args']['id'])) {

            $id = $context['args']['id'];

            if (is_string($id) && str_contains($id, '/')) {
                $id = (int) basename($id);
            }

            $data->cartId = (int) $id;
        }

        return $data;
    }

    /**
     * Validate operation has required parameters
     */
    private function validateOperation(string $operationName, ?string $token): void
    {
        $requiresToken = ! in_array($operationName, ['createOrGetCart', 'read']);

        if ($requiresToken && ! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }
    }

    /**
     * Validate add product operation
     */
    private function validateAddProduct(CartInput $data): void
    {
        if (! $data->productId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.product-id-required'));
        }

        if (! $data->quantity || $data->quantity < 1) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.invalid-quantity'));
        }
    }

    /**
     * Validate update item operation
     */
    private function validateUpdateItem(CartInput $data): void
    {
        if (! $data->cartItemId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-id-required'));
        }

        if (! $data->quantity || $data->quantity < 1) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.invalid-quantity'));
        }
    }

    /**
     * Validate remove item operation
     */
    private function validateRemoveItem(CartInput $data): void
    {
        if (! $data->cartItemId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-id-required'));
        }
    }

    /**
     * Validate remove items operation
     */
    private function validateRemoveItems(CartInput $data): void
    {
        if (empty($data->itemIds)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-ids-required'));
        }
    }

    /**
     * Validate apply coupon operation
     */
    private function validateApplyCoupon(CartInput $data): void
    {
        if (! $data->couponCode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.coupon-code-required'));
        }
    }

    /**
     * Validate estimate shipping operation
     */
    private function validateEstimateShipping(CartInput $data): void
    {
        if (! $data->country || ! $data->postcode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.shipping-address-required'));
        }
    }

    /**
     * Resolve cart based on operation and data
     */
    private function resolveCart(string $operationName, CartInput $data, ?Customer $customer, ?string $token): ?CartModel
    {
        if ($operationName === 'mergeGuest' && $data->cartId) {
            return CartTokenFacade::getCartById((int) $data->cartId);
        }

        if ($customer) {
            return $this->cartRepository->findOneWhere([
                'customer_id' => $customer->id,
                'is_active'   => 1,
            ]);
        }

        if ($token) {
            return CartTokenFacade::getCartByToken($token);
        }

        if ($data->cartId && $operationName === 'read') {
            return $this->cartRepository->find($data->cartId);
        }

        return null;
    }

    /**
     * Execute the operation handler
     */
    private function executeOperation(
        string $operationName,
        ?CartModel $cart,
        ?Customer $customer,
        CartInput $data
    ): mixed {
        return match ($operationName) {
            'addProduct'       => $this->handleAddProduct($cart, $customer, $data),
            'updateItem'       => $this->handleUpdateItem($cart, $customer, $data),
            'removeItem'       => $this->handleRemoveItem($cart, $customer, $data),
            'removeItems'      => $this->handleRemoveItems($cart, $customer, $data),
            'read'             => $this->handleGetCart($cart, $customer, $data),
            'collection'       => $this->handleGetCarts($customer, $data),
            'mergeGuest'       => $this->handleMergeGuest($cart, $customer, $data),
            'applyCoupon'      => $this->handleApplyCoupon($cart, $customer, $data),
            'removeCoupon'     => $this->handleRemoveCoupon($cart, $customer, $data),
            'moveToWishlist'   => $this->handleMoveToWishlist($cart, $customer, $data),
            'estimateShipping' => $this->handleEstimateShipping($cart, $customer, $data),
            'createOrGetCart'  => $this->handleCreateOrGetCart($customer, $data),
            default            => throw new InvalidInputException(__('bagistoapi::app.graphql.cart.unknown-operation')),
        };
    }

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

        $product = \Webkul\Product\Models\Product::find($data->productId);
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

        if (! $cart) {
            $channel = core()->getCurrentChannel();
            if ($customer) {
                $cart = $this->cartRepository->create([
                    'customer_id' => $customer->id,
                    'channel_id'  => $channel->id,
                    'is_active'   => 1,
                ]);
            } else {
                $cart = $this->cartRepository->create([
                    'channel_id' => $channel->id,
                    'is_active'  => 1,
                ]);
                $guestCartTokenDetail = $this->guestCartTokensRepository->createToken($cart->id);

            }
        }

        try {
            // Handle is_buy_now - deactivate cart and prepare for checkout
            if (! empty($data->isBuyNow)) {
                CartFacade::deActivateCart();

                // Create a new cart for buy now
                $channel = core()->getCurrentChannel();
                if ($customer) {
                    $cart = $this->cartRepository->create([
                        'customer_id' => $customer->id,
                        'channel_id'  => $channel->id,
                        'is_active'   => 1,
                    ]);
                } else {
                    $cart = $this->cartRepository->create([
                        'channel_id' => $channel->id,
                        'is_active'  => 1,
                    ]);
                    $guestCartTokenDetail = $this->guestCartTokensRepository->createToken($cart->id);
                }

                $redirectUri = route('shop.checkout.onepage.index');
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
                'quantity'   => $data->quantity,
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
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
        }

        if (! $updatedCart) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.cart.add-product-failed'));
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
        $optionRepo = app(\Webkul\Product\Repositories\ProductBundleOptionRepository::class);
        $optionProductRepo = app(\Webkul\Product\Repositories\ProductBundleOptionProductRepository::class);

        $sanitized = [];

        foreach ($bundleOptions as $optionId => $optionProductIds) {
            if (! is_array($optionProductIds)) {
                continue;
            }

            $bundleOption = $optionRepo->find($optionId);

            if (! $bundleOption) {
                continue;
            }

            // Checkbox/Multiselect: qty fixed by admin; Radio/Select: customer can change
            $canChangeQty = in_array($bundleOption->type, ['radio', 'select']);

            foreach ($optionProductIds as $optionProductId) {
                if (! $optionProductId) {
                    continue;
                }

                $optionProduct = $optionProductRepo->find($optionProductId);

                if (! $optionProduct) {
                    continue;
                }

                if ($canChangeQty) {
                    $sanitized[$optionId] = $bundleOptionQty[$optionId] ?? $optionProduct->qty;
                } else {
                    $userQty = $bundleOptionQty[$optionId] ?? null;

                    // If customer sent a qty that differs from admin-defined qty, throw error
                    if ($userQty !== null && (int) $userQty !== (int) $optionProduct->qty) {
                        throw new InvalidInputException(
                            __('bagistoapi::app.graphql.cart.bundle-qty-not-changeable', [
                                'option' => $bundleOption->label,
                                'qty'    => $optionProduct->qty,
                            ])
                        );
                    }

                    $sanitized[$optionId] = $optionProduct->qty;
                }
            }
        }

        return $sanitized;
    }

    /**
     * Normalize booking payload so clients can send user-friendly slot strings like "10:00 AM - 11:00 AM".
     */
    private function normalizeBookingForCart(array $booking, int $productId): array
    {
        if (($booking['type'] ?? null) === 'event') {
            $this->validateEventBookingPayload($booking);

            return $booking;
        }

        if (($booking['type'] ?? null) === 'table') {
            $note = $booking['note'] ?? null;

            if (! is_string($note) || trim($note) === '') {
                throw new InvalidInputException('booking.note is required for table booking.');
            }
        }

        if (! isset($booking['slot']) || ! is_string($booking['slot'])) {
            return $booking;
        }

        if (! $this->looksLikeFormattedTimeRange($booking['slot'])) {
            return $booking;
        }

        $date = $booking['date'] ?? null;

        if (! is_string($date) || $date === '') {
            throw new InvalidInputException('booking.date is required when booking.slot is a formatted time range.');
        }

        $timestamps = (new BookingSlotParser)->parse($date, $booking['slot'], $this->getChannelTimezone());

        if (
            ($booking['type'] ?? null) === 'rental'
            || ($booking['renting_type'] ?? null) === 'hourly'
        ) {
            $booking['renting_type'] ??= 'hourly';
            $booking['slot'] = $timestamps;
        } else {
            $bookingType = $booking['type'] ?? null;
            $duration = $this->getBookingDurationMinutes($productId, is_string($bookingType) ? $bookingType : null);

            if ($duration) {
                $timestamps['to'] = $timestamps['from'] + ($duration * 60);
            }

            $booking['slot'] = $timestamps['from'].'-'.$timestamps['to'];

            $this->validateFormattedSlotExists($productId, $bookingType, $date, $booking['slot']);
        }

        return $booking;
    }

    private function validateEventBookingPayload(array $booking): void
    {
        $qty = $booking['qty'] ?? null;

        if (! is_array($qty) || empty($qty)) {
            throw new InvalidInputException('booking.qty is required for event booking.');
        }

        $hasAtLeastOne = false;

        foreach ($qty as $ticketId => $count) {
            if ($count === null || $count === '') {
                continue;
            }

            if (! is_numeric($count) || (int) $count < 0) {
                throw new InvalidInputException('Event ticket quantity must be a non-negative number.');
            }

            if ((int) $count > 0) {
                $hasAtLeastOne = true;
            }
        }

        if (! $hasAtLeastOne) {
            throw new InvalidInputException('Select at least one ticket quantity for event booking.');
        }
    }

    private function getChannelTimezone(): ?string
    {
        $channel = core()->getCurrentChannel();

        return $channel?->timezone ?: config('app.timezone');
    }

    private function getBookingDurationMinutes(int $productId, ?string $bookingType): ?int
    {
        if (! $bookingType || ! in_array($bookingType, ['appointment', 'default', 'table'], true)) {
            return null;
        }

        $bookingProduct = \Webkul\BookingProduct\Models\BookingProduct::query()
            ->where('product_id', $productId)
            ->first();

        return match ($bookingType) {
            'appointment' => (int) ($bookingProduct?->appointment_slot?->duration ?? 0),
            'default'     => (int) ($bookingProduct?->default_slot?->duration ?? 0),
            'table'       => (int) ($bookingProduct?->table_slot?->duration ?? 0),
            default       => null,
        } ?: null;
    }

    private function validateFormattedSlotExists(int $productId, mixed $bookingType, string $date, string $slot): void
    {
        if (! is_string($bookingType) || ! in_array($bookingType, ['appointment', 'default', 'table'], true)) {
            return;
        }

        $bookingProduct = \Webkul\BookingProduct\Models\BookingProduct::query()
            ->where('product_id', $productId)
            ->first();

        if (! $bookingProduct) {
            return;
        }

        $bookingHelper = app(\Webkul\BookingProduct\Helpers\Booking::class);

        $typeHelper = app($bookingHelper->getTypeHelper($bookingType));

        $slots = $typeHelper->getSlotsByDate($bookingProduct, $date);

        $exists = collect($slots)->contains(function ($candidate) use ($slot) {
            return ($candidate['timestamp'] ?? null) === $slot;
        });

        if (! $exists) {
            throw new InvalidInputException('Selected slot is not available for the given date.');
        }
    }

    private function looksLikeFormattedTimeRange(string $value): bool
    {
        $slot = trim($value);

        if ($slot === '') {
            return false;
        }

        $hasSeparator = preg_match('/\\s*[-–—]\\s*/u', $slot) === 1;
        $hasAmPm = preg_match('/\\b(am|pm)\\b/i', $slot) === 1;
        $hasColon = str_contains($slot, ':');

        return $hasSeparator && ($hasColon || $hasAmPm);
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
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
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
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
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

    private function handleGetCart(?CartModel $cart, ?Customer $customer, CartInput $data)
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if ($customer && $cart->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.cart.unauthorized-access'));
        }

        CartFacade::setCart($cart);

        if ($cart->shipping_method && $cart->shipping_address) {
            \Webkul\Shipping\Facades\Shipping::collectRates();
        }

        CartFacade::collectTotals();

        $cart = CartFacade::getCart();
        $cart->load('items.product');

        $cartData = CartData::fromModel($cart);

        return (array) $cartData;
    }

    /**
     * Handle getting all customer carts
     */
    private function handleGetCarts(?Customer $customer, CartInput $data): array
    {
        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authenticated-only'));
        }

        $carts = $this->cartRepository->findWhere([
            'customer_id' => $customer->id,
        ])->load('items.product');

        return [
            'carts' => CartData::collection($carts),
        ];
    }

    /**
     * Handle merging guest cart to customer cart
     */
    private function handleMergeGuest(?CartModel $guestCart, ?Customer $customer, CartInput $data): array
    {
        if (! $customer) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.merge-requires-auth'));
        }

        if (! $guestCart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.guest-cart-not-found'));
        }

        $customerCart = $this->cartRepository->findOneWhere([
            'customer_id' => $customer->id,
            'is_active'   => 1,
        ]);

        if (! $customerCart) {
            $customerCart = $this->cartRepository->create([
                'customer_id' => $customer->id,
                'channel_id'  => $guestCart->channel_id,
                'is_active'   => 1,
            ]);

            $this->guestCartTokensRepository->create([
                'cart_id' => $customerCart->id,
                'token'   => $this->generateSecureToken(),
            ]);
        }

        $guestCart->load('items.child');

        foreach ($guestCart->items as $item) {
            try {
                $cartItem = $customerCart->items()
                    ->where('product_id', $item->product_id)
                    ->where('type', $item->type)
                    ->first();

                if ($cartItem) {
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + $item->quantity,
                    ]);
                } else {
                    $newItem = $item->replicate()
                        ->fill(['cart_id' => $customerCart->id]);
                    $newItem->save();

                    // Replicate child item for configurable products
                    if ($item->type === 'configurable' && $item->child) {
                        $item->child->replicate()
                            ->fill([
                                'cart_id'   => $customerCart->id,
                                'parent_id' => $newItem->id,
                            ])
                            ->save();
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $guestCart->update(['is_active' => 0]);

        // Reload cart with relationships and remove invalid items
        // (e.g., configurable items without child entries or deleted products)
        $customerCart = CartModel::with('items.product', 'items.child.product')->find($customerCart->id);

        foreach ($customerCart->items as $item) {
            if (! $item->product
                || ($item->type === 'configurable' && ! $item->child)
            ) {
                $item->delete();
            }
        }

        $customerCart = CartModel::with('items.product')->find($customerCart->id);

        CartFacade::setCart($customerCart);

        CartFacade::collectTotals();

        $customerCart = CartModel::find($customerCart->id);

        $cartData = CartData::fromModel($customerCart);
        $cartData->success = true;
        $cartData->message = __('bagistoapi::app.graphql.cart.guest-cart-merged');

        return (array) $cartData;
    }

    /**
     * Handle applying coupon code
     */
    private function handleApplyCoupon(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->couponCode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.coupon-code-required'));
        }

        if ($customer && $cart->customer_id !== $customer->id) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.cart.unauthorized-access'));
        }

        CartFacade::setCart($cart);

        try {
            CartFacade::setCouponCode($data->couponCode)->collectTotals();
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
        }

        $cart = CartFacade::getCart();

        return (array) CartData::fromModel($cart);
    }

    /**
     * Get customer from bearer token
     */
    private function getCustomerFromToken(string $token): ?Customer
    {
        try {
            $customerRepository = app('Webkul\Customer\Repositories\CustomerRepository');

            $customer = $customerRepository->findOneByField('token', $token);

            if ($customer) {
                return $customer;
            }

            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

            if ($personalAccessToken && $personalAccessToken->tokenable instanceof Customer) {
                return $personalAccessToken->tokenable;
            }
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
        }

        return null;
    }

    /**
     * Handle createOrGetCart operation
     */
    private function handleCreateOrGetCart(?Customer $customer, CartInput $data): array
    {
        if ($customer) {
            $cart = $this->cartRepository->findOneWhere([
                'customer_id' => $customer->id,
                'is_active'   => 1,
            ]);

            if (! $cart) {
                $cart = $this->cartRepository->create([
                    'customer_id' => $customer->id,
                    'channel_id'  => core()->getCurrentChannel()->id,
                    'is_active'   => 1,
                ]);
            }

            $cartData = CartData::fromModel($cart);
            $cartData->isGuest = false;
            $cartData->success = true;
            $cartData->message = __('bagistoapi::app.graphql.cart.using-authenticated-cart');

            return (array) $cartData;
        } else {
            $sessionToken = $this->generateSecureToken();

            $cart = $this->cartRepository->create([
                'channel_id' => core()->getCurrentChannel()->id,
                'is_active'  => 1,
            ]);

            $guestCartData = [
                'cart_id' => $cart->id,
                'token'   => $sessionToken,
            ];

            // Note: device_token is NOT saved for guest users anymore
            // Only logged-in customers can receive push notifications

            $this->guestCartTokensRepository->create($guestCartData);

            $cartData = CartData::fromModel($cart);
            $cartData->sessionToken = $sessionToken;
            $cartData->cartToken = $sessionToken;
            $cartData->isGuest = true;
            $cartData->success = true;
            $cartData->message = __('bagistoapi::app.graphql.cart.new-guest-cart-created');

            return (array) $cartData;
        }
    }

    /**
     * Generate cryptographically secure token
     */
    private function generateSecureToken(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
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
            } catch (\Exception $e) {

                throw new OperationFailedException($e->getMessage(), 0, $e);
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
     * Handle removing coupon code from cart
     */
    private function handleRemoveCoupon(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        CartFacade::setCart($cart);

        try {
            CartFacade::removeCouponCode()->collectTotals();

            $cart = CartFacade::getCart();

            if (! $cart) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.cart.remove-coupon-failed'));
            }

            return (array) CartData::fromModel($cart);
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Handle moving items to wishlist
     */
    private function handleMoveToWishlist(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->itemIds || ! is_array($data->itemIds) || empty($data->itemIds)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.item-ids-required'));
        }

        CartFacade::setCart($cart);

        foreach ($data->itemIds as $index => $itemId) {
            try {
                $qty = $data->quantities[$index] ?? 1;
                CartFacade::moveToWishlist($itemId, $qty);
            } catch (\Exception $e) {
                throw new OperationFailedException($e->getMessage(), 0, $e);
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
     * Handle estimating shipping methods and tax
     */
    private function handleEstimateShipping(?CartModel $cart, ?Customer $customer, CartInput $data): array
    {
        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.cart-not-found'));
        }

        if (! $data->country || ! $data->state || ! $data->postcode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.address-data-required'));
        }

        CartFacade::setCart($cart);

        try {
            $address = (new \Webkul\Checkout\Models\CartAddress)->fill([
                'country'  => $data->country,
                'state'    => $data->state,
                'postcode' => $data->postcode,
                'cart_id'  => $cart->id,
            ]);

            $cart->setRelation('billing_address', $address);
            $cart->setRelation('shipping_address', $address);

            CartFacade::setCart($cart);

            if ($data->shippingMethod) {
                CartFacade::saveShippingMethod($data->shippingMethod);
            }

            CartFacade::collectTotals();

            return (array) CartData::fromModel(CartFacade::getCart());
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
        }
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
            $bookingType = \Webkul\BookingProduct\Models\BookingProduct::query()
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
