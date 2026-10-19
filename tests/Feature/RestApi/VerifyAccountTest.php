<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;

class VerifyAccountTest extends RestApiTestCase
{
    /**
     * Test verify account with valid token verifies customer.
     */
    public function test_verify_account_with_valid_token_verifies_customer(): void
    {
        $this->seedRequiredData();

        $token = 'verify_tok_'.uniqid();

        $customer = $this->createCustomer([
            'is_verified' => 0,
            'token' => $token,
        ]);

        $response = $this->publicPost('/api/shop/verify-accounts', [
            'token' => $token,
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->json('success'));

        $customer->refresh();
        $this->assertEquals(1, $customer->is_verified);
        $this->assertNull($customer->token);
    }

    /**
     * Test verify account with invalid token fails.
     */
    public function test_verify_account_with_invalid_token_fails(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/verify-accounts', [
            'token' => 'nonexistent_token_123',
        ]);

        $this->assertFalse($response->json('success'));
    }

    /**
     * Test verify account missing token fails.
     */
    public function test_verify_account_missing_token_fails(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/verify-accounts', []);

        $this->assertFalse($response->json('success'));
    }
}
