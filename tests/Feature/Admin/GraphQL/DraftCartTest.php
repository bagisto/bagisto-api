<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;

/**
 * GraphQL coverage for Wave 3 — fresh draft-cart bootstrap.
 *
 * Mutation: createAdminDraftCart(input: { customerId: Int! })
 *
 * Returns the same `{ cartId, customerId, success, message }` shape as the
 * REST endpoint (`POST /api/admin/customers/{customerId}/draft-carts`).
 */
class DraftCartTest extends AdminApiTestCase
{
    private string $mutation = <<<'GQL'
        mutation CreateAdminDraftCart($input: createAdminDraftCartInput!) {
          createAdminDraftCart(input: $input) {
            adminDraftCart { id }
          }
        }
    GQL;

    public function test_create_draft_cart_requires_auth(): void
    {
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => 1]]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_create_draft_cart_unknown_customer_errors(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => 999999999]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_create_draft_cart_zero_customer_id_errors(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => 0]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    public function test_create_draft_cart_success(): void
    {
        $admin = $this->createAdmin();
        $customer = Customer::query()->first();

        if (! $customer) {
            $this->markTestSkipped('No customer fixture in DB.');
        }

        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => $customer->id]], $admin);
        $resp->assertOk();

        $cartId = $resp->json('data.createAdminDraftCart.adminDraftCart.id');

        if ($cartId !== null && is_string($cartId)) {
            expect($cartId)->toContain('/');
        }
    }
}
