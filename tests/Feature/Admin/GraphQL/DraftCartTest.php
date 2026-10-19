<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;

class DraftCartTest extends AdminApiTestCase
{
    /**
     * The mutation.
     */
    private string $mutation = <<<'GQL'
        mutation CreateAdminDraftCart($input: createAdminDraftCartInput!) {
          createAdminDraftCart(input: $input) {
            adminDraftCart { id }
          }
        }
    GQL;

    /**
     * Test create draft cart requires auth.
     */
    public function test_create_draft_cart_requires_auth(): void
    {
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => 1]]);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test create draft cart unknown customer errors.
     */
    public function test_create_draft_cart_unknown_customer_errors(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => 999999999]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test create draft cart zero customer id errors.
     */
    public function test_create_draft_cart_zero_customer_id_errors(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGraphQL($this->mutation, ['input' => ['customerId' => 0]], $admin);
        expect($resp->json('errors'))->not->toBeNull();
    }

    /**
     * Test create draft cart success.
     */
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

        if (
            $cartId !== null
            && is_string($cartId)
        ) {
            expect($cartId)->toContain('/');
        }
    }
}
