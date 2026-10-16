<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Shop\Mail\Customer\EmailVerificationNotification;

class ResendVerificationEmailTest extends GraphQLTestCase
{
    private const MUTATION = <<<'GQL'
        mutation resend($input: createResendVerificationEmailInput!) {
          createResendVerificationEmail(input: $input) {
            resendVerificationEmail { success message }
          }
        }
    GQL;

    public function test_resend_verification_email_for_existing_customer_rotates_token(): void
    {
        Mail::fake();

        $this->seedRequiredData();

        $oldToken = 'old-token-'.uniqid();

        $customer = $this->createCustomer([
            'is_verified' => 0,
            'token' => $oldToken,
        ]);

        $response = $this->graphQL(self::MUTATION, [
            'input' => ['email' => $customer->email],
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.createResendVerificationEmail.resendVerificationEmail');
        $this->assertTrue($data['success']);

        $customer->refresh();
        $this->assertNotSame($oldToken, (string) $customer->token);

        Mail::assertQueued(EmailVerificationNotification::class);
    }

    public function test_resend_verification_email_for_unknown_email_returns_generic_success(): void
    {
        Mail::fake();

        $this->seedRequiredData();

        $response = $this->graphQL(self::MUTATION, [
            'input' => ['email' => 'unknown-'.uniqid().'@example.com'],
        ]);

        $response->assertSuccessful();

        $data = $response->json('data.createResendVerificationEmail.resendVerificationEmail');
        $this->assertTrue($data['success']);

        Mail::assertNothingQueued();
    }
}
