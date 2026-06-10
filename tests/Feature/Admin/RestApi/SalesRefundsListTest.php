<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\User\Models\Role;

class SalesRefundsListTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/refunds')->assertStatus(401);
    }

    public function test_list_returns_data_meta_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/refunds');
        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_row_shape(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/refunds?per_page=1');
        $response->assertOk();
        $rows = $response->json('data');
        if (empty($rows)) {
            $this->bootstrapOrderWithRefund();
            $rows = $this->adminGet($admin, '/api/admin/refunds?per_page=1')->json('data');
        }
        expect($rows)->not->toBeEmpty();
        expect($rows[0])->toHaveKeys(['id', 'orderId', 'orderIncrementId', 'state', 'baseGrandTotal', 'formattedBaseGrandTotal', 'billedTo', 'createdAt']);
    }

    public function test_per_page_caps_at_50(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/refunds?per_page=500');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_filter_by_state(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/refunds?state=refunded');
        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['state'])->toBe('refunded');
        }
    }

    public function test_sort_default_id_desc(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/refunds?per_page=10');
        $response->assertOk();
        $ids = array_column($response->json('data'), 'id');
        $sorted = $ids;
        rsort($sorted);
        expect($ids)->toBe($sorted);
    }

    public function test_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->adminGet($admin, '/api/admin/refunds')->assertStatus(403);
    }

    public function test_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/refunds/export?format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('refunds.csv');
        expect($response->getContent())->toContain('ID,"Order ID","Refunded Amount","Billed To","Refund Date"');
    }

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/refunds/export?format=xlsx', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/refunds/export', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    public function test_export_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->get('/api/admin/refunds/export', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(403);
    }
}
