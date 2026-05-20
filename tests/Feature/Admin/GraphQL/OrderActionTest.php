<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Role;

/**
 * GraphQL coverage for the per-order admin actions — starting with Reorder.
 */
class OrderActionTest extends AdminApiTestCase
{
    /** Resolve a reorderable order id from the listing, or skip. */
    protected function aReorderableOrderId(): ?int
    {
        $admin = $this->createAdmin();
        $rows = $this->adminGet($admin, '/api/admin/orders?per_page=20')->json('data');

        foreach ($rows ?? [] as $row) {
            if (! ($row['isGuest'] ?? true)) {
                return $row['id'];
            }
        }

        return null;
    }

    public function test_reorder_mutation_creates_a_draft_cart(): void
    {
        $id = $this->aReorderableOrderId();

        if ($id === null) {
            $this->markTestSkipped('No non-guest order available to reorder.');
        }

        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation reorder($input: createAdminReorderInput!) {
              createAdminReorder(input: $input) {
                adminReorder { success message cartId }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['orderId' => '/api/admin/orders/'.$id],
        ], $admin);

        $response->assertOk();
        $payload = $response->json('data.createAdminReorder.adminReorder');
        expect($payload)->toHaveKeys(['success', 'message', 'cartId']);

        if ($payload['success']) {
            expect($payload['cartId'])->toBeInt()->toBeGreaterThan(0);
        } else {
            expect($payload['cartId'])->toBeNull();
            expect($payload['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.cannot-reorder'));
        }
    }

    public function test_reorder_mutation_requires_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation reorder($input: createAdminReorderInput!) {
              createAdminReorder(input: $input) {
                adminReorder { success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['orderId' => '/api/admin/orders/1'],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }

    /** Run the reorder mutation for a given order id as the given admin. */
    protected function runReorder(int $orderId, $admin = null)
    {
        $mutation = <<<'GQL'
            mutation reorder($input: createAdminReorderInput!) {
              createAdminReorder(input: $input) {
                adminReorder { success message cartId }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, [
            'input' => ['orderId' => '/api/admin/orders/'.$orderId],
        ], $admin);
    }

    /** Edge case A1: guest orders -> errors[]. */
    public function test_reorder_mutation_rejects_guest_orders(): void
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
            $order = Order::query()->first();
            if (! $order) {
                $this->markTestSkipped('No orders to mark as guest.');
            }
            $order->is_guest = 1;
            $order->save();
            $guestId = $order->id;
        }

        $response = $this->runReorder($guestId, $admin);
        $errors = $response->json('errors');

        expect($errors)->not->toBeNull();
        expect($errors[0]['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.guest-not-supported'));
    }

    /** Edge case A2: items not saleable -> errors[]. */
    public function test_reorder_mutation_rejects_when_items_not_saleable(): void
    {
        $admin = $this->createAdmin();
        $id = $this->aReorderableOrderId();

        if ($id === null) {
            $this->markTestSkipped('No reorderable non-guest order available.');
        }

        $order = Order::with('items')->find($id);

        if (! $order || $order->items->isEmpty()) {
            $this->markTestSkipped('Reorderable order has no items.');
        }

        $productIds = $order->items->pluck('product_id')->filter()->unique()->all();

        if (empty($productIds)) {
            $this->markTestSkipped('Order items have no products to flip.');
        }

        $affected = \Illuminate\Support\Facades\DB::table('product_attribute_values')
            ->whereIn('product_id', $productIds)
            ->where('attribute_id', function ($q) {
                $q->select('id')->from('attributes')->where('code', 'status')->limit(1);
            })
            ->update(['boolean_value' => 0]);

        if ($affected === 0) {
            $this->markTestSkipped('Could not flip product status — schema differs.');
        }

        $response = $this->runReorder($order->id, $admin);
        $errors = $response->json('errors');

        expect($errors)->not->toBeNull();
        expect($errors[0]['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.items-not-saleable'));
    }

    /** Edge case B: admin lacks permission -> errors[]. */
    public function test_reorder_mutation_rejects_when_admin_lacks_permission(): void
    {
        $role = Role::create([
            'name'            => 'no-perm-gql-'.uniqid(),
            'description'     => 'No perms',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $id = $this->aReorderableOrderId() ?? Order::where('is_guest', 0)->value('id');

        if ($id === null) {
            $this->markTestSkipped('No non-guest order.');
        }

        $response = $this->runReorder($id, $admin);
        $errors = $response->json('errors');

        expect($errors)->not->toBeNull();
        expect($errors[0]['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.no-permission'));
    }

    /** Edge case C: reorder disabled in settings -> errors[]. */
    public function test_reorder_mutation_rejects_when_disabled_in_settings(): void
    {
        $admin = $this->createAdmin();
        $id = $this->aReorderableOrderId() ?? Order::where('is_guest', 0)->value('id');

        if ($id === null) {
            $this->markTestSkipped('No non-guest order.');
        }

        CoreConfig::create([
            'code'  => 'sales.order_settings.reorder.admin',
            'value' => '0',
        ]);

        $response = $this->runReorder($id, $admin);
        $errors = $response->json('errors');

        expect($errors)->not->toBeNull();
        expect($errors[0]['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.disabled-in-settings'));
    }
}
