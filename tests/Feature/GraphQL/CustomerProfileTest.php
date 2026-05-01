<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Customer\Models\Customer;

class CustomerProfileTest extends GraphQLTestCase
{
    // ── Read Profile (Query) ──────────────────────────────────

    /**
     * Test: Read authenticated customer profile returns all fields
     */
    public function test_read_customer_profile_returns_all_fields(): void
    {
        $customer = $this->createCustomer([
            'first_name'                => 'Alice',
            'last_name'                 => 'Wonder',
            'email'                     => 'alice@example.com',
            'phone'                     => '555-1234',
            'gender'                    => 'Female',
            'date_of_birth'             => '1995-06-15',
            'status'                    => 1,
            'subscribed_to_news_letter' => true,
            'is_verified'               => 1,
        ]);

        $query = <<<'GQL'
            query readProfile {
              readCustomerProfile {
                id
                firstName
                lastName
                email
                phone
                gender
                dateOfBirth
                status
                subscribedToNewsLetter
                isVerified
                isSuspended
                image
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query, [
            'id' => '/api/shop/customer-profiles/'.$customer->id,
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.readCustomerProfile');

        expect($data)->not->toBeNull()
            ->and($data['firstName'])->toBe('Alice')
            ->and($data['lastName'])->toBe('Wonder')
            ->and($data['email'])->toBe('alice@example.com')
            ->and($data['phone'])->toBe('555-1234')
            ->and($data['gender'])->toBe('Female')
            ->and($data['dateOfBirth'])->toBe('1995-06-15')
            ->and($data['subscribedToNewsLetter'])->toBeTrue()
            ->and($data['isVerified'])->toBe('1');
    }

    /**
     * Test: Read profile without authentication returns error
     */
    public function test_read_profile_without_auth_returns_error(): void
    {
        $query = <<<'GQL'
            query readProfile($id: ID!) {
              readCustomerProfile(id: $id) {
                id
                firstName
                lastName
                email
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'id' => '/api/shop/customer-profiles/1',
        ]);

        $response->assertSuccessful();

        $json = $response->json();

        expect($json)->toHaveKey('errors')
            ->and($json['errors'])->not->toBeEmpty();
    }

    /**
     * Test: Read profile returns correct id format
     */
    public function test_read_profile_returns_correct_id_format(): void
    {
        $customer = $this->createCustomer();

        $query = <<<'GQL'
            query readProfile {
              readCustomerProfile {
                id
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query, [
            'id' => '/api/shop/customer-profiles/'.$customer->id,
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.readCustomerProfile');

        expect($data)->not->toBeNull()
            ->and($data['id'])->toContain('/api/shop/customer_profiles/');
    }

    /**
     * Test: Read profile with selective fields
     */
    public function test_read_profile_with_selective_fields(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'Bob',
            'email'      => 'bob@example.com',
        ]);

        $query = <<<'GQL'
            query readProfile {
              readCustomerProfile {
                firstName
                email
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $query, [
            'id' => '/api/shop/customer-profiles/'.$customer->id,
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.readCustomerProfile');

        expect($data)->not->toBeNull()
            ->and($data['firstName'])->toBe('Bob')
            ->and($data['email'])->toBe('bob@example.com')
            ->and($data)->not->toHaveKey('lastName')
            ->and($data)->not->toHaveKey('phone');
    }

    // ── Update Profile (Mutation) ─────────────────────────────

    /**
     * Test: Update customer first name
     */
    public function test_update_customer_first_name(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'Original',
            'last_name'  => 'Name',
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['firstName' => 'Updated'],
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('data.createCustomerProfileUpdate.clientMutationId', null);

        $customer->refresh();
        expect($customer->first_name)->toBe('Updated')
            ->and($customer->last_name)->toBe('Name');
    }

    /**
     * Test: Update multiple profile fields
     */
    public function test_update_multiple_profile_fields(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'Old',
            'last_name'  => 'User',
            'phone'      => '0000000',
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'firstName' => 'NewFirst',
                'lastName'  => 'NewLast',
                'phone'     => '9998888',
                'gender'    => 'Male',
            ],
        ]);

        $response->assertSuccessful();

        $customer->refresh();
        expect($customer->first_name)->toBe('NewFirst')
            ->and($customer->last_name)->toBe('NewLast')
            ->and($customer->phone)->toBe('9998888')
            ->and($customer->gender)->toBe('Male');
    }

    /**
     * Test: Update profile without authentication returns error
     */
    public function test_update_profile_without_auth_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => ['firstName' => 'Hacker'],
        ]);

        $response->assertSuccessful();

        $json = $response->json();

        expect($json)->toHaveKey('errors')
            ->and($json['errors'])->not->toBeEmpty();
    }

    /**
     * Test: Update customer email
     */
    public function test_update_customer_email(): void
    {
        $customer = $this->createCustomer([
            'email' => 'old@example.com',
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['email' => 'new@example.com'],
        ]);

        $response->assertSuccessful();

        $customer->refresh();
        expect($customer->email)->toBe('new@example.com');
    }

    /**
     * Test: Update customer date of birth
     */
    public function test_update_customer_date_of_birth(): void
    {
        $customer = $this->createCustomer([
            'date_of_birth' => '1990-01-01',
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['dateOfBirth' => '1985-12-25'],
        ]);

        $response->assertSuccessful();

        $customer->refresh();
        expect($customer->date_of_birth)->toBe('1985-12-25');
    }

    /**
     * Test: Update newsletter subscription
     */
    public function test_update_newsletter_subscription(): void
    {
        $customer = $this->createCustomer([
            'subscribed_to_news_letter' => false,
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['subscribedToNewsLetter' => true],
        ]);

        $response->assertSuccessful();

        $customer->refresh();
        expect((bool) $customer->subscribed_to_news_letter)->toBeTrue();
    }

    /**
     * Test: Update with empty input does not change data
     */
    public function test_update_with_empty_input_preserves_data(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'Unchanged',
            'last_name'  => 'User',
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [],
        ]);

        $response->assertSuccessful();

        $customer->refresh();
        expect($customer->first_name)->toBe('Unchanged')
            ->and($customer->last_name)->toBe('User');
    }

    /**
     * Test: Verify update is confirmed by subsequent read
     */
    public function test_update_confirmed_by_read_query(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'Before',
            'last_name'  => 'Update',
        ]);

        $mutation = <<<'GQL'
            mutation updateProfile($input: createCustomerProfileUpdateInput!) {
              createCustomerProfileUpdate(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [
                'firstName' => 'After',
                'lastName'  => 'Change',
            ],
        ]);

        $readQuery = <<<'GQL'
            query readProfile {
              readCustomerProfile {
                firstName
                lastName
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $readQuery, [
            'id' => '/api/shop/customer-profiles/'.$customer->id,
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.readCustomerProfile');

        expect($data['firstName'])->toBe('After')
            ->and($data['lastName'])->toBe('Change');
    }

    // ── Delete Profile (Mutation) ─────────────────────────────

    /**
     * Test: Delete customer profile removes the customer
     */
    public function test_delete_customer_profile(): void
    {
        $customer = $this->createCustomer([
            'first_name' => 'ToDelete',
            'email'      => 'delete-me@example.com',
        ]);

        $customerId = $customer->id;

        $mutation = <<<'GQL'
            mutation deleteProfile($input: createCustomerProfileDeleteInput!) {
              createCustomerProfileDelete(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [],
        ]);

        $response->assertSuccessful();

        expect(Customer::find($customerId))->toBeNull();
    }

    /**
     * Test: Delete profile also removes access tokens
     */
    public function test_delete_profile_removes_tokens(): void
    {
        $customer = $this->createCustomer([
            'email' => 'token-delete@example.com',
        ]);

        $customer->createToken('token-1');
        $customer->createToken('token-2');

        $tokenCount = DB::table('personal_access_tokens')
            ->where('tokenable_id', $customer->id)
            ->where('tokenable_type', Customer::class)
            ->count();

        expect($tokenCount)->toBeGreaterThanOrEqual(2);

        $mutation = <<<'GQL'
            mutation deleteProfile($input: createCustomerProfileDeleteInput!) {
              createCustomerProfileDelete(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $this->authenticatedGraphQL($customer, $mutation, [
            'input' => [],
        ]);

        $remainingTokens = DB::table('personal_access_tokens')
            ->where('tokenable_id', $customer->id)
            ->where('tokenable_type', Customer::class)
            ->count();

        expect($remainingTokens)->toBe(0);
    }

    /**
     * Test: Delete profile without authentication returns error
     */
    public function test_delete_profile_without_auth_returns_error(): void
    {
        $mutation = <<<'GQL'
            mutation deleteProfile($input: createCustomerProfileDeleteInput!) {
              createCustomerProfileDelete(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [],
        ]);

        $response->assertSuccessful();

        $json = $response->json();

        expect($json)->toHaveKey('errors')
            ->and($json['errors'])->not->toBeEmpty();
    }

    /**
     * Test: Read profile after delete returns error
     */
    public function test_read_profile_after_delete_returns_error(): void
    {
        $customer = $this->createCustomer([
            'email' => 'ghost@example.com',
        ]);

        $customerId = $customer->id;

        $deleteMutation = <<<'GQL'
            mutation deleteProfile($input: createCustomerProfileDeleteInput!) {
              createCustomerProfileDelete(input: $input) {
                clientMutationId
              }
            }
        GQL;

        $this->authenticatedGraphQL($customer, $deleteMutation, [
            'input' => [],
        ]);

        expect(Customer::find($customerId))->toBeNull();

        $readQuery = <<<'GQL'
            query readProfile($id: ID!) {
              readCustomerProfile(id: $id) {
                id
                firstName
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $readQuery, [
            'id' => '/api/shop/customer-profiles/'.$customerId,
        ]);

        $json = $response->json();

        expect($json)->toHaveKey('errors')
            ->and($json['errors'])->not->toBeEmpty();
    }

    // ── Schema Introspection ──────────────────────────────────

    /**
     * Test: CustomerProfile type has expected fields in schema
     */
    public function test_customer_profile_schema_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "CustomerProfile") {
                name
                fields {
                  name
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $type = $response->json('data.__type');

        expect($type)->not->toBeNull()
            ->and($type['name'])->toBe('CustomerProfile');

        $fieldNames = array_column($type['fields'], 'name');

        expect($fieldNames)
            ->toContain('id')
            ->toContain('firstName')
            ->toContain('lastName')
            ->toContain('email')
            ->toContain('phone')
            ->toContain('gender')
            ->toContain('dateOfBirth')
            ->toContain('status')
            ->toContain('subscribedToNewsLetter')
            ->toContain('isVerified')
            ->toContain('isSuspended')
            ->toContain('image');
    }

    /**
     * Test: Update mutation input has expected fields
     */
    public function test_update_mutation_input_has_expected_fields(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "createCustomerProfileUpdateInput") {
                name
                inputFields {
                  name
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $type = $response->json('data.__type');

        expect($type)->not->toBeNull();

        $fieldNames = array_column($type['inputFields'], 'name');

        expect($fieldNames)
            ->toContain('firstName')
            ->toContain('lastName')
            ->toContain('email')
            ->toContain('phone')
            ->toContain('gender')
            ->toContain('dateOfBirth')
            ->toContain('password')
            ->toContain('confirmPassword');
    }

    /**
     * Test: Delete mutation exists in schema
     */
    public function test_delete_mutation_exists_in_schema(): void
    {
        $query = <<<'GQL'
            {
              __schema {
                mutationType {
                  fields {
                    name
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $mutationNames = array_column(
            $response->json('data.__schema.mutationType.fields'),
            'name'
        );

        expect($mutationNames)
            ->toContain('createCustomerProfileUpdate')
            ->toContain('createCustomerProfileDelete');
    }
}
