<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class SalesBookingsTest extends AdminApiTestCase
{
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
}
