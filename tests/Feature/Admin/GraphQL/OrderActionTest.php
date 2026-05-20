<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

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
}
