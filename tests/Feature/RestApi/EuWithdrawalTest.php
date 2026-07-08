<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\EUWithdrawal\Models\Withdrawal;
use Webkul\Sales\Models\Order;

class EuWithdrawalTest extends RestApiTestCase
{
    private function enable(Channel $channel): void
    {
        DB::table('core_config')->updateOrInsert(
            ['code' => 'sales.eu_withdrawal.general.enabled', 'channel_code' => $channel->code, 'locale_code' => null],
            ['value' => 1]
        );
        core()->getConfigData('sales.eu_withdrawal.general.enabled', $channel->code);
    }

    private function customerOrder($customer, Channel $channel): Order
    {
        return Order::factory()->create([
            'customer_id'    => $customer->id,
            'customer_email' => $customer->email,
            'channel_id'     => $channel->id,
            'is_guest'       => 0,
            'status'         => 'completed',
        ]);
    }

    public function test_customer_submit_creates_withdrawal(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel  = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order    = $this->customerOrder($customer, $channel);

        $response = $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', [
            'order_id' => $order->id, 'reason_text' => 'Changed my mind.',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect((int) $response->json('orderId'))->toBe($order->id);
        expect($response->json('status'))->toBe('received');
        expect($response->json('uuid'))->not->toBeNull();
        $this->assertDatabaseHas('eu_withdrawals', ['order_id' => $order->id, 'status' => 'received']);
    }

    public function test_customer_submit_is_idempotent(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel  = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order    = $this->customerOrder($customer, $channel);

        $first  = $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);
        $second = $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);

        expect($first->json('uuid'))->toBe($second->json('uuid'));
        expect(Withdrawal::where('order_id', $order->id)->count())->toBe(1);
    }

    public function test_customer_cannot_withdraw_another_customers_order(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $me    = $this->createCustomer();
        $other = $this->createCustomer();
        $order = $this->customerOrder($other, $channel);

        $response = $this->authenticatedPost($me, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);
        expect($response->getStatusCode())->toBeIn([403, 404]);
        $this->assertDatabaseMissing('eu_withdrawals', ['order_id' => $order->id]);
    }

    public function test_submit_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/shop/eu-withdrawals', ['order_id' => 1]);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_channel_disabled_returns_404(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel  = Channel::first();
        // not enabled
        $customer = $this->createCustomer();
        $order    = $this->customerOrder($customer, $channel);

        $response = $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);
        expect($response->getStatusCode())->toBeIn([403, 404]);
    }

    public function test_list_and_view_own(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel  = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order    = $this->customerOrder($customer, $channel);
        $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);

        $list = $this->authenticatedGet($customer, '/api/shop/eu-withdrawals');
        $list->assertOk();
        $row = $list->json('0');
        expect((int) $row['orderId'])->toBe($order->id);

        $get = $this->authenticatedGet($customer, '/api/shop/eu-withdrawals/'.$row['id']);
        $get->assertOk();
        expect((int) $get->json('orderId'))->toBe($order->id);
    }

    public function test_guest_submit_matches_by_increment_and_email(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);

        $order = Order::factory()->create([
            'customer_id'    => null,
            'customer_email' => 'guest@example.com',
            'increment_id'   => 'G-'.uniqid(),
            'channel_id'     => $channel->id,
            'is_guest'       => 1,
            'status'         => 'completed',
        ]);

        $response = $this->publicPost('/api/shop/eu-withdrawals/guest', [
            'order_increment_id' => $order->increment_id,
            'email'              => 'guest@example.com',
            'reason_text'        => 'No longer needed.',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('uuid'))->not->toBeNull();
        expect((bool) $response->json('isGuest'))->toBeTrue();
        $this->assertDatabaseHas('eu_withdrawals', ['order_id' => $order->id, 'is_guest' => 1]);
    }

    public function test_guest_submit_wrong_email_returns_404(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);

        $order = Order::factory()->create([
            'customer_id'    => null,
            'customer_email' => 'guest@example.com',
            'increment_id'   => 'G-'.uniqid(),
            'channel_id'     => $channel->id,
            'is_guest'       => 1,
            'status'         => 'completed',
        ]);

        $response = $this->publicPost('/api/shop/eu-withdrawals/guest', [
            'order_increment_id' => $order->increment_id,
            'email'              => 'wrong@example.com',
        ]);
        expect($response->getStatusCode())->toBeIn([403, 404]);
    }
}
