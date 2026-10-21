<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

class OrderCancelTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test cancel requires authentication.
     */
    public function test_cancel_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminCancelOrderInput!){ createAdminCancelOrder(input:$input){ adminOrderDetail { id } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => 1]]);
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }

    /**
     * Test cancel returns errors for unknown order.
     */
    public function test_cancel_returns_errors_for_unknown_order(): void
    {
        $admin = $this->createAdmin();
        $mutation = 'mutation($input: createAdminCancelOrderInput!){ createAdminCancelOrder(input:$input){ adminOrderDetail { id } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => 999999999]], $admin);
        expect($response->json('errors'))->toBeArray();
    }

    /**
     * The cancel mutation returns the order summary with its multi-word fields filled.
     */
    public function test_cancel_succeeds_on_cancellable(): void
    {
        $order = $this->bootstrapAdminOrder();

        $mutation = <<<'GQL'
            mutation($input: createAdminCancelOrderInput!) {
              createAdminCancelOrder(input: $input) {
                adminCancelOrder {
                  _id orderId incrementId status statusLabel grandTotal baseGrandTotal totalQtyOrdered success message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => $order->id]], $this->createAdmin());

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminCancelOrder.adminCancelOrder');

        expect($node['_id'])->toBe($order->id);
        expect($node['orderId'])->toBe($order->id);
        expect($node['incrementId'])->toBe((string) $order->increment_id);
        expect($node['status'])->toBe('canceled');
        expect($node['statusLabel'])->not->toBeNull();
        expect($node['grandTotal'])->not->toBeNull();
        expect($node['baseGrandTotal'])->not->toBeNull();
        expect($node['totalQtyOrdered'])->not->toBeNull();
        expect($node['success'])->toBeTrue();
        expect($node['message'])->toBe(trans('bagistoapi::app.admin.order.actions.cancel.success'));
    }
}
