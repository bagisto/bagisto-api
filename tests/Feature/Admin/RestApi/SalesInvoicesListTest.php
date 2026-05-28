<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

/**
 * REST coverage for GET /api/admin/invoices — the admin Sales → Invoices
 * datagrid listing.
 */
class SalesInvoicesListTest extends AdminApiTestCase
{
    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/invoices')->assertStatus(401);
    }

    public function test_list_returns_data_meta_envelope(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/invoices');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_per_page_caps_at_50(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/invoices?per_page=500');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_filter_by_state(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/invoices?state=paid');

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['state'])->toBe('paid');
        }
    }

    public function test_filter_by_grand_total_range(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/invoices?base_grand_total_from=0&base_grand_total_to=1000000');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    public function test_sort_default_is_id_desc(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/invoices?per_page=10');
        $response->assertOk();

        $ids = array_column($response->json('data'), 'id');
        $sorted = $ids;
        rsort($sorted);
        expect($ids)->toBe($sorted);
    }

    public function test_sort_asc(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/invoices?sort=id&order=asc&per_page=10');
        $response->assertOk();
        $ids = array_column($response->json('data'), 'id');
        $sorted = $ids;
        sort($sorted);
        expect($ids)->toBe($sorted);
    }

    public function test_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->adminGet($admin, '/api/admin/invoices');

        $response->assertStatus(403);
    }
}
