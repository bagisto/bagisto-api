<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Sales\Models\Order;

class EuWithdrawalTest extends GraphQLTestCase
{
    private function enable(Channel $channel): void
    {
        DB::table('core_config')->updateOrInsert(
            ['code' => 'sales.eu_withdrawal.general.enabled', 'channel_code' => $channel->code, 'locale_code' => null],
            ['value' => 1]
        );
    }

    public function test_customer_create_and_list(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $customer = $this->createCustomer();
        $order = Order::factory()->create([
            'customer_id' => $customer->id, 'customer_email' => $customer->email,
            'channel_id' => $channel->id, 'is_guest' => 0, 'status' => 'completed',
        ]);

        $mutation = <<<GQL
            mutation {
              createEuWithdrawal(input: {orderId: {$order->id}, reasonText: "changed mind"}) {
                euWithdrawal { _id uuid orderId status isGuest }
              }
            }
        GQL;
        $response = $this->authenticatedGraphQL($customer, $mutation);
        $response->assertOk();
        $node = $response->json('data.createEuWithdrawal.euWithdrawal');
        expect((int) $node['orderId'])->toBe($order->id);
        expect($node['status'])->toBe('received');
        expect($node['uuid'])->not->toBeNull();

        $list = $this->authenticatedGraphQL($customer, 'query { euWithdrawals { edges { node { _id orderId } } } }');
        $list->assertOk();
        expect($list->json('data.euWithdrawals.edges'))->toBeArray();
    }

    public function test_guest_create(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $channel = Channel::first();
        $this->enable($channel);
        $order = Order::factory()->create([
            'customer_id' => null, 'customer_email' => 'g@example.com',
            'increment_id' => 'GQ-'.uniqid(), 'channel_id' => $channel->id,
            'is_guest' => 1, 'status' => 'completed',
        ]);

        $mutation = <<<GQL
            mutation {
              createGuestEuWithdrawal(input: {orderIncrementId: "{$order->increment_id}", email: "g@example.com", reasonText: "x"}) {
                guestEuWithdrawal { _id uuid orderId isGuest }
              }
            }
        GQL;
        $response = $this->graphQL($mutation);
        $response->assertOk();
        $node = $response->json('data.createGuestEuWithdrawal.guestEuWithdrawal');
        expect((int) $node['orderId'])->toBe($order->id);
        expect((bool) $node['isGuest'])->toBeTrue();
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->graphQL('mutation { createEuWithdrawal(input: {orderId: 1}) { euWithdrawal { _id } } }');
        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
