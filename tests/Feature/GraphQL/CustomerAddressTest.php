<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\Customer;

/**
 * Tests for customer address GraphQL operations:
 * - createAddUpdateCustomerAddress (create + update)
 * - getCustomerAddresses (list)
 * - createDeleteCustomerAddress (delete)
 *
 * All tests use freshly-created dummy data, no pre-existing records.
 */
class CustomerAddressTest extends GraphQLTestCase
{
    // ─── Helper methods ─────────────────────────────────────────────────

    /**
     * Create a customer and return [customer, bearer token].
     */
    private function authCustomer(): array
    {
        $customer = $this->createCustomer();
        $token = $customer->createToken('test-token')->plainTextToken;

        return ['customer' => $customer, 'token' => $token];
    }

    /**
     * Build a valid address input payload with dummy data.
     */
    private function dummyAddressInput(array $overrides = []): array
    {
        return array_merge([
            'firstName'      => 'Alice',
            'lastName'       => 'Tester',
            'email'          => 'address_'.uniqid().'@example.com',
            'phone'          => '5551234567',
            'address1'       => '123 Test Street',
            'address2'       => 'Suite 200',
            'city'           => 'Test City',
            'state'          => 'CA',
            'country'        => 'US',
            'postcode'       => '90001',
            'useForShipping' => true,
            'defaultAddress' => false,
        ], $overrides);
    }

    /**
     * Create an address via GraphQL and return its ID.
     */
    private function createAddressViaApi(string $token, array $input = []): int
    {
        $mutation = <<<'GQL'
            mutation createAddress($input: createAddUpdateCustomerAddressInput!) {
              createAddUpdateCustomerAddress(input: $input) {
                addUpdateCustomerAddress {
                  id
                  addressId
                  firstName
                  lastName
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => $this->dummyAddressInput($input),
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful();

        $data = $response->json('data.createAddUpdateCustomerAddress.addUpdateCustomerAddress');
        $this->assertNotNull($data, 'Address creation response is null');

        return (int) ($data['addressId'] ?? $data['id']);
    }

    // ─── Create address ─────────────────────────────────────────────────

    public function test_create_customer_address_successfully(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->authCustomer();

        $mutation = <<<'GQL'
            mutation createAddress($input: createAddUpdateCustomerAddressInput!) {
              createAddUpdateCustomerAddress(input: $input) {
                addUpdateCustomerAddress {
                  id
                  _id
                  addressId
                  firstName
                  lastName
                  email
                  phone
                  address1
                  address2
                  city
                  state
                  country
                  postcode
                  useForShipping
                  defaultAddress
                }
              }
            }
        GQL;

        $input = $this->dummyAddressInput([
            'firstName' => 'Charlie',
            'lastName'  => 'Newton',
        ]);

        $response = $this->graphQL($mutation, [
            'input' => $input,
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful();
        $this->assertArrayNotHasKey('errors', $response->json());

        $data = $response->json('data.createAddUpdateCustomerAddress.addUpdateCustomerAddress');
        $this->assertNotNull($data);
        $this->assertSame('Charlie', $data['firstName']);
        $this->assertSame('Newton', $data['lastName']);
        $this->assertSame('Test City', $data['city']);
        $this->assertSame('US', $data['country']);

        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'first_name'  => 'Charlie',
            'last_name'   => 'Newton',
            'city'        => 'Test City',
        ]);
    }

    public function test_create_address_fails_without_authentication(): void
    {
        $mutation = <<<'GQL'
            mutation createAddress($input: createAddUpdateCustomerAddressInput!) {
              createAddUpdateCustomerAddress(input: $input) {
                addUpdateCustomerAddress { id }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => $this->dummyAddressInput(),
        ]);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Unauthenticated address creation should fail');
    }

    // ─── Update address ─────────────────────────────────────────────────

    public function test_update_existing_customer_address(): void
    {
        ['token' => $token] = $this->authCustomer();

        $addressId = $this->createAddressViaApi($token, ['firstName' => 'Original']);

        $mutation = <<<'GQL'
            mutation updateAddress($input: createAddUpdateCustomerAddressInput!) {
              createAddUpdateCustomerAddress(input: $input) {
                addUpdateCustomerAddress {
                  id
                  addressId
                  firstName
                  city
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => $this->dummyAddressInput([
                'addressId' => $addressId,
                'firstName' => 'Updated',
                'city'      => 'New City',
            ]),
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful();

        $data = $response->json('data.createAddUpdateCustomerAddress.addUpdateCustomerAddress');
        $this->assertNotNull($data);
        $this->assertSame('Updated', $data['firstName']);
        $this->assertSame('New City', $data['city']);

        $this->assertDatabaseHas('addresses', [
            'id'         => $addressId,
            'first_name' => 'Updated',
            'city'       => 'New City',
        ]);
    }

    // ─── Get addresses ──────────────────────────────────────────────────

    public function test_get_customer_addresses_returns_empty_list_for_new_customer(): void
    {
        ['token' => $token] = $this->authCustomer();

        $query = <<<'GQL'
            query getAddresses {
              getCustomerAddresses(first: 10) {
                edges {
                  node {
                    id
                    _id
                    firstName
                    lastName
                    city
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [], ['Authorization' => 'Bearer '.$token]);
        $response->assertSuccessful();

        $this->assertArrayNotHasKey('errors', $response->json());

        $edges = $response->json('data.getCustomerAddresses.edges');
        $this->assertIsArray($edges);
        $this->assertCount(0, $edges, 'New customer should have no addresses');
    }

    public function test_get_customer_addresses_returns_created_addresses(): void
    {
        ['token' => $token] = $this->authCustomer();

        // Create two addresses
        $this->createAddressViaApi($token, ['firstName' => 'First', 'city' => 'City One']);
        $this->createAddressViaApi($token, ['firstName' => 'Second', 'city' => 'City Two']);

        $query = <<<'GQL'
            query getAddresses {
              getCustomerAddresses(first: 10) {
                edges {
                  node {
                    id
                    _id
                    firstName
                    city
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [], ['Authorization' => 'Bearer '.$token]);
        $response->assertSuccessful();

        $edges = $response->json('data.getCustomerAddresses.edges');
        $this->assertCount(2, $edges, 'Should return both addresses');

        $names = array_column(array_column($edges, 'node'), 'firstName');
        $this->assertContains('First', $names);
        $this->assertContains('Second', $names);
    }

    public function test_get_customer_addresses_fails_without_authentication(): void
    {
        $query = <<<'GQL'
            query getAddresses {
              getCustomerAddresses(first: 10) {
                edges { node { id } }
              }
            }
        GQL;

        $response = $this->graphQL($query);
        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Unauthenticated address listing should fail');
    }

    public function test_get_customer_addresses_only_returns_own_addresses(): void
    {
        // Customer A creates an address
        ['token' => $tokenA] = $this->authCustomer();
        $this->createAddressViaApi($tokenA, ['firstName' => 'Alpha', 'city' => 'City A']);

        // Customer B creates an address
        ['token' => $tokenB] = $this->authCustomer();
        $this->createAddressViaApi($tokenB, ['firstName' => 'Beta', 'city' => 'City B']);

        // Customer A queries — should only see their own
        $query = <<<'GQL'
            query getAddresses {
              getCustomerAddresses(first: 10) {
                edges { node { firstName city } }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], ['Authorization' => 'Bearer '.$tokenA]);
        $response->assertSuccessful();

        $edges = $response->json('data.getCustomerAddresses.edges');
        $this->assertCount(1, $edges, 'Customer A should only see their own address');
        $this->assertSame('Alpha', $edges[0]['node']['firstName']);
    }

    // ─── Delete address ─────────────────────────────────────────────────

    public function test_delete_customer_address_successfully(): void
    {
        ['customer' => $customer, 'token' => $token] = $this->authCustomer();

        $addressId = $this->createAddressViaApi($token, ['firstName' => 'ToDelete']);

        $this->assertDatabaseHas('addresses', [
            'id'          => $addressId,
            'customer_id' => $customer->id,
        ]);

        $mutation = <<<'GQL'
            mutation deleteAddress($input: createDeleteCustomerAddressInput!) {
              createDeleteCustomerAddress(input: $input) {
                deleteCustomerAddress {
                  id
                  addressId
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => ['addressId' => $addressId],
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful();
        $this->assertArrayNotHasKey('errors', $response->json());

        $this->assertDatabaseMissing('addresses', ['id' => $addressId]);
    }

    public function test_delete_address_fails_without_authentication(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token);

        $mutation = <<<'GQL'
            mutation deleteAddress($input: createDeleteCustomerAddressInput!) {
              createDeleteCustomerAddress(input: $input) {
                deleteCustomerAddress { id }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => ['addressId' => $addressId],
        ]);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Unauthenticated delete should fail');
    }

    public function test_delete_fails_for_another_customers_address(): void
    {
        // Customer A creates an address
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA, ['firstName' => 'Owner']);

        // Customer B tries to delete it
        ['token' => $tokenB] = $this->authCustomer();

        $mutation = <<<'GQL'
            mutation deleteAddress($input: createDeleteCustomerAddressInput!) {
              createDeleteCustomerAddress(input: $input) {
                deleteCustomerAddress { id }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => ['addressId' => $addressId],
        ], ['Authorization' => 'Bearer '.$tokenB]);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Customer B should not be allowed to delete Customer A\'s address');

        // Address should still exist
        $this->assertDatabaseHas('addresses', ['id' => $addressId]);
    }
}
