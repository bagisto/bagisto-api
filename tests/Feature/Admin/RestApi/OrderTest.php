<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\User\Models\Role;

/**
 * REST coverage for the admin Orders listing — GET /api/admin/orders.
 *
 * Verifies the { data, meta } envelope, the 7 filters, date presets,
 * pagination, and auth.
 */
class OrderTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/orders')->assertStatus(401);
    }

    public function test_list_returns_data_meta_envelope(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/orders');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(
            ['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']
        );
    }

    public function test_per_page_caps_the_page_size(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/orders?per_page=5');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBe(5);
        expect(count($response->json('data')))->toBeLessThanOrEqual(5);
    }

    public function test_per_page_is_hard_capped_at_50(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/orders?per_page=500');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_list_row_has_slim_shape(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/orders?per_page=1');
        $response->assertOk();

        $rows = $response->json('data');

        if (empty($rows)) {
            $this->bootstrapAdminOrder('pending', false);
            $rows = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');
        }

        expect($rows)->not->toBeEmpty();
        expect($rows[0])->toHaveKeys([
            'id', 'incrementId', 'status', 'statusLabel', 'grandTotal',
            'formattedGrandTotal', 'channelName', 'customerEmail', 'items',
        ]);
        expect($rows[0]['items'])->toBeArray();

        if (! empty($rows[0]['items'])) {
            expect($rows[0]['items'][0])->toHaveKeys(['id', 'sku', 'name', 'qtyOrdered', 'productImage']);
        }
    }

    public function test_filter_by_order_id(): void
    {
        $admin = $this->createAdmin();

        $first = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');

        if (empty($first)) {
            $this->bootstrapAdminOrder('pending', false);
            $first = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');
        }

        $incrementId = $first[0]['incrementId'];

        $response = $this->adminGet($admin, '/api/admin/orders?order_id='.$incrementId);

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['incrementId'])->toContain($incrementId);
        }
    }

    public function test_filter_by_status(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/orders?status=processing&per_page=20');

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['status'])->toBe('processing');
        }
    }

    public function test_filter_by_email(): void
    {
        $admin = $this->createAdmin();

        $first = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');

        if (empty($first) || empty($first[0]['customerEmail'])) {
            $this->bootstrapAdminOrder('pending', false);
            $first = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');
        }

        $email = $first[0]['customerEmail'];

        $response = $this->adminGet($admin, '/api/admin/orders?email='.$email);

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect(strtolower($row['customerEmail']))->toContain(strtolower($email));
        }
    }

    public function test_date_preset_filter_is_accepted(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/orders?date_range=this_year')->assertOk();
        $this->adminGet($admin, '/api/admin/orders?date_range=today')->assertOk();
    }

    public function test_custom_date_range_filter_is_accepted(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet(
            $admin,
            '/api/admin/orders?date_from=2000-01-01&date_to='.now()->addDay()->toDateString()
        );

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    /**
     * The date presets must use the admin datagrid keys (last_three_months,
     * last_six_months — NOT last_3_months) AND apply the admin grid's range
     * semantics: "last three months" ends at the end of LAST month, so an order
     * created today is excluded. Under the old bug, the wrong key fell through to
     * "no date filter" and the order would wrongly appear.
     */
    public function test_date_preset_uses_admin_grid_keys_and_range(): void
    {
        $admin = $this->createAdmin();
        $order = $this->bootstrapAdminOrder('pending', false);
        // Unique increment_id so the order_id LIKE filter isolates exactly this
        // order from the rest of the dataset; created_at stays "now" (today).
        $inc = 'QADATE'.uniqid();
        $order->update(['increment_id' => $inc]);

        $today = $this->adminGet($admin, '/api/admin/orders?order_id='.$inc.'&date_range=today');
        $today->assertOk();
        expect(collect($today->json('data'))->pluck('incrementId'))->toContain($inc);

        $past = $this->adminGet($admin, '/api/admin/orders?order_id='.$inc.'&date_range=last_three_months');
        $past->assertOk();
        expect(collect($past->json('data'))->pluck('incrementId'))->not->toContain($inc);

        $this->adminGet($admin, '/api/admin/orders?date_range=last_six_months')->assertOk();
    }

    /**
     * Grand total filters the base_grand_total column with from/to range support
     * (matching the admin datagrid). The old listing only did an exact match on
     * the wrong (grand_total) column and ignored the range params entirely.
     */
    public function test_filter_by_grand_total_range_uses_base_grand_total(): void
    {
        $admin = $this->createAdmin();
        $order = $this->bootstrapAdminOrder('pending', false);
        $inc = 'QAGT'.uniqid();
        $order->update(['increment_id' => $inc, 'base_grand_total' => 4242, 'grand_total' => 4242]);

        $within = $this->adminGet($admin, '/api/admin/orders?order_id='.$inc.'&grand_total_from=4000&grand_total_to=5000');
        $within->assertOk();
        expect(collect($within->json('data'))->pluck('incrementId'))->toContain($inc);

        $below = $this->adminGet($admin, '/api/admin/orders?order_id='.$inc.'&grand_total_to=4000');
        $below->assertOk();
        expect(collect($below->json('data'))->pluck('incrementId'))->not->toContain($inc);
    }

    public function test_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/orders/export?format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('orders.csv');
        expect($response->getContent())->toContain('ID,Status,"Grand Total","Payment Method",Channel,Customer,Email,"Order Date"');
    }

    public function test_export_supports_xlsx(): void
    {
        $admin = $this->createAdmin();

        $response = $this->get('/api/admin/orders/export?format=xlsx', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ));

        $response->assertStatus(200);

        expect($response->headers->get('Content-Type'))->toContain('spreadsheetml');
        expect($response->headers->get('Content-Disposition'))->toContain('.xlsx');
    }

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/orders/export?format=pdf', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/orders/export', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    public function test_export_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->get('/api/admin/orders/export', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(403);
    }
}
