<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Role;

/**
 * REST coverage for POST /api/admin/orders/{id}/cancel.
 */
class OrderCancelTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Find or create a cancellable order. Never returns null. */
    protected function aCancellableOrderId(): int
    {
        return $this->findOrCreateCancellableOrder()->id;
    }

    /** Find or create any order. Used by tests that will flip status. */
    protected function anyOrderId(): int
    {
        $id = Order::query()->value('id');

        return $id ?: $this->bootstrapAdminOrder('pending', false)->id;
    }

    public function test_cancel_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/cancel')->assertStatus(401);
    }

    public function test_cancel_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/cancel')->assertStatus(404);
    }

    public function test_cancel_rejects_closed_orders_with_422(): void
    {
        $id = $this->anyOrderId();
        Order::where('id', $id)->update(['status' => Order::STATUS_CLOSED]);

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/cancel');
        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.actions.cancel.closed'));
    }

    public function test_cancel_rejects_fraud_orders_with_422(): void
    {
        $id = $this->anyOrderId();
        Order::where('id', $id)->update(['status' => Order::STATUS_FRAUD]);

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/cancel');
        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.actions.cancel.fraud'));
    }

    public function test_cancel_rejects_when_admin_lacks_permission(): void
    {
        $id = $this->aCancellableOrderId();
        if (! $id) {
            $this->markTestSkipped('No cancellable order available to exercise the permission gate.');
        }

        $role = Role::create([
            'name' => 'no-cancel-'.uniqid(),
            'description' => 'No perms',
            'permission_type' => 'custom',
            'permissions' => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminTokenSameAsWeb($admin);

        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/cancel', [], $token);
        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.actions.cancel.no-permission'));
    }

    public function test_cancel_succeeds_on_a_cancellable_order(): void
    {
        $id = $this->aCancellableOrderId();

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/cancel');
        $response->assertStatus(201);
        expect($response->json('id'))->toBe($id);
        expect($response->json('status'))->toBeIn(['canceled', 'closed']);
    }
}
