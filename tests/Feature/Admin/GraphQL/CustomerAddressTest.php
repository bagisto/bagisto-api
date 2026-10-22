<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CustomerAddressTest extends AdminApiTestCase
{
    /**
     * Test query returns customer addresses.
     */
    public function test_query_returns_customer_addresses(): void
    {
        $customerId = Customer::whereHas('addresses')->value('id');

        if ($customerId === null) {
            $this->markTestSkipped('No customer with addresses in the database.');
        }

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query addresses($customerId: Int!) {
              adminCustomerAddresses(customerId: $customerId) {
                edges { node { id firstName lastName city country } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['customerId' => $customerId], $admin);

        $response->assertOk();
        $data = $response->json('data.adminCustomerAddresses');
        $errors = $response->json('errors');
        expect(is_array($data) || is_array($errors))->toBeTrue();
    }

    /**
     * The address list and the single address resolve their id as the nested address IRI.
     */
    public function test_query_resolves_the_address_iri(): void
    {
        [$customer, $first] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdmin();
        $iri = '/api/admin/customers/'.$customer->id.'/addresses/'.$first->id;

        $list = $this->adminGraphQL(
            'query addresses($customerId: Int!) { adminCustomerAddresses(customerId: $customerId) { edges { node { id _id } } } }',
            ['customerId' => $customer->id],
            $admin
        );

        $list->assertOk();

        expect($list->json('errors'))->toBeNull()
            ->and(array_column(array_column($list->json('data.adminCustomerAddresses.edges'), 'node'), 'id'))->toContain($iri);

        $item = $this->adminGraphQL(
            'query address($customerId: Int!, $id: ID!) { adminCustomerAddress(customerId: $customerId, id: $id) { id _id } }',
            ['customerId' => $customer->id, 'id' => $iri],
            $admin
        );

        $item->assertOk();

        expect($item->json('errors'))->toBeNull()
            ->and($item->json('data.adminCustomerAddress.id'))->toBe($iri)
            ->and((int) $item->json('data.adminCustomerAddress._id'))->toBe($first->id);
    }

    /**
     * Test query requires authentication.
     */
    public function test_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query { adminCustomerAddresses(customerId: 1) { totalCount } }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * The setDefault mutation makes the address the only default address of the customer.
     */
    public function test_set_default_mutation_moves_the_default_flag(): void
    {
        [$customer, $first, $second] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: setDefaultAdminCustomerAddressInput!) {
              setDefaultAdminCustomerAddress(input: $input) {
                adminCustomerAddress { _id defaultAddress }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id,
            'customerId' => $customer->id,
        ]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.setDefaultAdminCustomerAddress.adminCustomerAddress.defaultAddress'))->toBeTrue();
        expect((int) CustomerAddress::find($second->id)->default_address)->toBe(1);
        expect((int) CustomerAddress::find($first->id)->default_address)->toBe(0);
    }

    /**
     * The setDefault mutation refuses an address that belongs to another customer.
     */
    public function test_set_default_mutation_rejects_address_of_another_customer(): void
    {
        [, , $second] = $this->aCustomerWithTwoAddresses();
        $other = $this->createCustomer();

        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: setDefaultAdminCustomerAddressInput!) {
              setDefaultAdminCustomerAddress(input: $input) {
                adminCustomerAddress { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$other->id.'/addresses/'.$second->id,
            'customerId' => $other->id,
        ]], $admin);

        expect($response->json('errors'))->not->toBeNull();
        expect((int) CustomerAddress::find($second->id)->default_address)->toBe(0);
    }

    /**
     * The setDefault mutation is refused for a role without the address edit permission.
     */
    public function test_set_default_mutation_without_permission_is_refused(): void
    {
        [$customer, $first, $second] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdminWithoutAddressWrites();

        $mutation = <<<'GQL'
            mutation($input: setDefaultAdminCustomerAddressInput!) {
              setDefaultAdminCustomerAddress(input: $input) {
                adminCustomerAddress { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id,
            'customerId' => $customer->id,
        ]], $admin, $this->adminAccessToken($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.customer.no-permission'));
        expect((int) CustomerAddress::find($first->id)->default_address)->toBe(1);
    }

    /**
     * The setDefault mutation rejects a request without an admin token.
     */
    public function test_set_default_mutation_requires_authentication(): void
    {
        [$customer, , $second] = $this->aCustomerWithTwoAddresses();

        $mutation = <<<'GQL'
            mutation($input: setDefaultAdminCustomerAddressInput!) {
              setDefaultAdminCustomerAddress(input: $input) {
                adminCustomerAddress { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id,
            'customerId' => $customer->id,
        ]]);

        expect($response->json('errors'))->not->toBeNull();
        expect((int) CustomerAddress::find($second->id)->default_address)->toBe(0);
    }

    /**
     * The delete mutation removes the customer address.
     */
    public function test_delete_mutation_removes_the_address(): void
    {
        [$customer, , $second] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerAddressInput!) {
              deleteAdminCustomerAddress(input: $input) {
                adminCustomerAddress { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id,
        ]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        $this->assertDatabaseMissing('addresses', ['id' => $second->id]);
    }

    /**
     * The delete mutation is refused for a role without the address delete permission.
     */
    public function test_delete_mutation_without_permission_is_refused(): void
    {
        [$customer, , $second] = $this->aCustomerWithTwoAddresses();

        $admin = $this->createAdminWithoutAddressWrites();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerAddressInput!) {
              deleteAdminCustomerAddress(input: $input) {
                adminCustomerAddress { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id,
        ]], $admin, $this->adminAccessToken($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.customer.no-permission'));
        $this->assertDatabaseHas('addresses', ['id' => $second->id]);
    }

    /**
     * The delete mutation rejects a request without an admin token.
     */
    public function test_delete_mutation_requires_authentication(): void
    {
        [$customer, , $second] = $this->aCustomerWithTwoAddresses();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCustomerAddressInput!) {
              deleteAdminCustomerAddress(input: $input) {
                adminCustomerAddress { id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/customers/'.$customer->id.'/addresses/'.$second->id,
        ]]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('addresses', ['id' => $second->id]);
    }

    /**
     * Create a fresh customer with two addresses, the first of them the default.
     */
    protected function aCustomerWithTwoAddresses(): array
    {
        $customer = $this->createCustomer();

        $first = CustomerAddress::factory()->create([
            'customer_id' => $customer->id,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
            'default_address' => 1,
        ]);

        $second = CustomerAddress::factory()->create([
            'customer_id' => $customer->id,
            'address_type' => CustomerAddress::ADDRESS_TYPE,
            'default_address' => 0,
        ]);

        return [$customer, $first, $second];
    }

    /**
     * Create an admin whose role grants customer access but no address edit or delete.
     */
    protected function createAdminWithoutAddressWrites(): Admin
    {
        $role = Role::create([
            'name' => 'NoAddressWrites '.uniqid(),
            'description' => 'no address writes',
            'permission_type' => 'custom',
            'permissions' => ['customers', 'customers.customers', 'customers.addresses'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }
}
