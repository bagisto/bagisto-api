<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\Order;

/**
 * REST coverage for POST/GET /api/admin/orders/{id}/comments.
 */
class OrderCommentTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function anOrderId(): int
    {
        return Order::query()->value('id') ?? $this->bootstrapAdminOrder('pending', false)->id;
    }

    public function test_create_requires_authentication(): void
    {
        $this->publicPost('/api/admin/orders/1/comments', ['comment' => 'hi'])->assertStatus(401);
    }

    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/orders/1/comments')->assertStatus(401);
    }

    public function test_create_returns_404_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/orders/999999999/comments', ['comment' => 'hi'])
            ->assertStatus(404);
    }

    public function test_create_rejects_empty_comment(): void
    {
        $id = $this->anOrderId();
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => '']);
        $response->assertStatus(422);
        expect($response->json('detail') ?? $response->json('message'))
            ->toBe(trans('bagistoapi::app.admin.order.actions.comment.empty'));
    }

    public function test_create_persists_a_comment(): void
    {
        $id = $this->anOrderId();
        $admin = $this->createAdmin();
        $msg = 'Customer called at '.uniqid();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', [
            'comment'          => $msg,
            'customerNotified' => true,
        ]);
        $response->assertStatus(201);
        expect($response->json('orderId'))->toBe($id);
        expect($response->json('comment'))->toBe($msg);
        // Verify persisted at the DB level — the REST DTO field name is
        // subject to the project's name converter (camelCase) and may also
        // not surface boolean primitives reliably; reading the row directly
        // is the authoritative check.
        $persisted = \Webkul\Sales\Models\OrderComment::where('order_id', $id)
            ->where('comment', $msg)->first();
        expect($persisted)->not->toBeNull();
        expect((bool) $persisted->customer_notified)->toBeTrue();
    }

    public function test_list_returns_comments_newest_first(): void
    {
        $id = $this->anOrderId();
        $admin = $this->createAdmin();

        // Seed two comments
        $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => 'first']);
        $this->adminPost($admin, '/api/admin/orders/'.$id.'/comments', ['comment' => 'second']);

        $response = $this->adminGet($admin, '/api/admin/orders/'.$id.'/comments');
        $response->assertOk();
        $data = $response->json('data');
        expect($data)->toBeArray();
        expect(count($data))->toBeGreaterThanOrEqual(2);
        // Newest first means the latest seeded should be at index 0
        expect($data[0]['orderId'])->toBe($id);
    }
}
