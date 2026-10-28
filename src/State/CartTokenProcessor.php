<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Request;
use Webkul\BagistoApi\Dto\CartInput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\Services\Cart\GuestCartMerger;
use Webkul\BagistoApi\State\Concerns\HandlesBookingCartItems;
use Webkul\BagistoApi\State\Concerns\HandlesCartCouponsAndExtras;
use Webkul\BagistoApi\State\Concerns\HandlesCartItems;
use Webkul\BagistoApi\State\Concerns\HandlesCartLifecycle;
use Webkul\BagistoApi\Support\CartOptionFileStaging;
use Webkul\BookingProduct\Helpers\Booking;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\Shipping\Facades\Shipping;

class CartTokenProcessor implements ProcessorInterface
{
    use HandlesBookingCartItems;
    use HandlesCartCouponsAndExtras;
    use HandlesCartItems;
    use HandlesCartLifecycle;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository,
        protected GuestCartMerger $guestCartMerger,
        protected CartOptionFileStaging $optionFileStaging
    ) {}

    /**
     * Process.
     */
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

        $guestToken = ! $customer
            && $cart
            && $operationName !== 'mergeGuest'
            ? $token
            : null;

        try {
            $result = $this->executeOperation($operationName, $cart, $customer, $data);
        } finally {
            $this->keepGuestTokenBound($guestToken);
        }

        if (
            $operation instanceof Post
            && is_array($result)
            && isset($result['items'])
            && is_array($result['items'])
        ) {
            $result['items'] = array_map(
                static fn ($item) => is_object($item) ? (array) $item : $item,
                $result['items']
            );
        }

        return $result;
    }

    /**
     * Give a guest token a new empty cart when core deleted its cart for being empty, so the token keeps working.
     */
    private function keepGuestTokenBound(?string $guestToken): void
    {
        if (
            ! $guestToken
            || $this->guestCartTokensRepository->findCartByToken($guestToken)
        ) {
            return;
        }

        $cart = CartTokenFacade::createCart();

        $this->guestCartTokensRepository->updateOrCreate(
            ['token' => $guestToken],
            ['cart_id' => $cart->id],
        );
    }

    /**
     * Map BagistoApi operation to internal operation name.
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
            'CartToken' => 'createOrGetCart',
            'ReadCart' => 'read',
            'UpdateCartItem' => 'updateItem',
            'RemoveCartItem' => 'removeItem',
            'RemoveCartItems' => 'removeItems',
            'ApplyCoupon' => 'applyCoupon',
            'RemoveCoupon' => 'removeCoupon',
            'MoveToWishlist' => 'moveToWishlist',
            'EstimateShipping' => 'estimateShipping',
            'MergeCart' => 'mergeGuest',
        ];

        if (
            isset($operationMap[$resourceClassName])
            && (
                in_array($operationName, ['create', 'createCartToken'])
                || $operation instanceof Post
            )
        ) {
            return $operationMap[$resourceClassName];
        }

        if (
            $operationName === 'readCart'
            && $resourceClassName === 'CartToken'
        ) {
            return 'read';
        }

        return $operationName;
    }

    /**
     * Normalize and validate input data.
     */
    private function normalizeInputData(mixed $data, array $context, string $operationName): CartInput
    {
        $data = $data ?: new CartInput;

        $this->applyGraphQlInput($data, $context);
        $this->applyRestBody($data, $context);

        if (
            $operationName === 'read'
            && isset($context['args']['cartId'])
        ) {
            $data->cartId = $context['args']['cartId'];
        }

        return $data;
    }

    /**
     * Copy the GraphQL mutation `input` onto the matching DTO properties.
     */
    private function applyGraphQlInput(CartInput $data, array $context): void
    {
        $inputData = $context['args']['input'] ?? null;

        if (! is_array($inputData)) {
            return;
        }

        foreach ($inputData as $key => $value) {
            if (property_exists($data, $key)) {
                $data->$key = $value;
            }
        }
    }

    /**
     * Fill DTO properties the serializer left empty, because it snake-cases camelCase JSON keys.
     */
    private function applyRestBody(CartInput $data, array $context): void
    {
        $request = $context['request'] ?? null;

        if (
            ! $request
            || ! $request->isMethod('POST')
        ) {
            return;
        }

        foreach ($request->json()->all() as $key => $value) {
            $camelKey = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));

            if (
                ! property_exists($data, $camelKey)
                || $data->$camelKey !== null
            ) {
                continue;
            }

            $data->$camelKey = $this->castForProperty($data, $camelKey, $value);
        }
    }

    /**
     * Encode arrays as JSON for string-typed DTO properties such as booking and bundleOptions.
     */
    private function castForProperty(object $data, string $property, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $type = (new \ReflectionProperty($data, $property))->getType();

        return $type instanceof \ReflectionNamedType && $type->getName() === 'string'
            ? json_encode($value)
            : $value;
    }

    /**
     * Extract parameters for read operations from multiple sources.
     */
    private function extractReadOperationParameters(CartInput $data, array $uriVariables, array $context): CartInput
    {
        if (! empty($data->cartId)) {
            return $data;
        }

        if (isset($uriVariables['id'])) {

            $id = $uriVariables['id'];

            if (
                is_string($id)
                && str_contains($id, '/')
            ) {
                $id = (int) basename($id);
            }

            $data->cartId = (int) $id;

            return $data;
        }

        if (isset($context['args']['id'])) {

            $id = $context['args']['id'];

            if (
                is_string($id)
                && str_contains($id, '/')
            ) {
                $id = (int) basename($id);
            }

            $data->cartId = (int) $id;
        }

        return $data;
    }

    /**
     * Validate operation has required parameters.
     */
    private function validateOperation(string $operationName, ?string $token): void
    {
        $requiresToken = $operationName !== 'createOrGetCart';

        if (
            $requiresToken
            && ! $token
        ) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }
    }

    /**
     * Validate add product operation.
     */
    private function validateAddProduct(CartInput $data): void
    {
        if (! $data->productId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.product-id-required'));
        }

        if (
            ! $data->quantity
            || $data->quantity < 1
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.invalid-quantity'));
        }
    }

    /**
     * Validate update item operation.
     */
    private function validateUpdateItem(CartInput $data): void
    {
        if (! $data->cartItemId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-id-required'));
        }

        if (
            ! $data->quantity
            || $data->quantity < 1
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.invalid-quantity'));
        }
    }

    /**
     * Validate remove item operation.
     */
    private function validateRemoveItem(CartInput $data): void
    {
        if (! $data->cartItemId) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.cart-item-id-required'));
        }
    }

    /**
     * Validate remove items operation.
     */
    private function validateRemoveItems(CartInput $data): void
    {
        if (empty($data->itemIds)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.item-ids-required'));
        }
    }

    /**
     * Validate apply coupon operation.
     */
    private function validateApplyCoupon(CartInput $data): void
    {
        if (! $data->couponCode) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.coupon-code-required'));
        }
    }

    /**
     * Validate estimate shipping operation.
     */
    private function validateEstimateShipping(CartInput $data): void
    {
        if (
            ! $data->country
            || ! $data->postcode
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.shipping-address-required'));
        }
    }

    /**
     * Resolve cart based on operation and data.
     */
    private function resolveCart(string $operationName, CartInput $data, ?Customer $customer, ?string $token): ?CartModel
    {
        if ($operationName === 'mergeGuest') {
            return $this->resolveGuestCartForMerge($data);
        }

        if ($customer) {
            return $this->cartRepository->findOneWhere([
                'customer_id' => $customer->id,
                'is_active' => 1,
            ]);
        }

        if ($token) {
            return CartTokenFacade::getCartByToken($token);
        }

        return null;
    }

    /**
     * Resolve the guest cart to merge only from its guest cart token, never from a bare id.
     */
    private function resolveGuestCartForMerge(CartInput $data): ?CartModel
    {
        return $this->guestCartMerger->findMergeable($data->guestCartToken, $data->cartId ? (int) $data->cartId : null);
    }

    /**
     * Execute the operation handler.
     */
    private function executeOperation(
        string $operationName,
        ?CartModel $cart,
        ?Customer $customer,
        CartInput $data
    ): mixed {
        return match ($operationName) {
            'addProduct' => $this->handleAddProduct($cart, $customer, $data),
            'updateItem' => $this->handleUpdateItem($cart, $customer, $data),
            'removeItem' => $this->handleRemoveItem($cart, $customer, $data),
            'removeItems' => $this->handleRemoveItems($cart, $customer, $data),
            'read' => $this->handleGetCart($cart, $customer, $data),
            'collection' => $this->handleGetCarts($customer, $data),
            'mergeGuest' => $this->handleMergeGuest($cart, $customer, $data),
            'applyCoupon' => $this->handleApplyCoupon($cart, $customer, $data),
            'removeCoupon' => $this->handleRemoveCoupon($cart, $customer, $data),
            'moveToWishlist' => $this->handleMoveToWishlist($cart, $customer, $data),
            'estimateShipping' => $this->handleEstimateShipping($cart, $customer, $data),
            'createOrGetCart' => $this->handleCreateOrGetCart($customer, $data),
            default => throw new InvalidInputException(__('bagistoapi::app.graphql.cart.unknown-operation')),
        };
    }
}
