<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Sales\Models\Order;

class EuWithdrawalTest extends AdminApiTestCase
{
    private function createWithdrawal(string $status = 'received'): int
    {
        $channel = Channel::first();
        $order = Order::factory()->create([
            'customer_id' => null, 'customer_email' => 'w@example.com',
            'increment_id' => 'WG-'.uniqid(), 'channel_id' => $channel->id,
            'is_guest' => 1, 'status' => 'completed',
        ]);

        return DB::table('eu_withdrawals')->insertGetId([
            'uuid' => (string) Str::uuid(), 'order_id' => $order->id,
            'customer_id' => null, 'is_guest' => 1, 'customer_email' => 'w@example.com',
            'channel_id' => $channel->id, 'locale' => 'en', 'received_at' => now(),
            'status' => $status, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_list_and_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createWithdrawal();

        $list = $this->adminGraphQL('query { adminEuWithdrawals { edges { node { _id status } } } }', [], $admin);
        $list->assertOk();
        expect($list->json('data.adminEuWithdrawals.edges'))->toBeArray();

        $detail = <<<GQL
            query { adminEuWithdrawal(id: "/api/admin/eu-withdrawals/{$id}") { _id status customerEmail } }
        GQL;
        $d = $this->adminGraphQL($detail, [], $admin);
        $d->assertOk();
        expect($d->json('data.adminEuWithdrawal.status'))->toBe('received');
    }

    public function test_decline(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createWithdrawal();
        $mutation = <<<GQL
            mutation {
              declineAdminEuWithdrawal(input: {id: "/api/admin/eu-withdrawals/{$id}", declinedReason: "no"}) {
                adminEuWithdrawal { _id status declinedReason }
              }
            }
        GQL;
        $response = $this->adminGraphQL($mutation, [], $admin);
        $response->assertOk();
        $node = $response->json('data.declineAdminEuWithdrawal.adminEuWithdrawal');
        expect($node['status'])->toBe('declined');
        expect($node['declinedReason'])->toBe('no');
    }

    public function test_mark_refunded_and_resend(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $id = $this->createWithdrawal();

        $refund = <<<GQL
            mutation {
              markRefundedAdminEuWithdrawal(input: {id: "/api/admin/eu-withdrawals/{$id}", refundNote: "r1"}) {
                adminEuWithdrawal { _id status refundNote }
              }
            }
        GQL;
        $r = $this->adminGraphQL($refund, [], $admin);
        $r->assertOk();
        expect($r->json('data.markRefundedAdminEuWithdrawal.adminEuWithdrawal.status'))->toBe('refunded');

        $resend = <<<GQL
            mutation {
              resendConfirmationAdminEuWithdrawal(input: {id: "/api/admin/eu-withdrawals/{$id}"}) {
                adminEuWithdrawal { _id message }
              }
            }
        GQL;
        $re = $this->adminGraphQL($resend, [], $admin);
        $re->assertOk();
        expect($re->json('data.resendConfirmationAdminEuWithdrawal.adminEuWithdrawal.message'))->not->toBeNull();
    }
}
