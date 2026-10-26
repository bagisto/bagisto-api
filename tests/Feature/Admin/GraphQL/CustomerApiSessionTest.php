<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class CustomerApiSessionTest extends AdminApiTestCase
{
    /**
     * Test adminCustomerApiSessions lists a customer's live sessions with their multi-word fields.
     */
    public function test_sessions_are_listed(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $session = $this->customerTokenPair($customer)->session;

        $response = $this->adminGraphQL(
            'query ($customerId: Int) { adminCustomerApiSessions(customer_id: $customerId, first: 50) { edges { node { _id customerId customerEmail clientType expiresAt } } } }',
            ['customerId' => $customer->id],
            $admin,
        );

        $this->assertNull($response->json('errors'));

        $node = collect($response->json('data.adminCustomerApiSessions.edges'))->pluck('node')->firstWhere('_id', $session->id);

        $this->assertNotNull($node);
        $this->assertSame($customer->id, $node['customerId']);
        $this->assertSame($customer->email, $node['customerEmail']);
        $this->assertNotNull($node['expiresAt']);
    }

    /**
     * Test deleteAdminCustomerApiSession ends one session.
     */
    public function test_one_session_is_revoked(): void
    {
        $admin = $this->createAdmin();
        $session = $this->customerTokenPair($this->createCustomer())->session;

        $response = $this->adminGraphQL(
            'mutation ($input: deleteAdminCustomerApiSessionInput!) { deleteAdminCustomerApiSession(input: $input) { adminCustomerApiSession { _id revokedReason } } }',
            ['input' => ['id' => '/api/admin/customers/api-sessions/'.$session->id]],
            $admin,
        );

        $this->assertNull($response->json('errors'));
        $this->assertSame($session->id, $response->json('data.deleteAdminCustomerApiSession.adminCustomerApiSession._id'));
        $this->assertSame(CustomerApiSession::REVOKED_BY_ADMIN, $session->fresh()->revoked_reason);
    }

    /**
     * Test createAdminCustomerApiSessionRevoke ends every session of a customer.
     */
    public function test_every_session_of_a_customer_is_revoked(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $this->customerTokenPair($customer);
        $this->customerTokenPair($customer);

        $response = $this->adminGraphQL(
            'mutation ($input: createAdminCustomerApiSessionRevokeInput!) { createAdminCustomerApiSessionRevoke(input: $input) { adminCustomerApiSessionRevoke { customerId revoked message } } }',
            ['input' => ['customerId' => $customer->id]],
            $admin,
        );

        $this->assertNull($response->json('errors'));
        $this->assertSame(2, $response->json('data.createAdminCustomerApiSessionRevoke.adminCustomerApiSessionRevoke.revoked'));
        $this->assertSame(0, CustomerApiSession::query()->where('customer_id', $customer->id)->whereNull('revoked_at')->count());
    }

    /**
     * Test a role without the delete permission cannot revoke.
     */
    public function test_revoking_needs_the_delete_permission(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['customers', 'customers.api_sessions']);
        $session = $this->customerTokenPair($this->createCustomer())->session;

        $response = $this->adminGraphQL(
            'mutation ($input: deleteAdminCustomerApiSessionInput!) { deleteAdminCustomerApiSession(input: $input) { adminCustomerApiSession { _id } } }',
            ['input' => ['id' => '/api/admin/customers/api-sessions/'.$session->id]],
            $viewer,
        );

        $this->assertNotNull($response->json('errors'));
        $this->assertNull($session->fresh()->revoked_at);
    }
}
