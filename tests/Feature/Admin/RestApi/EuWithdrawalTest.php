<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\Channel;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Role;

class EuWithdrawalTest extends AdminApiTestCase
{
    private function createWithdrawal(string $status = 'received'): array
    {
        $channel = Channel::first();
        $order = Order::factory()->create([
            'customer_id' => null, 'customer_email' => 'w@example.com',
            'increment_id' => 'W-'.uniqid(), 'channel_id' => $channel->id,
            'is_guest' => 1, 'status' => 'completed',
        ]);

        $id = DB::table('eu_withdrawals')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'customer_id' => null,
            'is_guest' => 1,
            'customer_email' => 'w@example.com',
            'channel_id' => $channel->id,
            'locale' => 'en',
            'reason_text' => 'changed mind',
            'received_at' => now(),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['id' => $id, 'order' => $order];
    }

    public function test_list(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();
        $response = $this->adminGet($admin, '/api/admin/eu-withdrawals');
        $response->assertOk();
        expect(collect($response->json('data'))->firstWhere('id', $w['id']))->not->toBeNull();
    }

    public function test_list_filter_status(): void
    {
        $admin = $this->createAdmin();
        $this->createWithdrawal('received');
        $ref = $this->createWithdrawal('refunded');
        $response = $this->adminGet($admin, '/api/admin/eu-withdrawals?status=refunded');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('status')->unique()->all();
        expect($ids)->toBe(['refunded']);
    }

    public function test_get_detail(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();
        $response = $this->adminGet($admin, '/api/admin/eu-withdrawals/'.$w['id']);
        $response->assertOk();
        expect((int) $response->json('id'))->toBe($w['id']);
        expect($response->json('status'))->toBe('received');
        expect($response->json('customerEmail'))->toBe('w@example.com');
    }

    public function test_decline(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();
        $response = $this->adminPost($admin, '/api/admin/eu-withdrawals/'.$w['id'].'/decline', [
            'declined_reason' => 'Outside cooling-off period.',
        ]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('status'))->toBe('declined');
        expect($response->json('declinedReason'))->toBe('Outside cooling-off period.');
        $this->assertDatabaseHas('eu_withdrawals', ['id' => $w['id'], 'status' => 'declined']);
    }

    public function test_decline_requires_reason(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();
        $response = $this->adminPost($admin, '/api/admin/eu-withdrawals/'.$w['id'].'/decline', []);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mark_refunded(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal('declined');
        $response = $this->adminPost($admin, '/api/admin/eu-withdrawals/'.$w['id'].'/mark-refunded', [
            'refund_note' => 'refund #555',
        ]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('status'))->toBe('refunded');
        expect($response->json('refundNote'))->toBe('refund #555');
        // decline metadata cleared
        $this->assertDatabaseHas('eu_withdrawals', ['id' => $w['id'], 'status' => 'refunded', 'declined_reason' => null]);
    }

    public function test_resend_confirmation(): void
    {
        Mail::fake();
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();
        $response = $this->adminPost($admin, '/api/admin/eu-withdrawals/'.$w['id'].'/resend-confirmation', []);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('message'))->not->toBeNull();
        $this->assertDatabaseHas('eu_withdrawals', ['id' => $w['id']]);

        $withJunkObject = $this->adminPost($admin, '/api/admin/eu-withdrawals/'.$w['id'].'/resend-confirmation', ['unexpected' => 'value']);
        expect($withJunkObject->getStatusCode())->toBeIn([200, 201]);

        $withRawString = $this->call(
            'POST',
            '/api/admin/eu-withdrawals/'.$w['id'].'/resend-confirmation',
            [], [], [],
            $this->transformHeadersToServerVars($this->adminHeaders($admin)) + ['CONTENT_TYPE' => 'application/json'],
            '"string"'
        );
        expect($withRawString->getStatusCode())->toBeIn([200, 201]);
    }

    public function test_requires_auth(): void
    {
        $w = $this->createWithdrawal();
        $response = $this->getJson('/api/admin/eu-withdrawals/'.$w['id']);
        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();

        $response = $this->get('/api/admin/eu-withdrawals/export?format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('eu-withdrawals.csv');
        expect($response->getContent())->toContain('"Received At",Order,"Customer Email",Status,Channel,"Confirmation Sent",Reference');
        expect($response->getContent())->toContain($w['order']->increment_id);
    }

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $w = $this->createWithdrawal();

        $this->get('/api/admin/eu-withdrawals/export?format=pdf', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $w = $this->createWithdrawal();

        $this->get('/api/admin/eu-withdrawals/export', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    public function test_export_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $w = $this->createWithdrawal();

        $this->get('/api/admin/eu-withdrawals/export', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(403);
    }

    public function test_export_honours_the_status_filter(): void
    {
        $admin = $this->createAdmin();
        $received = $this->createWithdrawal('received');
        $declined = $this->createWithdrawal('declined');

        $content = $this->get('/api/admin/eu-withdrawals/export?format=csv&status=declined', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertOk()->getContent();

        expect($content)->toContain($declined['order']->increment_id);
        expect($content)->not->toContain($received['order']->increment_id);
    }
}
