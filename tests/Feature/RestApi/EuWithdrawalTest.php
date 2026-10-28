<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\EUWithdrawal\Models\Withdrawal;
use Webkul\Sales\Models\Order;
use Webkul\Shop\Mail\Customer\EUWithdrawal\GuestWithdrawalLink;

class EuWithdrawalTest extends RestApiTestCase
{
    private function enable(Channel $channel): void
    {
        DB::table('core_config')->updateOrInsert(
            ['code' => 'sales.eu_withdrawal.general.enabled', 'channel_code' => $channel->code, 'locale_code' => null],
            ['value' => 1]
        );

        $this->forgetCoreConfigCache();
    }

    private function customerOrder($customer, Channel $channel): Order
    {
        return Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => $channel->id,
            'is_guest' => 0,
            'status' => 'completed',
        ]);
    }

    public function test_customer_submit_creates_withdrawal(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order = $this->customerOrder($customer, $channel);

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
        $channel = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order = $this->customerOrder($customer, $channel);

        $first = $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);
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
        $me = $this->createCustomer();
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
        $channel = Channel::first();
        // not enabled
        $customer = $this->createCustomer();
        $order = $this->customerOrder($customer, $channel);

        $response = $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);
        expect($response->getStatusCode())->toBeIn([403, 404]);
    }

    public function test_list_and_view_own(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order = $this->customerOrder($customer, $channel);
        $this->authenticatedPost($customer, '/api/shop/eu-withdrawals', ['order_id' => $order->id]);

        $list = $this->authenticatedGet($customer, '/api/shop/eu-withdrawals');
        $list->assertOk();
        $row = $list->json('0');
        expect((int) $row['orderId'])->toBe($order->id);

        $get = $this->authenticatedGet($customer, '/api/shop/eu-withdrawals/'.$row['id']);
        $get->assertOk();
        expect((int) $get->json('orderId'))->toBe($order->id);
    }

    private function guestOrder(Channel $channel): Order
    {
        return Order::factory()->create([
            'customer_id' => null,
            'customer_email' => 'guest@example.com',
            'increment_id' => 'G-'.uniqid(),
            'channel_id' => $channel->id,
            'is_guest' => 1,
            'status' => 'completed',
        ]);
    }

    private function guestLink(Order $order, ?\DateTimeInterface $expires = null): string
    {
        return URL::temporarySignedRoute('shop.eu-withdrawal.guest.create', $expires ?? now()->addHours(24), ['orderId' => $order->id]);
    }

    public function test_guest_lookup_emails_a_link_when_the_order_matches(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);

        $response = $this->publicPost('/api/shop/eu-withdrawals/guest/lookup', [
            'order_increment_id' => $order->increment_id,
            'email' => 'guest@example.com',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
        Mail::assertQueued(GuestWithdrawalLink::class);
    }

    public function test_guest_lookup_gives_the_same_answer_when_nothing_matches(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);

        $match = $this->publicPost('/api/shop/eu-withdrawals/guest/lookup', [
            'order_increment_id' => $order->increment_id,
            'email' => 'guest@example.com',
        ]);

        $miss = $this->publicPost('/api/shop/eu-withdrawals/guest/lookup', [
            'order_increment_id' => $order->increment_id,
            'email' => 'wrong@example.com',
        ]);

        $miss->assertCreated();
        expect($miss->json())->toBe($match->json());
        Mail::assertQueuedCount(1);
    }

    public function test_guest_lookup_is_throttled(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $this->enable(Channel::first());

        foreach (range(1, 5) as $attempt) {
            $this->publicPost('/api/shop/eu-withdrawals/guest/lookup', ['order_increment_id' => 'x', 'email' => 'a@example.com'])->assertCreated();
        }

        $this->publicPost('/api/shop/eu-withdrawals/guest/lookup', ['order_increment_id' => 'x', 'email' => 'a@example.com'])->assertStatus(429);
    }

    public function test_guest_lookup_returns_404_when_the_feature_is_off(): void
    {
        $this->seedRequiredData();

        $this->publicPost('/api/shop/eu-withdrawals/guest/lookup', [
            'order_increment_id' => 'x',
            'email' => 'a@example.com',
        ])->assertNotFound();
    }

    public function test_guest_submit_with_a_signed_link_files_the_withdrawal(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);

        $response = $this->publicPost('/api/shop/eu-withdrawals/guest', [
            'link' => $this->guestLink($order),
            'reason_text' => 'No longer needed.',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('uuid'))->not->toBeNull();
        expect((bool) $response->json('isGuest'))->toBeTrue();
        $this->assertDatabaseHas('eu_withdrawals', ['order_id' => $order->id, 'is_guest' => 1]);
    }

    public function test_guest_submit_with_the_same_link_returns_the_existing_withdrawal(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);
        $link = $this->guestLink($order);

        $first = $this->publicPost('/api/shop/eu-withdrawals/guest', ['link' => $link]);
        $second = $this->publicPost('/api/shop/eu-withdrawals/guest', ['link' => $link]);

        expect($second->json('uuid'))->toBe($first->json('uuid'));
        expect(Withdrawal::where('order_id', $order->id)->count())->toBe(1);
    }

    public function test_guest_submit_without_a_link_is_refused(): void
    {
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);

        $response = $this->publicPost('/api/shop/eu-withdrawals/guest', [
            'order_increment_id' => $order->increment_id,
            'email' => 'guest@example.com',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('eu_withdrawals', ['order_id' => $order->id]);
    }

    public function test_guest_submit_with_a_tampered_link_is_refused(): void
    {
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);
        $other = $this->guestOrder($channel);

        $tampered = str_replace('/withdraw/'.$order->id.'/', '/withdraw/'.$other->id.'/', $this->guestLink($order));

        $this->publicPost('/api/shop/eu-withdrawals/guest', ['link' => $tampered])->assertStatus(422);
        $this->assertDatabaseMissing('eu_withdrawals', ['order_id' => $other->id]);
    }

    public function test_guest_submit_with_an_expired_link_is_refused(): void
    {
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->guestOrder($channel);

        $this->publicPost('/api/shop/eu-withdrawals/guest', [
            'link' => $this->guestLink($order, now()->subMinute()),
        ])->assertStatus(422);
    }

    public function test_guest_submit_refuses_a_customer_order(): void
    {
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = $this->customerOrder($this->createCustomer(), $channel);

        $this->publicPost('/api/shop/eu-withdrawals/guest', ['link' => $this->guestLink($order)])->assertNotFound();
    }
}
