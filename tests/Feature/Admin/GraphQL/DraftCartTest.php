<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

class DraftCartTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

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
     * The draft cart mutation returns the new cart id and the customer id.
     */
    public function test_create_draft_cart_success(): void
    {
        $customer = $this->findOrCreateCustomer();

        $mutation = <<<'GQL'
            mutation CreateAdminDraftCart($input: createAdminDraftCartInput!) {
              createAdminDraftCart(input: $input) {
                adminDraftCart { id cartId customerId success message }
              }
            }
        GQL;

        $resp = $this->adminGraphQL($mutation, ['input' => ['customerId' => $customer->id]], $this->createAdmin());

        expect($resp->json('errors'))->toBeNull();

        $node = $resp->json('data.createAdminDraftCart.adminDraftCart');

        expect($node['cartId'])->toBeInt()->toBeGreaterThan(0);
        expect($node['customerId'])->toBe($customer->id);
        expect($node['success'])->toBeTrue();
        expect($node['id'])->toContain('/');
        $this->assertDatabaseHas('cart', [
            'id' => $node['cartId'],
            'customer_id' => $customer->id,
        ]);
    }
}
