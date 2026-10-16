<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

/**
 * Tests for customer authentication GraphQL mutations:
 * - createCustomer (registration)
 * - createCustomerLogin
 * - createLogout
 * - createForgotPassword
 * - createResetPassword
 * - createVerifyAccount
 * - createVerificationEmail
 * - createVerifyToken
 *
 * All tests use freshly-created dummy data via factories,
 * no reliance on pre-existing DB records.
 */
class CustomerAuthTest extends GraphQLTestCase
{
    // ─── Registration ───────────────────────────────────────────────────

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

        // Verify customer exists in DB
        $this->assertDatabaseHas('customers', ['email' => $email]);
    }

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

    // ─── Login ──────────────────────────────────────────────────────────

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

        $data = $response->json('data.createCustomerLogin.customerLogin');
        $this->assertNotNull($data);
        $this->assertFalse($data['success'], 'Login should fail with wrong password');
        $this->assertEmpty($data['token'], 'No token should be returned on failure');
    }

    public function test_suspended_customer_can_login(): void
    {
        // A suspended customer may sign in and browse; they are stopped only at
        // checkout (mirrors the storefront). Only inactive accounts are refused.
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

    // ─── Logout ─────────────────────────────────────────────────────────

    public function test_logout_authenticated_customer(): void
    {
        if (! Schema::hasColumn('customers', 'device_token')) {
            Schema::table('customers', function ($table) {
                $table->string('device_token')->nullable();
            });
        }

        $customer = $this->createCustomer();
        $token = $customer->createToken('test-token')->plainTextToken;

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

        // Either an error or a failed response is acceptable
        $this->assertTrue(
            ! empty($errors) || ($logoutData && ! $logoutData['success']),
            'Logout without auth should fail'
        );
    }

    // ─── Forgot Password ────────────────────────────────────────────────

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
        // Should return a success/message response (actual email delivery depends on mail config)
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertNotEmpty($data['message']);
    }

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

        // Response should be present — either success:false or an error
        $data = $response->json('data.createForgotPassword.forgotPassword');
        $errors = $response->json('errors');

        $this->assertTrue(
            ! empty($errors) || ($data && isset($data['success']) && $data['success'] === false),
            'Forgot password for non-existent email should indicate failure'
        );
    }

    // ─── Verify Token ───────────────────────────────────────────────────

    public function test_verify_token_with_valid_token(): void
    {
        $customer = $this->createCustomer();
        $token = $customer->createToken('test-token')->plainTextToken;

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

    public function test_verify_token_fails_for_suspended_customer(): void
    {
        $customer = $this->createCustomer(['is_suspended' => 1]);
        $token = $customer->createToken('test-token')->plainTextToken;

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

    // ─── Reset Password ─────────────────────────────────────────────────

    private const RESET_PASSWORD = <<<'GQL'
        mutation reset($input: createResetPasswordInput!) {
          createResetPassword(input: $input) {
            resetPassword {
              success
              message
            }
          }
        }
    GQL;

    public function test_reset_password_with_valid_token_changes_the_password(): void
    {
        $customer = $this->createCustomer();
        $token = Password::broker('customers')->createToken($customer);

        $response = $this->graphQL(self::RESET_PASSWORD, [
            'input' => [
                'token' => $token,
                'email' => $customer->email,
                'password' => 'NewSecret123',
                'passwordConfirmation' => 'NewSecret123',
            ],
        ]);

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertTrue($response->json('data.createResetPassword.resetPassword.success'));
        $this->assertTrue(Hash::check('NewSecret123', $customer->fresh()->password));
    }

    public function test_reset_password_with_invalid_token_returns_error(): void
    {
        $customer = $this->createCustomer(['password' => bcrypt('OldSecret123')]);

        $response = $this->graphQL(self::RESET_PASSWORD, [
            'input' => [
                'token' => 'invalid-token',
                'email' => $customer->email,
                'password' => 'NewSecret123',
                'passwordConfirmation' => 'NewSecret123',
            ],
        ]);

        $this->assertNotEmpty($response->json('errors'));
        $this->assertTrue(Hash::check('OldSecret123', $customer->fresh()->password));
    }

    // ─── Account Verification ───────────────────────────────────────────

    public function test_verify_account_with_valid_token(): void
    {
        Mail::fake();
        $customer = $this->createCustomer(['is_verified' => 0, 'token' => 'verify-'.uniqid()]);

        $mutation = <<<'GQL'
            mutation verify($input: createVerifyAccountInput!) {
              createVerifyAccount(input: $input) {
                verifyAccount {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['token' => $customer->token]]);

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertTrue($response->json('data.createVerifyAccount.verifyAccount.success'));
        $this->assertSame(1, (int) $customer->fresh()->is_verified);
    }

    public function test_verify_account_with_unknown_token_returns_error(): void
    {
        $this->seedRequiredData();

        $mutation = <<<'GQL'
            mutation verify($input: createVerifyAccountInput!) {
              createVerifyAccount(input: $input) {
                verifyAccount {
                  success
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['token' => 'unknown-'.uniqid()]]);

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_verification_email_is_sent(): void
    {
        Mail::fake();
        $customer = $this->createCustomer(['is_verified' => 0, 'token' => 'old-token']);

        $mutation = <<<'GQL'
            mutation resend($input: createVerificationEmailInput!) {
              createVerificationEmail(input: $input) {
                verificationEmail {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['email' => $customer->email]]);

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertTrue($response->json('data.createVerificationEmail.verificationEmail.success'));
        $this->assertNotSame('old-token', $customer->fresh()->token);
    }

    public function test_register_with_newsletter_creates_the_subscriber_row(): void
    {
        $this->seedRequiredData();
        $email = 'news_'.uniqid().'@example.com';

        $mutation = <<<'GQL'
            mutation registerCustomer($input: createCustomerInput!) {
              createCustomer(input: $input) {
                customer {
                  _id
                  email
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
                'subscribedToNewsLetter' => true,
            ],
        ]);

        $response->assertOk();
        $this->assertNull($response->json('errors'));

        $customerId = DB::table('customers')->where('email', $email)->value('id');

        $this->assertTrue(DB::table('subscribers_list')->where('email', $email)->where('customer_id', $customerId)->where('is_subscribed', 1)->exists());
    }
}
