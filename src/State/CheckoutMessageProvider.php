<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\RateLimiter;
use Webkul\BagistoApi\Dto\CheckoutMessageOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Facades\CartTokenFacade;
use Webkul\BagistoApi\Facades\TokenHeaderFacade;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Sales\Contracts\Order as OrderContract;
use Webkul\Sales\Repositories\OrderRepository;

class CheckoutMessageProvider implements ProviderInterface
{
    /**
     * Attempts allowed per minute and IP, as each one is a paid Magic AI request.
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Create a new provider instance.
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected GuestCartTokensRepository $guestCartTokensRepository,
    ) {}

    /**
     * Provide the checkout success message for the order in the route.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CheckoutMessageOutput
    {
        $orderId = (int) ($uriVariables['orderId'] ?? $context['args']['orderId'] ?? request()->route('orderId'));

        return $this->provideForOrder($orderId);
    }

    /**
     * Generate the checkout success message for an order placed with the bearer token, as core's
     * success page does, leaving it null when the feature is off or Magic AI fails.
     */
    public function provideForOrder(int $orderId): CheckoutMessageOutput
    {
        $token = TokenHeaderFacade::getAuthorizationBearerToken(request());

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.cart.authentication-required'));
        }

        $key = 'checkout-message:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.rate-limit.too-many-attempts', ['seconds' => RateLimiter::availableIn($key)]), 429);
        }

        RateLimiter::hit($key, 60);

        $order = $this->orderRepository->find($orderId);

        if (
            ! $order
            || ! $this->placedWithToken($order, $token)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.customer-order.not-found', ['id' => $orderId]));
        }

        $output = new CheckoutMessageOutput;

        if (
            ! core()->getConfigData('magic_ai.general.settings.enabled')
            || ! core()->getConfigData('magic_ai.storefront_features.checkout_message.enabled')
        ) {
            return $output;
        }

        try {
            $output->message = (string) MagicAI::checkoutMessage($order);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $output;
    }

    /**
     * Whether the order came from the guest token's cart or belongs to the token's customer.
     */
    private function placedWithToken(OrderContract $order, string $token): bool
    {
        if ($guestToken = $this->guestCartTokensRepository->findByToken($token)) {
            return (int) $guestToken->cart_id === (int) $order->cart_id;
        }

        $customer = CartTokenFacade::getCustomerByToken($token);

        return $customer
            && (int) $order->customer_id === (int) $customer->id;
    }
}
