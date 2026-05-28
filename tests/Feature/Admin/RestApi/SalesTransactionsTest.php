<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\User\Models\Role;

class SalesTransactionsTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Bootstrap a transaction and return its id. */
    protected function aTransactionId(): int
    {
        $existing = \Webkul\Sales\Models\OrderTransaction::query()->value('id');
        if ($existing) {
            return $existing;
        }
        $order = $this->bootstrapOrderWithInvoice('processing');

        return $this->bootstrapOrderTransaction($order);
    }

    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/transactions')->assertStatus(401);
    }

    public function test_list_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/transactions');
        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_row_shape(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/transactions?per_page=1');
        $response->assertOk();
        $rows = $response->json('data');
        if (empty($rows)) {
            $this->aTransactionId();
            $rows = $this->adminGet($admin, '/api/admin/transactions?per_page=1')->json('data');
        }
        expect($rows)->not->toBeEmpty();
        expect($rows[0])->toHaveKeys(['id', 'transactionId', 'invoiceId', 'orderId', 'orderIncrementId', 'amount', 'status', 'createdAt']);
    }

    public function test_per_page_caps_at_50(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/transactions?per_page=500');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/transactions?status=paid');
        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['status'])->toBe('paid');
        }
    }

    public function test_detail_404_on_unknown_id(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/transactions/99999999')->assertStatus(404);
    }

    public function test_detail_returns_dto(): void
    {
        $admin = $this->createAdmin();
        $list = $this->adminGet($admin, '/api/admin/transactions?per_page=1')->json('data');
        if (empty($list)) {
            $this->aTransactionId();
            $list = $this->adminGet($admin, '/api/admin/transactions?per_page=1')->json('data');
        }
        $response = $this->adminGet($admin, '/api/admin/transactions/'.$list[0]['id']);
        $response->assertOk();
        expect($response->json())->toHaveKeys(['id', 'transactionId', 'amount', 'status', 'createdAt', 'order']);
    }

    /**
     * Regression — `OrderTransaction` does NOT declare an `order()` relation
     * in Bagisto core, so `OrderTransaction::with('order')` used to throw
     * "Call to undefined relationship [order]" (HTTP 500). Detail provider
     * now resolves the order manually via `Order::find($order_id)`.
     */
    public function test_detail_does_not_500_on_missing_order_relation(): void
    {
        $admin = $this->createAdmin();
        $list = $this->adminGet($admin, '/api/admin/transactions?per_page=1')->json('data');
        if (empty($list)) {
            $this->aTransactionId();
            $list = $this->adminGet($admin, '/api/admin/transactions?per_page=1')->json('data');
        }
        $response = $this->adminGet($admin, '/api/admin/transactions/'.$list[0]['id']);
        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        // The order block must be embedded inline.
        expect($response->json('order'))->toBeArray();
        expect($response->json('order'))->toHaveKeys(['id', 'incrementId']);
    }

    public function test_no_permission_returns_403(): void
    {
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->adminGet($admin, '/api/admin/transactions')->assertStatus(403);
    }
}
