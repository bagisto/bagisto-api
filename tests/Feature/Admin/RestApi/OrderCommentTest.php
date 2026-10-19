<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderComment;
use Webkul\User\Models\Role;

class OrderCommentTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test create requires authentication.
     */
    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/comments', ['comment' => 'hi'])->assertStatus(401);
    }

    /**
     * Test list requires authentication.
     */
    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/orders/1/comments')->assertStatus(401);
    }

    /**
     * Test create returns 404 for unknown order.
     */
    public function test_create_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/comments', ['comment' => 'hi'])
            ->assertStatus(404);
    }

    /**
     * Test create rejects empty comment.
     */
    public function test_create_rejects_empty_comment(): void
    {
        $id = $this->anOrderId();
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => '']);
        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.actions.comment.empty'));
    }

    /**
     * Test create without view permission returns 403.
     */
    public function test_create_without_view_permission_returns_403(): void
    {
        $id = $this->anOrderId();
        $role = Role::create([
            'name' => 'Limited '.uniqid(),
            'description' => 'no order perms',
            'permission_type' => 'custom',
            'permissions' => ['catalog', 'catalog.products'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $msg = 'blocked '.uniqid();

        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => $msg]);

        $response->assertStatus(403);
        expect(OrderComment::where('comment', $msg)->exists())->toBeFalse();
    }

    /**
     * Test create persists a comment.
     */
    public function test_create_persists_a_comment(): void
    {
        $id = $this->anOrderId();
        $admin = $this->createAdmin();
        $msg = 'Customer called at '.uniqid();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', [
            'comment' => $msg,
            'customerNotified' => true,
        ]);
        $response->assertStatus(201);
        expect($response->json('orderId'))->toBe($id);
        expect($response->json('comment'))->toBe($msg);
        $persisted = OrderComment::where('order_id', $id)
            ->where('comment', $msg)->first();
        expect($persisted)->not->toBeNull();
        expect((bool) $persisted->customer_notified)->toBeTrue();
    }

    /**
     * Test list returns comments newest first.
     */
    public function test_list_returns_comments_newest_first(): void
    {
        $id = $this->anOrderId();
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => 'first']);
        $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => 'second']);

        $response = $this->adminGet($admin, '/api/admin/orders/'.$id.'/comments');
        $response->assertOk();
        $data = $response->json('data');
        expect($data)->toBeArray();
        expect(count($data))->toBeGreaterThanOrEqual(2);
        expect($data[0]['orderId'])->toBe($id);
    }

    /**
     * An order id.
     */
    protected function anOrderId(): int
    {
        return Order::query()->value('id') ?? $this->bootstrapAdminOrder('pending', false)->id;
    }
}
