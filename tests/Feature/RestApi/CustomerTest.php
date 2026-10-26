<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

class CustomerTest extends RestApiTestCase
{
    /**
     * Test customer can login with valid credentials.
     */
    public function test_customer_can_login_with_valid_credentials(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('token'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test login returns api token and bearer token.
     */
    public function test_login_returns_api_token_and_bearer_token(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('apiToken'))->toBeString()->not()->toBeEmpty();
        expect($response->json('token'))->toBe($response->json('accessToken'));
        expect(substr_count((string) $response->json('accessToken'), '.'))->toBe(2);
    }

    /**
     * Test login fails with wrong password.
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email' => $customer->email,
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(401);
        expect($response->json('error'))->toBe('invalid_credentials');
        expect($response->json('accessToken'))->toBeNull();
    }

    /**
     * Test login fails with nonexistent email.
     */
    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customer/login', [
            'email' => 'nobody@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(401);
        expect($response->json('error'))->toBe('invalid_credentials');
    }

    /**
     * Test login fails with missing credentials.
     */
    public function test_login_fails_with_missing_credentials(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customer/login', []);

        expect($response->getStatusCode())->toBeIn([201, 400, 422]);
        if ($response->getStatusCode() === 201) {
            expect($response->json('success'))->toBeFalse();
        }
    }

    /**
     * Test suspended customer can login.
     */
    public function test_suspended_customer_can_login(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'password' => bcrypt('Password123!'),
            'is_suspended' => 1,
        ]);

        $response = $this->publicPost('/api/shop/customer/login', [
            'email' => $customer->email,
            'password' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('token'))->not->toBeNull();
    }

    /**
     * Test authenticated customer can logout.
     */
    public function test_authenticated_customer_can_logout(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/customer/logout');

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
    }

    /**
     * Test logout without bearer token returns failure.
     */
    public function test_logout_without_bearer_token_returns_failure(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customer/logout');

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
    }

    /**
     * Test logout revokes token.
     */
    public function test_logout_revokes_token(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $this->authenticatedPost($customer, '/api/shop/customer/logout');

        expect(
            CustomerApiSession::query()->where('customer_id', $customer->id)->whereNull('revoked_at')->count()
        )->toBe(0);
    }

    /**
     * Test get customer profile.
     */
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

    /**
     * Test get customer profile requires auth.
     */
    public function test_get_customer_profile_requires_auth(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/customer-profile');

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test forged token with valid id is rejected.
     */
    public function test_forged_token_with_valid_id_is_rejected(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $token = $this->customerAccessToken($customer);

        $forged = $this->withTamperedPayload($token, ['sub' => $customer->id + 1]);

        $response = $this->getJson('/api/shop/customer-profile', array_merge($this->storefrontHeaders(), [
            'Authorization' => "Bearer {$forged}",
        ]));

        expect($response->getStatusCode())->toBeIn([401, 403]);
        expect($response->getContent())->not->toContain($customer->email);
    }

    /**
     * Test profile has expected fields.
     */
    public function test_profile_has_expected_fields(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response = $this->authenticatedGet($customer, '/api/shop/customer-profile');

        $response->assertOk();
        $profile = $response->json(0);

        expect($profile)->toHaveKey('firstName');
        expect($profile)->toHaveKey('lastName');
        expect($profile)->toHaveKey('email');
    }

    /**
     * Test profile returns correct customer data.
     */
    public function test_profile_returns_correct_customer_data(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
        ]);

        $response = $this->authenticatedGet($customer, '/api/shop/customer-profile');

        $response->assertOk();
        $profile = $response->json(0);

        expect($profile['firstName'])->toBe('Alice');
        expect($profile['lastName'])->toBe('Smith');
        expect($profile['email'])->toBe($customer->email);
    }

    /**
     * Test update customer profile.
     */
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

    /**
     * Test update profile requires auth.
     */
    public function test_update_profile_requires_auth(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->putJson(
            '/api/shop/customer-profile-updates/'.$customer->id,
            ['firstName' => 'Updated'],
            $this->storefrontHeaders()
        );

        expect($response->getStatusCode())->toBeIn([401, 403, 500]);
    }

    /**
     * Test update profile with email of another customer is rejected.
     */
    public function test_update_profile_with_email_of_another_customer_is_rejected(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $other = $this->createCustomer();

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            ['email' => $other->email]
        );

        expect($response->getStatusCode())->toBe(400);
        expect($customer->fresh()->email)->not()->toBe($other->email);
    }

    /**
     * Test update profile with an invalid email is rejected and leaves the email unchanged.
     */
    public function test_update_profile_with_invalid_email_is_rejected(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            ['email' => 'not-an-email']
        );

        expect($response->getStatusCode())->toBe(400);
        expect($customer->fresh()->email)->not()->toBe('not-an-email');
    }

    /**
     * Test update profile accepts an email another channel's customer uses, as core does.
     */
    public function test_update_profile_with_email_used_on_another_channel_is_accepted(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();
        $other = $this->createCustomer(['channel_id' => 999]);

        $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            ['email' => $other->email]
        )->assertOk();

        expect($customer->fresh()->email)->toBe($other->email);
    }

    /**
     * Test update profile password mismatch returns error.
     */
    public function test_update_profile_password_mismatch_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            [
                'password' => 'NewPassword123!',
                'confirmPassword' => 'DifferentPassword!',
            ]
        );

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Test update password with correct current password succeeds.
     */
    public function test_update_password_with_correct_current_password_succeeds(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => Hash::make('OldPassword123!')]);
        $oldHash = $customer->fresh()->password;

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            [
                'currentPassword' => 'OldPassword123!',
                'password' => 'NewPassword456!',
                'confirmPassword' => 'NewPassword456!',
            ]
        );

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($customer->fresh()->password)->not->toBe($oldHash);
        expect(Hash::check('NewPassword456!', $customer->fresh()->password))->toBeTrue();
    }

    /**
     * Test update password with wrong current password returns error.
     */
    public function test_update_password_with_wrong_current_password_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => Hash::make('OldPassword123!')]);
        $oldHash = $customer->fresh()->password;

        $response = $this->authenticatedPut(
            $customer,
            '/api/shop/customer-profile-updates/'.$customer->id,
            [
                'currentPassword' => 'WrongPassword!',
                'password' => 'NewPassword456!',
                'confirmPassword' => 'NewPassword456!',
            ]
        );

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
        expect($customer->fresh()->password)->toBe($oldHash);
    }

    /**
     * Test cannot update other customers profile.
     */
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

        expect($otherCustomer->fresh()->first_name)->toBe($originalName);
    }

    /**
     * Test delete customer profile endpoint is reachable.
     */
    public function test_delete_customer_profile_endpoint_is_reachable(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->authenticatedPost(
            $customer,
            '/api/shop/customer-profile-deletes/'.$customer->id,
            ['password' => 'Password123!']
        );

        expect($response->getStatusCode())->toBeIn([200, 201, 204]);
    }

    /**
     * Test delete profile requires password.
     */
    public function test_delete_profile_requires_password(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->authenticatedPost($customer, '/api/shop/customer-profile-deletes/'.$customer->id, []);

        expect($response->getStatusCode())->toBe(422);
        expect(Customer::find($customer->id))->not()->toBeNull();
    }

    /**
     * Test delete profile rejects wrong password.
     */
    public function test_delete_profile_rejects_wrong_password(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        $response = $this->authenticatedPost(
            $customer,
            '/api/shop/customer-profile-deletes/'.$customer->id,
            ['password' => 'wrong-password']
        );

        expect($response->getStatusCode())->toBe(422);
        expect(Customer::find($customer->id))->not()->toBeNull();
    }

    /**
     * Test delete profile refused with pending orders.
     */
    public function test_delete_profile_refused_with_pending_orders(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);

        Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'status' => 'pending',
        ]);

        $response = $this->authenticatedPost(
            $customer,
            '/api/shop/customer-profile-deletes/'.$customer->id,
            ['password' => 'Password123!']
        );

        expect($response->getStatusCode())->toBe(422);
        expect(Customer::find($customer->id))->not()->toBeNull();
    }

    /**
     * Test delete profile removes the customer over rest.
     */
    public function test_delete_profile_removes_the_customer_over_rest(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['password' => bcrypt('Password123!')]);
        $customerId = $customer->id;

        $this->authenticatedPost(
            $customer,
            '/api/shop/customer-profile-deletes/'.$customerId,
            ['password' => 'Password123!']
        );

        expect(Customer::find($customerId))->toBeNull();
    }

    // ============================================================================
    // Registration
    // ============================================================================

    /**
     * Test register new customer.
     */
    public function test_register_new_customer(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'jane.doe.'.uniqid().'@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('id'))->toBeInt()->toBeGreaterThan(0);

        expect($response->json('channelId'))->not->toBeNull();
        expect($response->json('customerGroupId'))->not->toBeNull();
        $this->assertDatabaseHas('customers', [
            'id' => $response->json('id'),
            'channel_id' => $response->json('channelId'),
            'customer_group_id' => $response->json('customerGroupId'),
        ]);
    }

    /**
     * Test register withholds token when email verification is enabled.
     */
    public function test_register_withholds_token_when_email_verification_is_enabled(): void
    {
        $this->seedRequiredData();

        DB::table('core_config')->updateOrInsert(
            [
                'code' => 'customer.settings.email.verification',
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
            ], [
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        $this->forgetCoreConfigCache();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Verify',
            'lastName' => 'Me',
            'email' => 'verify.'.uniqid().'@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('token'))->toBeNull();
        expect($response->json('message'))->toBe(__('bagistoapi::app.graphql.customer.verify-email-to-login'));
        $this->assertDatabaseHas('customers', [
            'id' => $response->json('id'),
            'is_verified' => 0,
        ]);
    }

    /**
     * Test register accepts snake case body on shop endpoint.
     */
    public function test_register_accepts_snake_case_body_on_shop_endpoint(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'first_name' => 'Snake',
            'last_name' => 'Case',
            'email' => 'snake.'.uniqid().'@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        expect($response->getStatusCode())->toBeIn([201, 200]);
    }

    /**
     * Test registration returns sanctum token.
     */
    public function test_registration_returns_sanctum_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'jane.new.'.uniqid().'@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        $response->assertCreated();
        expect($response->json('token'))->toBeString()->toBe($response->json('accessToken'));
        expect($response->json('refreshToken'))->toBeString()->toContain('|');
    }

    /**
     * Test registration missing required fields returns error.
     */
    public function test_registration_missing_required_fields_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Test registration password mismatch returns error.
     */
    public function test_registration_password_mismatch_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => 'jane.'.uniqid().'@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'WrongPassword!',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422, 500]);
    }

    /**
     * Test registration duplicate email returns error.
     */
    public function test_registration_duplicate_email_returns_error(): void
    {
        $this->seedRequiredData();
        $existing = $this->createCustomer();

        $response = $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => $existing->email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        expect($response->getStatusCode())->toBeIn([400, 409, 422, 500]);
    }

    /**
     * Test registered customer persisted in database.
     */
    public function test_registered_customer_persisted_in_database(): void
    {
        $this->seedRequiredData();
        $email = 'persist.'.uniqid().'@example.com';

        $this->publicPost('/api/shop/customers', [
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'email' => $email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!',
        ]);

        expect(Customer::where('email', $email)->exists())->toBeTrue();
    }

    /**
     * Test verify token returns customer when authenticated.
     */
    public function test_verify_token_returns_customer_when_authenticated(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);

        $response = $this->authenticatedPost($customer, '/api/shop/verify-tokens');

        $response->assertCreated();
        expect($response->json('isValid'))->toBeTrue();
        expect($response->json('id'))->toBe($customer->id);
        expect($response->json('email'))->toBe($customer->email);
        expect($response->json('firstName'))->toBe('Ada');
        expect($response->json('lastName'))->toBe('Lovelace');
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test verify token fails without bearer token.
     */
    public function test_verify_token_fails_without_bearer_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/verify-tokens');

        $response->assertCreated();
        expect($response->json('isValid'))->toBeFalse();
        expect($response->json('id'))->toBe(0);
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test verify token fails for suspended customer.
     */
    public function test_verify_token_fails_for_suspended_customer(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer(['is_suspended' => 1]);

        $response = $this->authenticatedPost($customer, '/api/shop/verify-tokens');

        $response->assertCreated();
        expect($response->json('isValid'))->toBeFalse();
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test verify token accepts empty body.
     */
    public function test_verify_token_accepts_empty_body(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedPost($customer, '/api/shop/verify-tokens', []);

        $response->assertCreated();
        expect($response->json('isValid'))->toBeTrue();
    }

    /**
     * Test forgot password with valid email returns success.
     */
    public function test_forgot_password_with_valid_email_returns_success(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->publicPost('/api/shop/forgot-passwords', [
            'email' => $customer->email,
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeBool();
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test forgot password with unknown email returns failure.
     */
    public function test_forgot_password_with_unknown_email_returns_failure(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/forgot-passwords', [
            'email' => 'does-not-exist-'.uniqid().'@example.com',
        ]);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test forgot password repeat request reports throttling.
     */
    public function test_forgot_password_repeat_request_reports_throttling(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $this->publicPost('/api/shop/forgot-passwords', ['email' => $customer->email]);

        $response = $this->publicPost('/api/shop/forgot-passwords', ['email' => $customer->email]);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
        expect($response->json('message'))->toBe(__('bagistoapi::app.graphql.forgot-password.throttled'));
    }

    /**
     * Test forgot password missing email returns failure.
     */
    public function test_forgot_password_missing_email_returns_failure(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/forgot-passwords', []);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test forgot password empty email returns failure.
     */
    public function test_forgot_password_empty_email_returns_failure(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/forgot-passwords', ['email' => '']);

        $response->assertCreated();
        expect($response->json('success'))->toBeFalse();
    }
}
