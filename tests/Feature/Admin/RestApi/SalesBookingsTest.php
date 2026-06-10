<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class SalesBookingsTest extends AdminApiTestCase
{
    public function test_detail_returns_full_payload(): void
    {
        $bookingId = DB::table('bookings')->value('id');
        if (! $bookingId) {
            $this->markTestSkipped('No booking rows seeded in the test database.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/bookings/'.$bookingId);
        $response->assertOk();
        expect($response->json())->toHaveKeys([
            'id', 'orderId', 'orderIncrementId', 'orderItemId', 'productId', 'productSku',
            'productName', 'bookingType', 'qty', 'from', 'to', 'fromFormatted', 'toFormatted',
            'bookingProductEventTicketId', 'order', 'orderItem', 'createdAt',
            'paymentMethod', 'paymentTitle', 'shippingMethod', 'shippingTitle',
            'billingAddress', 'shippingAddress', 'invoices', 'shipments', 'refunds',
        ]);
        expect($response->json('id'))->toBe((int) $bookingId);
        expect($response->json('invoices'))->toBeArray();
        expect($response->json('shipments'))->toBeArray();
        expect($response->json('refunds'))->toBeArray();
    }

    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/bookings')->assertStatus(401);
    }

    public function test_list_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/bookings');
        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_per_page_caps_at_50(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/bookings?per_page=500');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_sort_default_id_desc(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/bookings?per_page=10');
        $response->assertOk();
        $ids = array_column($response->json('data'), 'id');
        $sorted = $ids;
        rsort($sorted);
        expect($ids)->toBe($sorted);
    }

    public function test_filter_by_qty(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/bookings?qty=1');
        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect((int) $row['qty'])->toBe(1);
        }
    }

    public function test_detail_404_on_unknown_id(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/bookings/99999999')->assertStatus(404);
    }

    public function test_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->adminGet($admin, '/api/admin/bookings')->assertStatus(403);
    }

    public function test_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/bookings/export?format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('bookings.csv');
        expect($response->getContent())->toContain('ID,"Order ID",Qty,From,To,"Booking Date"');
    }

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/bookings/export?format=xlsx', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/bookings/export', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    public function test_export_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->get('/api/admin/bookings/export', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(403);
    }
}
