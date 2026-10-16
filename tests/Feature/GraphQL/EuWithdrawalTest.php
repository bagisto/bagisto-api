<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

        $this->forgetCoreConfigCache();
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

        $mutation = <<<'GQL'
            mutation guest($input: createGuestEuWithdrawalInput!) {
              createGuestEuWithdrawal(input: $input) {
                guestEuWithdrawal {
                  _id
                  uuid
                  orderId
                  isGuest
                }
              }
            }
        GQL;
        $response = $this->graphQL($mutation, [
            'input' => [
                'link' => URL::temporarySignedRoute('shop.eu-withdrawal.guest.create', now()->addHours(24), ['orderId' => $order->id]),
                'reasonText' => 'x',
            ],
        ]);
        $this->assertNull($response->json('errors'));
        $response->assertOk();
        $node = $response->json('data.createGuestEuWithdrawal.guestEuWithdrawal');
        expect((int) $node['orderId'])->toBe($order->id);
        expect((bool) $node['isGuest'])->toBeTrue();
    }

    public function test_guest_create_without_a_valid_link_returns_an_error(): void
    {
        $this->seedRequiredData();
        $this->enable(Channel::first());

        $mutation = <<<'GQL'
            mutation guest($input: createGuestEuWithdrawalInput!) {
              createGuestEuWithdrawal(input: $input) {
                guestEuWithdrawal {
                  _id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['link' => 'https://shop.example.com/withdraw/1/create']]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_guest_lookup_returns_a_constant_answer(): void
    {
        Mail::fake();
        $this->seedRequiredData();
        $this->enable(Channel::first());

        $mutation = <<<'GQL'
            mutation lookup($input: createGuestEuWithdrawalLinkInput!) {
              createGuestEuWithdrawalLink(input: $input) {
                guestEuWithdrawalLink {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['orderIncrementId' => 'none', 'email' => 'a@example.com']]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createGuestEuWithdrawalLink.guestEuWithdrawalLink.success'))->toBeTrue();
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->graphQL('mutation { createEuWithdrawal(input: {orderId: 1}) { euWithdrawal { _id } } }');
        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
