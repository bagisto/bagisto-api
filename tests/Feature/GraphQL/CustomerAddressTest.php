<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\Customer;

class CustomerAddressTest extends GraphQLTestCase
{
    // ============================================================================
    // Create Address
    // ============================================================================

    /**
     * Test create customer address successfully.
     */
    public function test_create_customer_address_successfully(): void
    {
        [
            'customer' => $customer,
            'token' => $token,
        ] = $this->authCustomer();

        $mutation = <<<'GQL'
            mutation createAddress($input: createAddUpdateCustomerAddressInput!) {
              createAddUpdateCustomerAddress(input: $input) {
                addUpdateCustomerAddress {
                  id
                  _id
                  addressId
                  firstName
                  lastName
                  companyName
                  vatId
                  email
                  phone
                  address1
                  address2
                  city
                  state
                  country
                  postcode
                  defaultAddress
                }
              }
            }
        GQL;

        $input = $this->dummyAddressInput([
            'firstName' => 'Charlie',
            'lastName' => 'Newton',
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
        $this->assertSame('Acme Inc.', $data['companyName']);
        $this->assertSame('GB123456789', $data['vatId']);
        $this->assertSame('Test City', $data['city']);
        $this->assertSame('US', $data['country']);

        $this->assertDatabaseHas('addresses', [
            'customer_id' => $customer->id,
            'first_name' => 'Charlie',
            'last_name' => 'Newton',
            'company_name' => 'Acme Inc.',
            'vat_id' => 'GB123456789',
            'city' => 'Test City',
        ]);
    }

    /**
     * Test create address fails without authentication.
     */
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

    // ============================================================================
    // Update Address
    // ============================================================================

    /**
     * Test update existing customer address.
     */
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
                  companyName
                  vatId
                  city
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => $this->dummyAddressInput([
                'addressId' => $addressId,
                'firstName' => 'Updated',
                'companyName' => 'Updated Corp.',
                'vatId' => 'DE987654321',
                'city' => 'New City',
            ]),
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful();

        $data = $response->json('data.createAddUpdateCustomerAddress.addUpdateCustomerAddress');
        $this->assertNotNull($data);
        $this->assertSame('Updated', $data['firstName']);
        $this->assertSame('Updated Corp.', $data['companyName']);
        $this->assertSame('DE987654321', $data['vatId']);
        $this->assertSame('New City', $data['city']);

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'first_name' => 'Updated',
            'company_name' => 'Updated Corp.',
            'vat_id' => 'DE987654321',
            'city' => 'New City',
        ]);
    }

    // ============================================================================
    // Get Addresses
    // ============================================================================

    /**
     * Test get customer addresses returns empty list for new customer.
     */
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

    /**
     * Test get customer addresses returns created addresses.
     */
    public function test_get_customer_addresses_returns_created_addresses(): void
    {
        ['token' => $token] = $this->authCustomer();

        $this->createAddressViaApi($token, [
            'firstName' => 'First',
            'city' => 'City One',
        ]);
        $this->createAddressViaApi($token, [
            'firstName' => 'Second',
            'city' => 'City Two',
        ]);

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

    /**
     * Test get customer addresses sort created at desc returns newest first.
     */
    public function test_get_customer_addresses_sort_created_at_desc_returns_newest_first(): void
    {
        ['token' => $token] = $this->authCustomer();

        $firstId = $this->createAddressViaApi($token, ['firstName' => 'First']);
        $secondId = $this->createAddressViaApi($token, ['firstName' => 'Second']);

        $query = <<<'GQL'
            query getAddresses {
              getCustomerAddresses(first: 10, sort: "created_at-desc") {
                edges {
                  node {
                    _id
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, [], ['Authorization' => 'Bearer '.$token]);
        $response->assertSuccessful();

        $edges = $response->json('data.getCustomerAddresses.edges');
        $this->assertSame($secondId, (int) $edges[0]['node']['_id']);
        $this->assertSame($firstId, (int) $edges[1]['node']['_id']);
    }

    /**
     * Test get customer addresses fails without authentication.
     */
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

    /**
     * Test get customer addresses only returns own addresses.
     */
    public function test_get_customer_addresses_only_returns_own_addresses(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $this->createAddressViaApi($tokenA, [
            'firstName' => 'Alpha',
            'city' => 'City A',
        ]);

        ['token' => $tokenB] = $this->authCustomer();
        $this->createAddressViaApi($tokenB, [
            'firstName' => 'Beta',
            'city' => 'City B',
        ]);

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

    // ============================================================================
    // Delete Address
    // ============================================================================

    /**
     * Test delete customer address successfully.
     */
    public function test_delete_customer_address_successfully(): void
    {
        [
            'customer' => $customer,
            'token' => $token,
        ] = $this->authCustomer();

        $addressId = $this->createAddressViaApi($token, ['firstName' => 'ToDelete']);

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
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

    /**
     * Test delete address fails without authentication.
     */
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

    /**
     * Test delete fails for another customers address.
     */
    public function test_delete_fails_for_another_customers_address(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA, ['firstName' => 'Owner']);

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

        $this->assertDatabaseHas('addresses', ['id' => $addressId]);
    }

    // ============================================================================
    // Single Address Query
    // ============================================================================

    /**
     * The customerAddress query returns the customer's own address.
     */
    public function test_customer_address_query_returns_own_address(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token, [
            'firstName' => 'Owned',
            'city' => 'Own City',
        ]);

        $response = $this->graphQL($this->customerAddressQuery($addressId), [], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.customerAddress._id'))->toBe($addressId)
            ->and($response->json('data.customerAddress.firstName'))->toBe('Owned')
            ->and($response->json('data.customerAddress.city'))->toBe('Own City');
    }

    /**
     * The customerAddress query refuses another customer's address.
     */
    public function test_customer_address_query_refuses_another_customers_address(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA, ['firstName' => 'Owner']);

        ['token' => $tokenB] = $this->authCustomer();

        $response = $this->graphQL($this->customerAddressQuery($addressId), [], ['Authorization' => 'Bearer '.$tokenB]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerAddress'))->toBeNull();
    }

    /**
     * The customerAddress query refuses a guest.
     */
    public function test_customer_address_query_requires_authentication(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token);

        $response = $this->graphQL($this->customerAddressQuery($addressId));

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerAddress'))->toBeNull();
    }

    /**
     * The customerAddress query rejects a malformed identifier.
     */
    public function test_customer_address_query_rejects_an_invalid_id(): void
    {
        ['token' => $token] = $this->authCustomer();

        $response = $this->graphQL(<<<'GQL'
            query {
              customerAddress(id: "not-a-valid-id") {
                _id
              }
            }
        GQL, [], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.customerAddress'))->toBeNull();
    }

    // ============================================================================
    // Token Address Queries
    // ============================================================================

    /**
     * The readGetCustomerAddress query returns the customer's own address.
     */
    public function test_read_get_customer_address_returns_own_address(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token, [
            'firstName' => 'Token',
            'city' => 'Token City',
        ]);

        $response = $this->graphQL($this->tokenAddressQuery('readGetCustomerAddress', $addressId, 'addressId firstName city'), [], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.readGetCustomerAddress.addressId'))->toBe($addressId)
            ->and($response->json('data.readGetCustomerAddress.firstName'))->toBe('Token')
            ->and($response->json('data.readGetCustomerAddress.city'))->toBe('Token City');
    }

    /**
     * The readGetCustomerAddress query refuses another customer's address.
     */
    public function test_read_get_customer_address_refuses_another_customers_address(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA);

        ['token' => $tokenB] = $this->authCustomer();

        $response = $this->graphQL($this->tokenAddressQuery('readGetCustomerAddress', $addressId), [], ['Authorization' => 'Bearer '.$tokenB]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.readGetCustomerAddress'))->toBeNull();
    }

    /**
     * The readCustomerAddressToken query identifies the customer's own address.
     */
    public function test_read_customer_address_token_returns_own_address(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token);

        $response = $this->graphQL($this->tokenAddressQuery('readCustomerAddressToken', $addressId, 'addressId'), [], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and((string) $response->json('data.readCustomerAddressToken.id'))->toEndWith((string) $addressId)
            ->and($response->json('data.readCustomerAddressToken.addressId'))->toBe($addressId);
    }

    /**
     * The readCustomerAddressToken query refuses another customer's address.
     */
    public function test_read_customer_address_token_refuses_another_customers_address(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA);

        ['token' => $tokenB] = $this->authCustomer();

        $response = $this->graphQL($this->tokenAddressQuery('readCustomerAddressToken', $addressId), [], ['Authorization' => 'Bearer '.$tokenB]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.readCustomerAddressToken'))->toBeNull();
    }

    /**
     * The readCustomerAddressToken query refuses a guest.
     */
    public function test_read_customer_address_token_requires_authentication(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token);

        $response = $this->graphQL($this->tokenAddressQuery('readCustomerAddressToken', $addressId));

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.readCustomerAddressToken'))->toBeNull();
    }

    /**
     * The collectionCustomerAddressToken query is not offered; customerAddresses lists the addresses.
     */
    public function test_collection_customer_address_token_is_not_a_query(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token);

        $response = $this->graphQL($this->tokenAddressQuery('collectionCustomerAddressToken', $addressId), [], ['Authorization' => 'Bearer '.$token]);

        expect($response->json('errors.0.message'))->toContain('collectionCustomerAddressToken')
            ->and($response->json('data'))->toBeNull();
    }

    // ============================================================================
    // Token Address Mutations
    // ============================================================================

    /**
     * The createCustomerAddressToken mutation stores a new address for the customer.
     */
    public function test_create_customer_address_token_creates_address(): void
    {
        [
            'customer' => $customer,
            'token' => $token,
        ] = $this->authCustomer();

        $response = $this->graphQL($this->createAddressTokenMutation(), [
            'input' => $this->dummyAddressInput([
                'firstName' => 'Tokened',
                'city' => 'Token City',
            ]),
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $data = $response->json('data.createCustomerAddressToken.customerAddressToken');

        expect($response->json('errors'))->toBeNull()
            ->and($data['firstName'])->toBe('Tokened')
            ->and($data['city'])->toBe('Token City');

        $this->assertDatabaseHas('addresses', [
            'id' => $data['addressId'],
            'customer_id' => $customer->id,
            'address_type' => 'customer',
            'first_name' => 'Tokened',
            'city' => 'Token City',
        ]);
    }

    /**
     * The createCustomerAddressToken mutation updates the customer's own address when an addressId is sent.
     */
    public function test_create_customer_address_token_updates_own_address(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token, ['city' => 'Before']);

        $response = $this->graphQL($this->createAddressTokenMutation(), [
            'input' => [
                'addressId' => $addressId,
                'city' => 'After',
            ],
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.createCustomerAddressToken.customerAddressToken.city'))->toBe('After');

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'city' => 'After',
        ]);
    }

    /**
     * The createCustomerAddressToken mutation refuses to update another customer's address.
     */
    public function test_create_customer_address_token_refuses_another_customers_address(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA, ['city' => 'Untouched']);

        ['token' => $tokenB] = $this->authCustomer();

        $response = $this->graphQL($this->createAddressTokenMutation(), [
            'input' => [
                'addressId' => $addressId,
                'city' => 'Hijacked',
            ],
        ], ['Authorization' => 'Bearer '.$tokenB]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty();

        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'city' => 'Untouched',
        ]);
    }

    /**
     * The createCustomerAddressToken mutation refuses a guest.
     */
    public function test_create_customer_address_token_requires_authentication(): void
    {
        $response = $this->graphQL($this->createAddressTokenMutation(), [
            'input' => $this->dummyAddressInput(['firstName' => 'GuestTokenAddress']),
        ]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty();

        $this->assertDatabaseMissing('addresses', ['first_name' => 'GuestTokenAddress']);
    }

    /**
     * The createCustomerAddressToken mutation rejects an address without its required fields.
     */
    public function test_create_customer_address_token_rejects_missing_required_fields(): void
    {
        [
            'customer' => $customer,
            'token' => $token,
        ] = $this->authCustomer();

        $response = $this->graphQL($this->createAddressTokenMutation(), [
            'input' => ['firstName' => 'Incomplete'],
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty();

        $this->assertDatabaseMissing('addresses', [
            'customer_id' => $customer->id,
            'first_name' => 'Incomplete',
        ]);
    }

    /**
     * The createDeleteCustomerAddressToken mutation deletes the customer's own address.
     */
    public function test_create_delete_customer_address_token_deletes_own_address(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token, ['firstName' => 'Doomed']);

        $response = $this->graphQL($this->deleteAddressTokenMutation(), [
            'input' => [
                'id' => (string) $addressId,
                'addressId' => $addressId,
            ],
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.createDeleteCustomerAddressToken.customerAddressToken.addressId'))->toBe($addressId)
            ->and($response->json('data.createDeleteCustomerAddressToken.customerAddressToken.firstName'))->toBe('Doomed');

        $this->assertDatabaseMissing('addresses', ['id' => $addressId]);
    }

    /**
     * The createDeleteCustomerAddressToken mutation refuses another customer's address.
     */
    public function test_create_delete_customer_address_token_refuses_another_customers_address(): void
    {
        ['token' => $tokenA] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($tokenA);

        ['token' => $tokenB] = $this->authCustomer();

        $response = $this->graphQL($this->deleteAddressTokenMutation(), [
            'input' => [
                'id' => (string) $addressId,
                'addressId' => $addressId,
            ],
        ], ['Authorization' => 'Bearer '.$tokenB]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty();

        $this->assertDatabaseHas('addresses', ['id' => $addressId]);
    }

    /**
     * The createDeleteCustomerAddressToken mutation refuses a guest.
     */
    public function test_create_delete_customer_address_token_requires_authentication(): void
    {
        ['token' => $token] = $this->authCustomer();
        $addressId = $this->createAddressViaApi($token);

        $response = $this->graphQL($this->deleteAddressTokenMutation(), [
            'input' => [
                'id' => (string) $addressId,
                'addressId' => $addressId,
            ],
        ]);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty();

        $this->assertDatabaseHas('addresses', ['id' => $addressId]);
    }

    /**
     * Create a customer and return [customer, bearer token].
     */
    private function authCustomer(): array
    {
        $customer = $this->createCustomer();
        $token = $this->customerAccessToken($customer);

        return [
            'customer' => $customer,
            'token' => $token,
        ];
    }

    /**
     * Build a valid address input payload with dummy data.
     */
    private function dummyAddressInput(array $overrides = []): array
    {
        return array_merge([
            'firstName' => 'Alice',
            'lastName' => 'Tester',
            'companyName' => 'Acme Inc.',
            'vatId' => 'GB123456789',
            'email' => 'address_'.uniqid().'@example.com',
            'phone' => '5551234567',
            'address1' => '123 Test Street',
            'address2' => 'Suite 200',
            'city' => 'Test City',
            'state' => 'CA',
            'country' => 'US',
            'postcode' => '90001',
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

    /**
     * Build the customerAddress query for an address id.
     */
    private function customerAddressQuery(int $addressId): string
    {
        return <<<GQL
            query {
              customerAddress(id: "/api/shop/customer-addresses/{$addressId}") {
                _id
                firstName
                city
              }
            }
        GQL;
    }

    /**
     * Build a token address query selecting the id, and any extra fields, of the given address.
     */
    private function tokenAddressQuery(string $operation, int $addressId, string $fields = ''): string
    {
        return <<<GQL
            query {
              {$operation}(id: "{$addressId}") {
                id
                {$fields}
              }
            }
        GQL;
    }

    /**
     * Build the createCustomerAddressToken mutation.
     */
    private function createAddressTokenMutation(): string
    {
        return <<<'GQL'
            mutation createAddressToken($input: createCustomerAddressTokenInput!) {
              createCustomerAddressToken(input: $input) {
                customerAddressToken {
                  addressId
                  firstName
                  city
                }
              }
            }
        GQL;
    }

    /**
     * Build the createDeleteCustomerAddressToken mutation.
     */
    private function deleteAddressTokenMutation(): string
    {
        return <<<'GQL'
            mutation deleteAddressToken($input: createDeleteCustomerAddressTokenInput!) {
              createDeleteCustomerAddressToken(input: $input) {
                customerAddressToken {
                  addressId
                  firstName
                }
              }
            }
        GQL;
    }
}
