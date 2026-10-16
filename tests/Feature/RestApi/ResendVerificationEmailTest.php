<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Shop\Mail\Customer\EmailVerificationNotification;

class ResendVerificationEmailTest extends RestApiTestCase
{
    public function test_resend_verification_email_for_existing_customer_rotates_token(): void
    {
        Mail::fake();

        $this->seedRequiredData();

        $oldToken = 'old-token-'.uniqid();

        $customer = $this->createCustomer([
            'is_verified' => 0,
            'token' => $oldToken,
        ]);

        $response = $this->publicPost('/api/shop/verification-emails', [
            'email' => $customer->email,
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->json('success'));

        $customer->refresh();
        $this->assertNotSame($oldToken, (string) $customer->token);
        $this->assertSame(64, strlen((string) $customer->token));

        Mail::assertQueued(EmailVerificationNotification::class);
    }

    public function test_resend_verification_email_for_unknown_email_returns_generic_success(): void
    {
        Mail::fake();

        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/verification-emails', [
            'email' => 'unknown-'.uniqid().'@example.com',
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response->json('success'));

        Mail::assertNothingQueued();
    }

    public function test_resend_verification_email_with_invalid_email_fails(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/verification-emails', [
            'email' => 'not-an-email',
        ]);

        $this->assertFalse($response->json('success'));
    }

    public function test_resend_verification_email_with_missing_email_fails(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/verification-emails', []);

        $this->assertFalse($response->json('success'));
    }
}
