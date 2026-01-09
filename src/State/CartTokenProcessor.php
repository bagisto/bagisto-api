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

        // Extract Bearer token from Authorization header via TokenHeaderFacade
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

        if ($operationName === 'create' && isset($operationMap[$resourceClassName])) {
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
        if ($operationName === 'createOrGetCart') {
            return null;
        }

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
        if (! $data->productId || ! $data->quantity) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.product-id-quantity-required'));
        }

        $product = \Webkul\Product\Models\Product::find($data->productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.product-not-found'));
        }

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

                $this->guestCartTokensRepository->createToken($cart->id);
            }
        }

        try {
            Event::dispatch('cart.before.add', ['cartItem' => null]);

            CartFacade::setCart($cart);

            $cartData = [
                'quantity'   => $data->quantity,
                'product_id' => $product->id,
                ...(is_array($data->options) ? $data->options : []),
            ];

            CartFacade::addProduct($product, $cartData);

            CartFacade::collectTotals();

            Event::dispatch('cart.after.add', ['cart' => CartFacade::getCart()]);

            $updatedCart = CartFacade::getCart();
        } catch (\Exception $e) {
            throw new OperationFailedException($e->getMessage(), 0, $e);
        }

        $responseData = CartData::fromModel($updatedCart);

        $responseData->success = true;

        $responseData->message = __('bagistoapi::app.graphql.cart.product-added-successfully');

        return (array) $responseData;
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

        return (array) CartData::fromModel($cart);
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
                    $item->replicate()
                        ->fill(['cart_id' => $customerCart->id])
                        ->save();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $guestCart->update(['is_active' => 0]);

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

            $this->guestCartTokensRepository->create([
                'cart_id' => $cart->id,
                'token'   => $sessionToken,
            ]);

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
}
