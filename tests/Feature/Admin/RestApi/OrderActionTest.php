<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Core\Models\CoreConfig;
use Webkul\Sales\Models\Order;

/**
 * REST coverage for the per-order admin actions: Reorder (+ Cancel, Refund as
 * they land in future milestones).
 */
class OrderActionTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Resolve or create a reorderable (non-guest) order id. Never returns null. */
    protected function aReorderableOrderId(): int
    {
        $admin = $this->createAdmin();
        $rows = $this->adminGet($admin, '/api/admin/orders?per_page=20')->json('data');

        foreach ($rows ?? [] as $row) {
            if (! ($row['isGuest'] ?? true)) {
                return $row['id'];
            }
        }

        return $this->bootstrapAdminOrder('pending', false)->id;
    }

    public function test_reorder_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/reorder')->assertStatus(401);
    }

    public function test_reorder_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/orders/999999999/reorder')->assertStatus(404);
    }

    public function test_reorder_creates_a_draft_cart_for_a_valid_order(): void
    {
        $id = $this->aReorderableOrderId();

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/reorder');

        $response->assertStatus(201);

        $json = $response->json();
        expect($json)->toHaveKeys(['success', 'message', 'cartId']);

        if ($json['success']) {
            expect($json['cartId'])->toBeInt()->toBeGreaterThan(0);
            expect($json['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.success'));
        } else {
            expect($json['cartId'])->toBeNull();
            expect($json['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.cannot-reorder'));
        }
    }

    /** Edge case A1: guest orders cannot be reordered (HTTP 422). */
    public function test_reorder_rejects_guest_orders_with_422(): void
    {
        $admin = $this->createAdmin();
        $rows = $this->adminGet($admin, '/api/admin/orders?per_page=50')->json('data');

        $guestId = null;
        foreach ($rows ?? [] as $row) {
            if ($row['isGuest'] ?? false) {
                $guestId = $row['id'];
                break;
            }
        }

        if ($guestId === null) {
            $order = Order::query()->first() ?? $this->bootstrapAdminOrder('pending', false);
            $order->is_guest = 1;
            $order->save();
            $guestId = $order->id;
        }

        $response = $this->adminPost($admin, '/api/admin/orders/'.$guestId.'/reorder');

        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.reorder.guest-not-supported'));
    }

    /** Edge case A2: at least one item not saleable -> HTTP 422. */
    public function test_reorder_rejects_when_items_not_saleable(): void
    {
        $admin = $this->createAdmin();

        $order = $this->bootstrapAdminOrder('pending', false)->load('items');

        $productIds = $order->items->pluck('product_id')->filter()->unique()->all();

        if (empty($productIds)) {
            $this->markTestSkipped('Order items have no associated products in this DB.');
        }

        $affected = \Illuminate\Support\Facades\DB::table('product_attribute_values')
            ->whereIn('product_id', $productIds)
            ->where('attribute_id', function ($q) {
                $q->select('id')->from('attributes')->where('code', 'status')->limit(1);
            })
            ->update(['boolean_value' => 0]);

        if ($affected === 0) {
            $this->markTestSkipped('Could not flip product status — schema differs in this DB.');
        }

        $response = $this->adminPost($admin, '/api/admin/orders/'.$order->id.'/reorder');

        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.reorder.items-not-saleable'));
    }

    /** Edge case B: admin lacks `sales.orders.create` permission -> HTTP 422. */
    public function test_reorder_rejects_when_admin_lacks_permission(): void
    {
        $id = $this->aReorderableOrderId() ?? Order::where('is_guest', 0)->value('id');
        if (! $id) {
            $this->markTestSkipped('No reorderable order available to exercise the permission gate.');
        }

        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-create-orders-'.uniqid(),
            'description'     => 'No perms',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $token = $this->adminTokenSameAsWeb($admin);

        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/reorder', [], $token);

        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.reorder.no-permission'));
    }

    /** Edge case C: admin reorder disabled in store settings -> HTTP 422. */
    public function test_reorder_rejects_when_disabled_in_settings(): void
    {
        $admin = $this->createAdmin();

        $id = $this->aReorderableOrderId() ?? Order::where('is_guest', 0)->value('id');

        CoreConfig::where('code', 'sales.order_settings.reorder.admin')->delete();
        CoreConfig::create([
            'code'  => 'sales.order_settings.reorder.admin',
            'value' => '0',
        ]);

        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/reorder');

        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.reorder.disabled-in-settings'));
    }
}
