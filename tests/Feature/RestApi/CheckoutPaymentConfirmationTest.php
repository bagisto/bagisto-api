<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use ApiPlatform\Metadata\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Dto\CheckoutPaymentConfirmationInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Http\Controllers\StripeCheckoutController;
use Webkul\BagistoApi\Models\CheckoutPaymentConfirmation;
use Webkul\BagistoApi\Repositories\GuestCartTokensRepository;
use Webkul\BagistoApi\State\CheckoutPaymentConfirmationProcessor;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Models\Order;
use Webkul\Stripe\Payment\Stripe;

class CheckoutPaymentConfirmationTest extends RestApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    private function guestPostWithToken(string $url, string $token, array $payload): TestResponse
    {
        return $this->withHeaders([
            ...$this->storefrontHeaders(),
            'Authorization' => 'Bearer '.$token,
        ])->postJson($url, $payload);
    }

    private function createStripeCart(): array
    {
        $this->seedRequiredData();

        $cart = app(CartRepository::class)->create([
            'is_guest' => 1,
            'is_active' => 1,
            'channel_id' => core()->getCurrentChannel()->id,
            'global_currency_code' => core()->getBaseCurrencyCode(),
            'base_currency_code' => core()->getBaseCurrencyCode(),
            'channel_currency_code' => core()->getChannelBaseCurrencyCode(),
            'cart_currency_code' => core()->getCurrentCurrencyCode(),
            'grand_total' => 100,
            'base_grand_total' => 100,
        ]);
        DB::table('cart_payment')->insert([
            'cart_id' => $cart->id,
            'method' => 'stripe',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cartId = (int) $cart->id;
        $cartToken = app(GuestCartTokensRepository::class)->createToken($cartId)->token;

        return [$cartId, $cartToken];
    }

    private function stripeSession(int $cartId, float $baseGrandTotal = 100): object
    {
        $decimal = core()->getBaseCurrency()->decimal ?? 2;

        return (object) [
            'id' => 'cs_test_paid',
            'metadata' => (object) ['cart_id' => $cartId],
            'currency' => strtolower(core()->getBaseCurrencyCode()),
            'amount_total' => (int) round($baseGrandTotal * (10 ** $decimal)),
        ];
    }

    private function fakeStripeSession(string $sessionId, ?object $session): void
    {
        $this->mock(Stripe::class)
            ->shouldReceive('retrieveCheckoutSession')
            ->once()
            ->with($sessionId)
            ->andReturn($session);
    }

    private function processConfirmation(string $cartToken, ?string $sessionId): CheckoutPaymentConfirmation
    {
        $request = Request::create('/api/shop/checkout-payments/confirm', 'POST');
        $request->headers->set('Authorization', 'Bearer '.$cartToken);
        \Illuminate\Support\Facades\Request::swap($request);

        $input = new CheckoutPaymentConfirmationInput;
        $input->sessionId = $sessionId;

        return app(CheckoutPaymentConfirmationProcessor::class)->process($input, new Post);
    }

    public function test_confirm_payment_settles_a_paid_session_for_the_bearer_cart(): void
    {
        [$cartId, $cartToken] = $this->createStripeCart();

        $session = $this->stripeSession($cartId);
        $this->fakeStripeSession('cs_test_paid', $session);

        $order = new Order;
        $order->id = 1729;

        $this->mock(StripeCheckoutController::class)
            ->shouldReceive('settle')
            ->once()
            ->with($session)
            ->andReturn($order);

        $confirmation = $this->processConfirmation($cartToken, 'cs_test_paid');

        expect($confirmation->success)->toBeTrue()
            ->and($confirmation->cartId)->toBe($cartId)
            ->and($confirmation->orderId)->toBe('1729');
    }

    public function test_confirm_payment_returns_the_order_already_placed_for_the_cart(): void
    {
        [$cartId, $cartToken] = $this->createStripeCart();

        $this->fakeStripeSession('cs_test_paid', $this->stripeSession($cartId));

        $orderId = Order::factory()->create([
            'is_guest' => 1,
            'cart_id' => $cartId,
        ])->id;

        $this->mock(StripeCheckoutController::class)
            ->shouldNotReceive('settle');

        $confirmation = $this->processConfirmation($cartToken, 'cs_test_paid');

        expect($confirmation->orderId)->toBe((string) $orderId);
    }

    public function test_confirm_payment_requires_a_session_id(): void
    {
        [, $cartToken] = $this->createStripeCart();

        $response = $this->guestPostWithToken('/api/shop/checkout-payments/confirm', $cartToken, []);

        $response->assertUnprocessable();
    }

    public function test_confirm_payment_rejects_a_session_of_another_cart(): void
    {
        [, $cartToken] = $this->createStripeCart();
        [$otherCartId] = $this->createStripeCart();

        $this->fakeStripeSession('cs_test_other_cart', $this->stripeSession($otherCartId));

        $this->mock(StripeCheckoutController::class)
            ->shouldNotReceive('settle');

        expect(fn () => $this->processConfirmation($cartToken, 'cs_test_other_cart'))
            ->toThrow(InvalidInputException::class);
    }

    public function test_confirm_payment_rejects_a_session_whose_amount_differs_from_the_cart(): void
    {
        [$cartId, $cartToken] = $this->createStripeCart();

        $this->fakeStripeSession('cs_test_paid', $this->stripeSession($cartId, 1));

        $this->mock(StripeCheckoutController::class)
            ->shouldNotReceive('settle');

        expect(fn () => $this->processConfirmation($cartToken, 'cs_test_paid'))
            ->toThrow(InvalidInputException::class);
    }

    public function test_confirm_payment_rejects_an_unknown_session(): void
    {
        [, $cartToken] = $this->createStripeCart();

        $this->fakeStripeSession('cs_test_missing', null);

        expect(fn () => $this->processConfirmation($cartToken, 'cs_test_missing'))
            ->toThrow(InvalidInputException::class);
    }
}
