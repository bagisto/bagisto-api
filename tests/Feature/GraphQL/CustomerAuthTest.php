<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CustomerAuthTest extends GraphQLTestCase
{
    // ============================================================================
    // Registration
    // ============================================================================

    /**
     * Test register new customer successfully.
     */
    public function test_register_new_customer_successfully(): void
    {
        $this->seedRequiredData();

        $email = 'auth_test_'.uniqid().'@example.com';

        $mutation = <<<'GQL'
            mutation registerCustomer($input: createCustomerInput!) {
              createCustomer(input: $input) {
                customer {
                  id
                  _id
                  email
                  firstName
                  lastName
                  token
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'email' => $email,
                'password' => 'secret123',
                'confirmPassword' => 'secret123',
                'phone' => '9990000001',
                'status' => '1',
                'isVerified' => '1',
                'isSuspended' => '0',
                'subscribedToNewsLetter' => false,
            ],
        ]);

        $response->assertSuccessful();

        $json = $response->json();
        $this->assertArrayNotHasKey('errors', $json, 'GraphQL errors: '.json_encode($json['errors'] ?? []));

        $data = $response->json('data.createCustomer.customer');
        $this->assertNotNull($data);
        $this->assertSame($email, $data['email']);
        $this->assertSame('Jane', $data['firstName']);
        $this->assertSame('Doe', $data['lastName']);
        $this->assertNotEmpty($data['token'], 'Registration should return an auth token');

        $this->assertDatabaseHas('customers', ['email' => $email]);
    }

    /**
     * Test register fails with duplicate email.
     */
    public function test_register_fails_with_duplicate_email(): void
    {
        $existing = $this->createCustomer([
            'email' => 'duplicate_'.uniqid().'@example.com',
        ]);

        $mutation = <<<'GQL'
            mutation registerCustomer($input: createCustomerInput!) {
              createCustomer(input: $input) {
                customer { id email }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'firstName' => 'Dup',
                'lastName' => 'User',
                'email' => $existing->email,
                'password' => 'secret123',
                'confirmPassword' => 'secret123',
                'status' => '1',
                'isVerified' => '1',
                'isSuspended' => '0',
                'subscribedToNewsLetter' => false,
            ],
        ]);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Duplicate email should return an error');
    }

    /**
     * Test register fails with mismatched passwords.
     */
    public function test_register_fails_with_mismatched_passwords(): void
    {
        $this->seedRequiredData();

        $mutation = <<<'GQL'
            mutation registerCustomer($input: createCustomerInput!) {
              createCustomer(input: $input) {
                customer { id }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'firstName' => 'Mis',
                'lastName' => 'Match',
                'email' => 'mismatch_'.uniqid().'@example.com',
                'password' => 'secret123',
                'confirmPassword' => 'different456',
                'status' => '1',
                'isVerified' => '1',
                'isSuspended' => '0',
                'subscribedToNewsLetter' => false,
            ],
        ]);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Mismatched password should return an error');
    }

    // ============================================================================
    // Login
    // ============================================================================

    /**
     * Test login with valid credentials.
     */
    public function test_login_with_valid_credentials(): void
    {
        $password = 'secret123';
        $email = 'login_'.uniqid().'@example.com';

        $this->createCustomer([
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $mutation = <<<'GQL'
            mutation login($input: createCustomerLoginInput!) {
              createCustomerLogin(input: $input) {
                customerLogin {
                  id
                  _id
                  token
                  apiToken
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.createCustomerLogin.customerLogin');
        $this->assertNotNull($data);
        $this->assertTrue($data['success'], 'Login should succeed with valid credentials');
        $this->assertNotEmpty($data['token'], 'Login should return a bearer token');
    }

    /**
     * Test login fails with wrong password.
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $email = 'wrongpass_'.uniqid().'@example.com';

        $this->createCustomer([
            'email' => $email,
            'password' => Hash::make('correctpass'),
        ]);

        $mutation = <<<'GQL'
            mutation login($input: createCustomerLoginInput!) {
              createCustomerLogin(input: $input) {
                customerLogin { success message token }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'email' => $email,
                'password' => 'wrongpass',
            ],
        ]);

        $this->assertSame('invalid_credentials', $response->json('errors.0.extensions.code'));
        $this->assertNull($response->json('data.createCustomerLogin'));
    }

    /**
     * Test suspended customer can login.
     */
    public function test_suspended_customer_can_login(): void
    {
        $email = 'suspended_'.uniqid().'@example.com';
        $password = 'secret123';

        $this->createCustomer([
            'email' => $email,
            'password' => Hash::make($password),
            'is_suspended' => 1,
        ]);

        $mutation = <<<'GQL'
            mutation login($input: createCustomerLoginInput!) {
              createCustomerLogin(input: $input) {
                customerLogin { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $data = $response->json('data.createCustomerLogin.customerLogin');
        $this->assertTrue($data['success'], 'Suspended customer should be able to log in (blocked only at checkout)');
    }

    // ============================================================================
    // Logout
    // ============================================================================

    /**
     * Test logout authenticated customer.
     */
    public function test_logout_authenticated_customer(): void
    {
        if (! Schema::hasColumn('customers', 'device_token')) {
            Schema::table('customers', function ($table) {
                $table->string('device_token')->nullable();
            });
        }

        $customer = $this->createCustomer();
        $token = $this->customerAccessToken($customer);

        $mutation = <<<'GQL'
            mutation logout($input: createLogoutInput!) {
              createLogout(input: $input) {
                logout { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [],
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.createLogout.logout');
        $this->assertNotNull($data);
        $this->assertTrue($data['success'], 'Logout should succeed with valid token');
    }

    /**
     * Test logout fails without token.
     */
    public function test_logout_fails_without_token(): void
    {
        $mutation = <<<'GQL'
            mutation logout($input: createLogoutInput!) {
              createLogout(input: $input) {
                logout { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => []]);

        $errors = $response->json('errors');
        $logoutData = $response->json('data.createLogout.logout');

        $this->assertTrue(
            ! empty($errors) || ($logoutData && ! $logoutData['success']),
            'Logout without auth should fail'
        );
    }

    // ============================================================================
    // Forgot Password
    // ============================================================================

    /**
     * Test forgot password for existing email.
     */
    public function test_forgot_password_for_existing_email(): void
    {
        $customer = $this->createCustomer([
            'email' => 'forgot_'.uniqid().'@example.com',
        ]);

        $mutation = <<<'GQL'
            mutation forgot($input: createForgotPasswordInput!) {
              createForgotPassword(input: $input) {
                forgotPassword { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => ['email' => $customer->email],
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.createForgotPassword.forgotPassword');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertNotEmpty($data['message']);
    }

    /**
     * Test forgot password for nonexistent email.
     */
    public function test_forgot_password_for_nonexistent_email(): void
    {
        $this->seedRequiredData();

        $mutation = <<<'GQL'
            mutation forgot($input: createForgotPasswordInput!) {
              createForgotPassword(input: $input) {
                forgotPassword { success message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => ['email' => 'does-not-exist-'.uniqid().'@example.com'],
        ]);

        $data = $response->json('data.createForgotPassword.forgotPassword');
        $errors = $response->json('errors');

        $this->assertTrue(
            ! empty($errors) || ($data && isset($data['success']) && $data['success'] === false),
            'Forgot password for non-existent email should indicate failure'
        );
    }

    // ============================================================================
    // Verify Token
    // ============================================================================

    /**
     * Test verify token with valid token.
     */
    public function test_verify_token_with_valid_token(): void
    {
        $customer = $this->createCustomer();
        $token = $this->customerAccessToken($customer);

        $mutation = <<<'GQL'
            mutation verify($input: createVerifyTokenInput!) {
              createVerifyToken(input: $input) {
                verifyToken { id _id firstName lastName email isValid message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [],
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertSuccessful();

        $data = $response->json('data.createVerifyToken.verifyToken');
        $this->assertNotNull($data);
        $this->assertTrue($data['isValid'], 'Valid token should be verified');
        $this->assertSame($customer->email, $data['email']);
    }

    /**
     * Test verify token fails without token.
     */
    public function test_verify_token_fails_without_token(): void
    {
        $mutation = <<<'GQL'
            mutation verify($input: createVerifyTokenInput!) {
              createVerifyToken(input: $input) {
                verifyToken { isValid message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => []]);

        $data = $response->json('data.createVerifyToken.verifyToken');
        $errors = $response->json('errors');

        $this->assertTrue(
            ! empty($errors) || ($data && isset($data['isValid']) && $data['isValid'] === false),
            'Verify token without auth should fail'
        );
    }

    /**
     * Test verify token fails for suspended customer.
     */
    public function test_verify_token_fails_for_suspended_customer(): void
    {
        $customer = $this->createCustomer(['is_suspended' => 1]);
        $token = $this->customerAccessToken($customer);

        $mutation = <<<'GQL'
            mutation verify($input: createVerifyTokenInput!) {
              createVerifyToken(input: $input) {
                verifyToken { isValid message }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, [
            'input' => [],
        ], ['Authorization' => 'Bearer '.$token]);

        $data = $response->json('data.createVerifyToken.verifyToken');
        $errors = $response->json('errors');

        $this->assertTrue(
            ! empty($errors) || ($data && isset($data['isValid']) && $data['isValid'] === false),
            'Suspended customer token should not be valid'
        );
    }
}
