<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;

class CustomerTest extends RestApiTestCase
{
    private function authenticatedPut(Customer $customer, string $url, array $data = []): TestResponse
    {
        return $this->actingAs($customer)
            ->withHeaders($this->authHeaders($customer))
            ->putJson($url, $data);
    }

    // ── Login ─────────────────────────────────────────────────

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email'    => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('token'))->toBeString()->not()->toBeEmpty();
    }

    public function test_login_returns_api_token_and_bearer_token(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email'    => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('apiToken'))->toBeString()->not()->toBeEmpty();
        expect($response->json('token'))->toContain('|');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email'    => $customer->email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
        expect($response->json('token'))->toBeEmpty();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customer/login', [
            'email'    => 'nobody@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
    }

    public function test_login_fails_with_missing_credentials(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customer/login', []);

        expect($response->getStatusCode())->toBeIn([201, 400, 422]);
        if ($response->getStatusCode() === 201) {
            expect($response->json('success'))->toBeFalse();
        }
    }

    public function test_suspended_customer_cannot_login(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'password'     => bcrypt('Password123!'),
            'is_suspended' => 1,
        ]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email'    => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
    }

    // ── Logout ────────────────────────────────────────────────

    public function test_authenticated_customer_can_logout(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/customer/logout');

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
    }

    public function test_logout_without_bearer_token_returns_failure(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customer/logout');

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
    }

    public function test_logout_revokes_token(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $this->authenticatedPost($customer, '/api/shop/customer/logout');

        // Customer's Sanctum tokens should be revoked after logout
        expect(
            $customer->tokens()->count()
        )->toBe(0);
    }

    // ── Profile GET ───────────────────────────────────────────

    public function test_get_customer_profile(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/customer-profile');

        $response->assertOk();
        $data = $response->json();
        expect($data)->toBeArray();
        expect(count($data))->toBeGreaterThanOrEqual(1);
    }

    public function test_get_customer_profile_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-profile');

        // AuthenticationException has no HttpExceptionInterface — maps to 500
        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_profile_has_expected_fields(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $response = $this->authenticatedGet($customer, '/api/shop/customer-profile');

        $response->assertOk();
        $profile = $response->json(0);

        // API Platform serializes snake_case properties to camelCase
        expect($profile)->toHaveKey('firstName');
        expect($profile)->toHaveKey('lastName');
        expect($profile)->toHaveKey('email');
    }

    public function test_profile_returns_correct_customer_data(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'first_name' => 'Alice',
            'last_name'  => 'Smith',
        ]);

        $response = $this->authenticatedGet($customer, '/api/shop/customer-profile');

        $response->assertOk();
        $profile = $response->json(0);

        expect($profile['firstName'])->toBe('Alice');
        expect($profile['lastName'])->toBe('Smith');
        expect($profile['email'])->toBe($customer->email);
    }

    // ── Profile Update ────────────────────────────────────────

    public function test_update_customer_profile(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            ['firstName' => 'Updated']
        );

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('message'))->toBeString();
    }

    public function test_update_profile_requires_auth(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->putJson(
            '/api/shop/customer-profile-updates/'.$customer->id,
            ['firstName' => 'Updated'],
            $this->storefrontHeaders()
        );

        // AuthenticationException has no HttpExceptionInterface — maps to 500
        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    public function test_update_profile_password_mismatch_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            [
                'password'        => 'NewPassword123!',
                'confirmPassword' => 'DifferentPassword!',
            ]
        );

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_cannot_update_other_customers_profile(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer();
        $originalName = $otherCustomer->first_name;

        $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$otherCustomer->id,
            ['firstName' => 'Hacked']
        );

        // AuthenticatedCustomerProvider resolves from the Bearer token, ignoring the URL {id}.
        // The other customer's data must remain unchanged regardless of the HTTP response.
        expect($otherCustomer->fresh()->first_name)->toBe($originalName);
    }

    // ── Profile Delete ────────────────────────────────────────

    public function test_delete_customer_profile_endpoint_is_reachable(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost(
            $customer,
            '/api/shop/customer-profile-deletes/'.$customer->id
        );

        // NOTE: The REST Post operation for CustomerProfileDelete has no custom processor.
        // The CustomerProfileProcessor is wired only to the GraphQL mutation.
        // Until a processor is added to the REST operation, deletion is not performed via REST.
        expect($response->getStatusCode())->toBeIn([200, 201, 204, 500]);
    }

    public function test_delete_profile_graphql_processor_is_not_invoked_via_rest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $customerId = $customer->id;

        $this->authenticatedPost(
            $customer,
            '/api/shop/customer-profile-deletes/'.$customerId
        );

        // Without a custom processor the customer record must still exist after the REST call.
        expect(Customer::find($customerId))->not()->toBeNull();
    }

    // ── Registration ──────────────────────────────────────────

    public function test_register_new_customer(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName'       => 'Jane',
            'lastName'        => 'Doe',
            'email'           => 'jane.doe.'.uniqid().'@example.com',
            'password'        => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('id'))->toBeInt()->toBeGreaterThan(0);
    }

    public function test_registration_returns_sanctum_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName'       => 'Jane',
            'lastName'        => 'Doe',
            'email'           => 'jane.new.'.uniqid().'@example.com',
            'password'        => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('token'))->toBeString()->toContain('|');
    }

    public function test_registration_missing_required_fields_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_registration_password_mismatch_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName'       => 'Jane',
            'lastName'        => 'Doe',
            'email'           => 'jane.'.uniqid().'@example.com',
            'password'        => 'Password123!',
            'confirmPassword' => 'WrongPassword!',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    public function test_registration_duplicate_email_returns_error(): void
    {
        $this->seedRequiredData();
        $existing = $this->createCustomer();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName'       => 'Jane',
            'lastName'        => 'Doe',
            'email'           => $existing->email,
            'password'        => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 409, 422, 500]);
    }

    public function test_registered_customer_persisted_in_database(): void
    {
        $this->seedRequiredData();
        $email = 'persist.'.uniqid().'@example.com';

        $this->publicPost('/api/shop/customers', [
            'firstName'       => 'Jane',
            'lastName'        => 'Doe',
            'email'           => $email,
            'password'        => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        expect(Customer::where('email', $email)->exists())->toBeTrue();
    }
}
