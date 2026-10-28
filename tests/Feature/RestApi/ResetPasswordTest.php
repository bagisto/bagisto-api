<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class ResetPasswordTest extends RestApiTestCase
{
    /**
     * Test reset password with valid token succeeds.
     */
    public function test_reset_password_with_valid_token_succeeds(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'email' => 'reset_test_'.uniqid().'@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $token = Password::broker('customers')->createToken($customer);

        $response = $this->publicPost('/api/shop/reset-passwords', [
            'token' => $token,
            'email' => $customer->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->json('success'));

        $customer->refresh();
        $this->assertTrue(Hash::check('new-password-123', $customer->password));
    }

    /**
     * Test reset password with invalid token fails.
     */
    public function test_reset_password_with_invalid_token_fails(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'email' => 'reset_fail_'.uniqid().'@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->publicPost('/api/shop/reset-passwords', [
            'token' => 'invalid-token-xyz',
            'email' => $customer->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $this->assertFalse($response->json('success'));
    }

    /**
     * Test reset password with mismatched confirmation fails.
     */
    public function test_reset_password_with_mismatched_confirmation_fails(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'email' => 'reset_mismatch_'.uniqid().'@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $token = Password::broker('customers')->createToken($customer);

        $response = $this->publicPost('/api/shop/reset-passwords', [
            'token' => $token,
            'email' => $customer->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ]);

        $this->assertFalse($response->json('success'));
    }

    /**
     * Test a password reset ends every API session of the customer, and only theirs.
     */
    public function test_reset_password_ends_every_session(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'email' => 'reset_sessions_'.uniqid().'@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $first = $this->customerTokenPair($customer);
        $second = $this->customerTokenPair($customer);
        $other = $this->customerTokenPair($this->createCustomer());

        $this->getJson('/api/shop/customer-profile', $this->bearerHeaders($first->accessToken))->assertOk();

        $this->publicPost('/api/shop/reset-passwords', [
            'token' => Password::broker('customers')->createToken($customer),
            'email' => $customer->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSuccessful();

        $this->assertSame(CustomerApiSession::REVOKED_PASSWORD_RESET, $first->session->fresh()->revoked_reason);
        $this->assertSame(CustomerApiSession::REVOKED_PASSWORD_RESET, $second->session->fresh()->revoked_reason);
        $this->assertNull($other->session->fresh()->revoked_at);

        $this->getJson('/api/shop/customer-profile', $this->bearerHeaders($first->accessToken))
            ->assertStatus(401)
            ->assertJsonPath('error', 'unauthenticated');

        $this->postJson('/api/shop/customer/refresh', ['refresh_token' => $second->refreshToken], $this->storefrontHeaders())
            ->assertStatus(401)
            ->assertJsonPath('error', 'refresh_token_invalid');
    }

    /**
     * The storefront headers with a bearer token.
     */
    protected function bearerHeaders(string $token): array
    {
        return array_merge($this->storefrontHeaders(), ['Authorization' => 'Bearer '.$token]);
    }
}
