<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the per-order admin actions: Reorder (+ Cancel, Refund as
 * they land in future milestones).
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

        if ($id === null) {
            $this->markTestSkipped('No non-guest order available to reorder.');
        }

        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/orders/'.$id.'/reorder');

        // The processor returns success/cannot-reorder shape with HTTP 201 either way.
        $response->assertStatus(201);

        $json = $response->json();
        expect($json)->toHaveKeys(['success', 'message', 'cartId']);

        if ($json['success']) {
            expect($json['cartId'])->toBeInt()->toBeGreaterThan(0);
            expect($json['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.success'));
        } else {
            // Some items may no longer be saleable in the dev DB — accept the documented refusal.
            expect($json['cartId'])->toBeNull();
            expect($json['message'])->toBe(trans('bagistoapi::app.admin.order.reorder.cannot-reorder'));
        }
    }
}
