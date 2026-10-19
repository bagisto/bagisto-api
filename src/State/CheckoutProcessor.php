<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\CheckoutAddressInput;
use Webkul\BagistoApi\Dto\CheckoutAddressOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\OperationFailedException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Models\CheckoutOrder;
use Webkul\BagistoApi\Models\CheckoutPaymentMethod;
use Webkul\BagistoApi\Models\CheckoutShippingMethod;
use Webkul\BagistoApi\State\Concerns\ResolvesCartToken;
use Webkul\CartRule\Exceptions\CouponUsageLimitExceededException;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Core\Rules\PostCode;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Payment\Facades\Payment;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shipping\Facades\Shipping;

class CheckoutProcessor implements ProcessorInterface
{
    use ResolvesCartToken;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected OrderRepository $orderRepository,
        protected CartRepository $cartRepository
    ) {}

    /**
     * Process checkout operation.
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): mixed {
        $request = Request::instance() ?? ($context['request'] ?? null);
        $operationName = $this->mapOperation($operation);

        if ($operationName === 'read') {
            $token = TokenHeaderFacade::getAuthorizationBearerToken($request);

            if (! $token) {
                throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
            }

            $cart = CartTokenFacade::getCartByToken($token);

            if (! $cart) {
                throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
            }

            return $this->fetchAddresses($cart);
        }

        if (
            $operation instanceof Post
            && ! $data instanceof CheckoutAddressInput
        ) {
            $data = $this->buildCheckoutAddressInputFromRequest($request);
        }

        if (! $data instanceof CheckoutAddressInput) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-input'));
        }

        $token = null;
        if ($request) {
            $token = TokenHeaderFacade::getAuthorizationBearerToken($request);
        }

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $cart = CartTokenFacade::getCartByToken($token);

        if (! $cart) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.invalid-token'));
        }

        return match ($operationName) {
            'saveAddress' => $this->saveAddress($cart, $data),
            'saveShippingMethod' => $this->saveShippingMethod($cart, $data),
            'savePaymentMethod' => $this->savePaymentMethod($cart, $data),
            'createOrder' => $this->createOrder($cart, $data),
            default => throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.unknown-operation', ['operation' => $operationName])),
        };
    }

    /**
     * Map BagistoApi operation name to internal operation type.
     */
    private function mapOperation(Operation $operation): string
    {
        $operationName = $operation->getName();
        $resourceClass = $operation->getClass();
        $resourceClassName = $resourceClass ? class_basename($resourceClass) : '';

        return match ($resourceClassName) {
            'CheckoutAddress' => 'saveAddress',
            'CheckoutShippingMethod' => 'saveShippingMethod',
            'CheckoutPaymentMethod' => 'savePaymentMethod',
            'CheckoutOrder' => 'createOrder',
            default => $operationName,
        };
    }

    /**
     * Save billing and shipping addresses for cart, the way core's checkout address step does.
     */
    private function saveAddress($cart, CheckoutAddressInput $input)
    {
        try {
            Cart::setCart($cart);

            $this->assertGuestCheckoutAllowed($cart);

            $this->assertCartHasNoErrors();

            if (
                ! $input->billingFirstName
                && ! $input->billingAddress
            ) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.billing-address-required'));
            }

            $useForShipping = (bool) $input->useForShipping;

            $needsShippingAddress = $cart->haveStockableItems() && ! $useForShipping;

            if (
                $needsShippingAddress
                && ! $input->shippingFirstName
                && ! $input->shippingAddress
            ) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-address-required'));
            }

            $params = [
                'billing' => [
                    ...$this->addressParams('billing', $input),
                    'use_for_shipping' => $useForShipping,
                ],
            ];

            if ($needsShippingAddress) {
                $params['shipping'] = $this->addressParams('shipping', $input);
            }

            $this->validateAddresses($params);

            Cart::saveAddresses($params);

            Cart::collectTotals();

            $cart = Cart::getCart();

            if ($cart->haveStockableItems()) {
                Shipping::collectRates();
            }

            return $this->buildAddressOutput($cart->billing_address, $cart->shipping_address);
        } catch (InvalidInputException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Map one side of the address input to the keys core's cart address step expects.
     */
    private function addressParams(string $type, CheckoutAddressInput $input): array
    {
        $address = $input->{$type.'Address'};

        return [
            'company_name' => $input->{$type.'CompanyName'},
            'first_name' => $input->{$type.'FirstName'},
            'last_name' => $input->{$type.'LastName'},
            'email' => $input->{$type.'Email'} ?: $input->billingEmail,
            'address' => filled($address) ? [$address] : [],
            'country' => $input->{$type.'Country'},
            'state' => $input->{$type.'State'},
            'city' => $input->{$type.'City'},
            'postcode' => $input->{$type.'Postcode'},
            'phone' => $input->{$type.'PhoneNumber'},
        ];
    }

    /**
     * Apply the storefront's cart address rules, reporting failures by their API field names.
     */
    private function validateAddresses(array $params): void
    {
        $rules = [];

        $attributes = [];

        foreach (array_keys($params) as $type) {
            $rules += [
                "{$type}.company_name" => ['nullable'],
                "{$type}.first_name" => ['required'],
                "{$type}.last_name" => ['required'],
                "{$type}.email" => ['required'],
                "{$type}.address" => ['required', 'array', 'min:1'],
                "{$type}.city" => ['required'],
                "{$type}.country" => core()->isCountryRequired() ? ['required'] : ['nullable'],
                "{$type}.state" => core()->isStateRequired() ? ['required'] : ['nullable'],
                "{$type}.postcode" => core()->isPostCodeRequired() ? ['required', new PostCode] : [new PostCode],
                "{$type}.phone" => ['required', new PhoneNumber],
            ];

            $attributes += [
                "{$type}.company_name" => "{$type}CompanyName",
                "{$type}.first_name" => "{$type}FirstName",
                "{$type}.last_name" => "{$type}LastName",
                "{$type}.email" => "{$type}Email",
                "{$type}.address" => "{$type}Address",
                "{$type}.city" => "{$type}City",
                "{$type}.country" => "{$type}Country",
                "{$type}.state" => "{$type}State",
                "{$type}.postcode" => "{$type}Postcode",
                "{$type}.phone" => "{$type}PhoneNumber",
            ];
        }

        $validator = Validator::make($params, $rules, [], $attributes);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first(), 422);
        }
    }

    /**
     * Save shipping method for cart.
     */
    private function saveShippingMethod($cart, CheckoutAddressInput $input)
    {
        try {
            cart()->setCart($cart);

            $this->assertCartHasNoErrors();

            if (! $input->shippingMethod) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-method-required'));
            }

            Shipping::collectRates();

            if (! Shipping::isMethodCodeExists($input->shippingMethod)) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-shipping-method'));
            }

            if (! Cart::saveShippingMethod($input->shippingMethod)) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-method-save-failed'));
            }

            Cart::collectTotals();

            return $this->shippingMethodOutput([
                'id' => (string) $cart->id,
                'success' => true,
                'message' => __('bagistoapi::app.graphql.checkout.shipping-method-saved'),
                'cartToken' => $this->resolveCartToken($cart),
                'shippingMethod' => (string) ($cart->shipping_method ?? ''),
            ]);
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Save payment method for cart.
     */
    private function savePaymentMethod($cart, CheckoutAddressInput $input)
    {
        cart()->setCart($cart);

        if (! $input->paymentMethod) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.payment-method-required'));
        }

        $this->assertCartHasNoErrors();

        $paymentMethodConfig = config('payment_methods.'.$input->paymentMethod);
        if (
            ! $paymentMethodConfig
            || ! isset($paymentMethodConfig['class'])
            || ! $this->isPaymentMethodAvailable($input->paymentMethod)
        ) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-payment-method'));
        }

        if (! Cart::savePaymentMethod(['method' => $input->paymentMethod])) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.payment-method-save-failed'));
        }

        try {
            Cart::collectTotals();
            $cart = Cart::getCart();

            [$paymentGatewayUrl, $paymentData] = $this->resolvePaymentGateway($cart, $paymentMethodConfig, $input);

            return $this->paymentMethodOutput([
                'success' => true,
                'message' => __('bagistoapi::app.graphql.checkout.payment-method-saved'),
                'cartToken' => $this->resolveCartToken($cart),
                'paymentMethod' => (string) ($cart->payment?->method ?? ''),
                'paymentGatewayUrl' => $paymentGatewayUrl,
                'paymentData' => $paymentData,
            ]);
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Resolve where the customer pays and the data the gateway needs, for the saved payment method.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolvePaymentGateway($cart, array $paymentMethodConfig, CheckoutAddressInput $input): array
    {
        if (! $cart->payment) {
            return [null, null];
        }

        $paymentMethodClass = app($paymentMethodConfig['class']);

        if (
            ! is_callable([$paymentMethodClass, 'getPaymentUrl'])
            || ! is_callable([$paymentMethodClass, 'getPaymentData'])
        ) {
            return [Payment::getRedirectUrl($cart) ?: null, null];
        }

        $paymentData = $this->applyReturnUrls($paymentMethodClass->getPaymentData($cart), $paymentMethodClass, $input);

        return [$paymentMethodClass->getPaymentUrl(), json_encode($paymentData)];
    }

    /**
     * Add the storefront return URLs to the payment data and sign it when the gateway supports it.
     */
    private function applyReturnUrls(array $paymentData, object $paymentMethodClass, CheckoutAddressInput $input): array
    {
        $returnUrls = array_filter([
            'surl' => $input->paymentSuccessUrl,
            'furl' => $input->paymentFailureUrl,
            'curl' => $input->paymentCancelUrl,
        ]);

        if (! $returnUrls) {
            return $paymentData;
        }

        $paymentData = array_merge($paymentData, $returnUrls);

        if (method_exists($paymentMethodClass, 'generateHash')) {
            $paymentData['hash'] = $paymentMethodClass->generateHash(
                $paymentData['txnid'],
                $paymentData['amount'],
                $paymentData['productinfo'],
                $paymentData['firstname'],
                $paymentData['email'],
                $paymentData['udf1']
            );
        }

        return $paymentData;
    }

    /**
     * Create order from cart data.
     */
    private function createOrder($cart, CheckoutAddressInput $input)
    {
        try {
            Cart::setCart($cart);

            $this->assertCartHasNoErrors();

            Cart::collectTotals();

            $cart = Cart::getCart();

            $this->validateOrderCreation($cart, $input);

            if ($redirectUrl = Payment::getRedirectUrl($cart)) {
                return $this->orderOutput([
                    'id' => $cart->id,
                    'cartToken' => $this->resolveCartToken($cart),
                    'redirect' => true,
                    'redirectUrl' => $redirectUrl,
                    'success' => true,
                    'message' => __('bagistoapi::app.graphql.checkout.order-redirect-required'),
                ]);
            }

            $orderData = $this->buildOrderDataFromCart($cart);
            try {
                $order = $this->orderRepository->create($orderData);
            } catch (CouponUsageLimitExceededException $exception) {
                Cart::removeCouponCode();

                Cart::collectTotals();

                throw new InvalidInputException(__('shop::app.checkout.coupon.usage-limit-exceeded'));
            }

            if (
                ! $order
                || ! $order->id
            ) {
                throw new \Exception(__('bagistoapi::app.graphql.checkout.order-creation-failed'));
            }

            $orderId = $order->id;
            $order = $this->orderRepository->find($orderId);

            if (! $order) {
                throw new \Exception(__('bagistoapi::app.graphql.checkout.order-retrieval-failed', ['orderId' => $orderId]));
            }

            Cart::deActivateCart($cart);

            Event::dispatch('order.created.after', $order);

            return $this->orderOutput([
                'id' => $cart->id,
                'cartToken' => $this->resolveCartToken($cart),
                'orderId' => (string) $order->id,
                'redirect' => false,
                'redirectUrl' => null,
                'success' => true,
                'message' => __('bagistoapi::app.graphql.checkout.order-placed'),
            ]);
        } catch (InvalidInputException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Build order data from cart.
     */
    private function buildOrderDataFromCart($cart): array
    {
        $orderResource = new OrderResource($cart);

        return $orderResource->jsonSerialize();
    }

    /**
     * Validate order can be created.
     */
    private function validateOrderCreation($cart, CheckoutAddressInput $input): void
    {
        if (
            ! $cart
            || $cart->items()->count() === 0
        ) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.cart-empty'));
        }

        $customer = $cart->customer;

        if ($customer?->is_suspended) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.account-suspended'));
        }

        if (
            $customer
            && ! $customer->status
        ) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.account-inactive'));
        }

        $this->assertGuestCheckoutAllowed($cart);

        $minimumOrderAmount = core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;
        if (! Cart::haveMinimumOrderAmount()) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.minimum-order-not-met', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        $hasBillingAddress = $input->billingAddress || $cart->billing_address()->exists();
        if (! $hasBillingAddress) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.billing-address-required'));
        }

        $hasShippingAddress = $input->shippingAddress || $input->useForShipping || $cart->shipping_address()->exists();
        if (
            ! $hasShippingAddress
            && $cart->haveStockableItems()
        ) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-address-required'));
        }

        $hasEmail = $cart->customer_email || $input->billingEmail || ($cart->billing_address && $cart->billing_address->email);
        if (! $hasEmail) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.email-required'));
        }

        if ($cart->haveStockableItems()) {
            $hasShippingMethod = $input->shippingMethod || $cart->shipping_method;
            if (! $hasShippingMethod) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.shipping-method-required'));
            }

            if (! $cart->selected_shipping_rate) {
                throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-shipping-method'));
            }
        }

        $hasPaymentMethod = $input->paymentMethod || $cart->payment()->exists();
        if (! $hasPaymentMethod) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.payment-method-required'));
        }

        if (! $this->isPaymentMethodAvailable($cart->payment?->method)) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.invalid-payment-method'));
        }
    }

    /**
     * Stop when the cart carries an error, such as an item no longer in stock.
     */
    private function assertCartHasNoErrors(): void
    {
        if (Cart::hasError()) {
            throw new OperationFailedException(Cart::getErrors()['message'] ?? __('bagistoapi::app.graphql.checkout.cart-empty'));
        }
    }

    /**
     * Apply the storefront's guest checkout rules to a cart that has no customer.
     */
    private function assertGuestCheckoutAllowed($cart): void
    {
        if ($cart->customer_id) {
            return;
        }

        if (
            ! core()->getConfigData('sales.checkout.shopping_cart.allow_guest_checkout')
            || $cart->hasDownloadableItems()
            || ! $cart->hasGuestCheckoutItems()
        ) {
            throw new OperationFailedException(__('bagistoapi::app.graphql.checkout.guest-checkout-not-allowed'));
        }
    }

    /**
     * Whether the method is offered for the current cart. The PayPal Smart Button is never
     * accepted here, as it captures payment through its own client-side flow.
     */
    private function isPaymentMethodAvailable(?string $method): bool
    {
        if (
            ! $method
            || $method === 'paypal_smart_button'
        ) {
            return false;
        }

        return collect(Payment::getSupportedPaymentMethods()['payment_methods'] ?? [])
            ->contains('method', $method);
    }

    /**
     * Build a CheckoutAddressInput DTO from the incoming REST request body.
     */
    private function buildCheckoutAddressInputFromRequest($request): CheckoutAddressInput
    {
        $input = new CheckoutAddressInput;

        $body = [];
        if (
            $request
            && method_exists($request, 'all')
        ) {
            $body = $request->all();
        }
        if (empty($body)) {
            $body = request()->all();
        }

        $fields = [
            'billingFirstName', 'billingLastName', 'billingEmail', 'billingCompanyName',
            'billingAddress', 'billingCountry', 'billingState', 'billingCity',
            'billingPostcode', 'billingPhoneNumber',
            'shippingFirstName', 'shippingLastName', 'shippingEmail', 'shippingCompanyName',
            'shippingAddress', 'shippingCountry', 'shippingState', 'shippingCity',
            'shippingPostcode', 'shippingPhoneNumber',
            'shippingMethod', 'paymentMethod',
            'paymentSuccessUrl', 'paymentFailureUrl', 'paymentCancelUrl',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $body)) {
                $input->{$field} = $body[$field];
            }
        }

        if (array_key_exists('useForShipping', $body)) {
            $input->useForShipping = filter_var($body['useForShipping'], FILTER_VALIDATE_BOOLEAN);
        }

        return $input;
    }

    /**
     * Build CartData from cart model.
     */
    private function buildCartData($cart): CartData
    {
        $cartData = CartData::fromModel($cart);

        return $cartData;
    }

    /**
     * Build CheckoutAddressOutput from cart address models.
     */
    private function buildAddressOutput($billingAddress = null, $shippingAddress = null)
    {
        $output = new CheckoutAddressOutput;
        $output->success = true;
        $output->message = __('bagistoapi::app.graphql.checkout.address-saved');

        if ($billingAddress) {
            $output->id = $billingAddress->id;
            $output->cartToken = $this->resolveCartToken($billingAddress->cart);
            $output->customerId = $billingAddress->cart->customer_id;

            $output->billingFirstName = (string) ($billingAddress->first_name ?? '');
            $output->billingLastName = (string) ($billingAddress->last_name ?? '');
            $output->billingEmail = (string) ($billingAddress->email ?? '');
            $output->billingCompanyName = (string) ($billingAddress->company_name ?? '');
            $output->billingAddress = (string) ($billingAddress->address ?? '');
            $output->billingCountry = (string) ($billingAddress->country ?? '');
            $output->billingState = (string) ($billingAddress->state ?? '');
            $output->billingCity = (string) ($billingAddress->city ?? '');
            $output->billingPostcode = (string) ($billingAddress->postcode ?? '');
            $output->billingPhoneNumber = (string) ($billingAddress->phone ?? '');
        }

        if ($shippingAddress) {
            $output->shippingFirstName = (string) ($shippingAddress->first_name ?? '');
            $output->shippingLastName = (string) ($shippingAddress->last_name ?? '');
            $output->shippingEmail = (string) ($shippingAddress->email ?? '');
            $output->shippingCompanyName = (string) ($shippingAddress->company_name ?? '');
            $output->shippingAddress = (string) ($shippingAddress->address ?? '');
            $output->shippingCountry = (string) ($shippingAddress->country ?? '');
            $output->shippingState = (string) ($shippingAddress->state ?? '');
            $output->shippingCity = (string) ($shippingAddress->city ?? '');
            $output->shippingPostcode = (string) ($shippingAddress->postcode ?? '');
            $output->shippingPhoneNumber = (string) ($shippingAddress->phone ?? '');
        }

        return $output;
    }

    /**
     * Fetch billing and shipping addresses for cart.
     */
    private function fetchAddresses($cart)
    {
        try {
            $output = new CheckoutAddressOutput;

            $output->id = $cart->id;
            $output->cartToken = $this->resolveCartToken($cart);
            $output->customerId = $cart->customer_id;

            $billingAddress = $cart->billing_address;
            if ($billingAddress) {
                $output->billingFirstName = $billingAddress->first_name;
                $output->billingLastName = $billingAddress->last_name;
                $output->billingEmail = $billingAddress->email;
                $output->billingCompanyName = $billingAddress->company_name;
                $output->billingAddress = $billingAddress->address;
                $output->billingCountry = $billingAddress->country;
                $output->billingState = $billingAddress->state;
                $output->billingCity = $billingAddress->city;
                $output->billingPostcode = $billingAddress->postcode;
                $output->billingPhoneNumber = $billingAddress->phone;
            }

            $shippingAddress = $cart->shipping_address;
            if ($shippingAddress) {
                $output->shippingFirstName = $shippingAddress->first_name;
                $output->shippingLastName = $shippingAddress->last_name;
                $output->shippingEmail = $shippingAddress->email;
                $output->shippingCompanyName = $shippingAddress->company_name;
                $output->shippingAddress = $shippingAddress->address;
                $output->shippingCountry = $shippingAddress->country;
                $output->shippingState = $shippingAddress->state;
                $output->shippingCity = $shippingAddress->city;
                $output->shippingPostcode = $shippingAddress->postcode;
                $output->shippingPhoneNumber = $shippingAddress->phone;
            }

            $output->success = true;
            $output->message = __('bagistoapi::app.graphql.address.retrieved');

            return $output;
        } catch (\Exception $exception) {
            throw new OperationFailedException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Shipping method output.
     */
    private function shippingMethodOutput(array $data): CheckoutShippingMethod
    {
        $output = new CheckoutShippingMethod;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }

    /**
     * Payment method output.
     */
    private function paymentMethodOutput(array $data): CheckoutPaymentMethod
    {
        $output = new CheckoutPaymentMethod;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }

    /**
     * Order output.
     */
    private function orderOutput(array $data): CheckoutOrder
    {
        $output = new CheckoutOrder;

        foreach ($data as $property => $value) {
            $output->{$property} = $value;
        }

        return $output;
    }
}
