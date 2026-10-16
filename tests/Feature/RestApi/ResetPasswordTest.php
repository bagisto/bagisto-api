<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class ResetPasswordTest extends RestApiTestCase
{
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
}
