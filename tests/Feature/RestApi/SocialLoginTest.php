<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;

class SocialLoginTest extends RestApiTestCase
{
    private string $url = '/api/shop/customers/social-login';

    protected function setUp(): void
    {
        parent::setUp();

        $this->forgetCoreConfigCache();
    }

    private function enableGoogle(): void
    {
        DB::table('core_config')->whereIn('code', [
            'customer.settings.social_login.enable_google',
            'customer.settings.social_login.google_client_id',
        ])->delete();

        foreach ([
            'customer.settings.social_login.enable_google' => '1',
            'customer.settings.social_login.google_client_id' => 'test-client-id.apps.googleusercontent.com',
        ] as $code => $value) {
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    private function disableGoogle(): void
    {
        DB::table('core_config')->where('code', 'customer.settings.social_login.enable_google')->delete();

        $this->forgetCoreConfigCache();
    }

    private function fakeGoogle(string $email, array $overrides = []): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(array_merge([
                'sub' => 'g-'.substr(md5($email), 0, 12),
                'email' => $email,
                'email_verified' => 'true',
                'name' => 'Nadia Rahman',
                'aud' => 'test-client-id.apps.googleusercontent.com',
            ], $overrides), 200),
        ]);
    }

    public function test_unsupported_provider_is_rejected(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost($this->url, ['provider' => 'github', 'idToken' => 'x']);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('PROVIDER_NOT_SUPPORTED');
    }

    public function test_disabled_provider_is_rejected(): void
    {
        $this->seedRequiredData();
        $this->disableGoogle();

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'x']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('PROVIDER_DISABLED');
    }

    public function test_missing_token_is_rejected(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $response = $this->publicPost($this->url, ['provider' => 'google']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('SOCIAL_TOKEN_REQUIRED');
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        Http::fake(['oauth2.googleapis.com/*' => Http::response(['error' => 'invalid_token'], 400)]);

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'bogus']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('SOCIAL_TOKEN_INVALID');
    }

    public function test_google_fails_closed_without_a_client_id(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        DB::table('core_config')->where('code', 'customer.settings.social_login.google_client_id')->delete();
        config(['services.google.client_id' => null]);
        $this->forgetCoreConfigCache();

        $this->fakeGoogle('closed-'.uniqid().'@example.com');

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('SOCIAL_PROVIDER_NOT_CONFIGURED');
    }

    public function test_new_customer_is_created_and_gets_a_bearer_token(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $email = 'social-new-'.uniqid().'@example.com';
        $this->fakeGoogle($email);

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('token'))->not->toBeNull();
        expect($response->json('email'))->toBe($email);
        expect($response->json('firstName'))->toBe('Nadia');
        expect($response->json('lastName'))->toBe('Rahman');
        expect($response->json('isNewCustomer'))->toBeTrue();

        $this->assertDatabaseHas('customers', ['email' => $email, 'status' => 1]);
    }

    public function test_existing_email_is_linked_not_duplicated(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $email = 'social-existing-'.uniqid().'@example.com';
        $customer = $this->createCustomer(['email' => $email]);

        $this->fakeGoogle($email);

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->json('success'))->toBeTrue();
        expect($response->json('isNewCustomer'))->toBeFalse();
        expect((int) $response->json('id'))->toBe((int) $customer->id);
        expect(Customer::where('email', $email)->count())->toBe(1);
    }

    public function test_repeat_login_returns_same_account(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $email = 'social-repeat-'.uniqid().'@example.com';
        $this->fakeGoogle($email);

        $first = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);
        $second = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($first->json('isNewCustomer'))->toBeTrue();
        expect($second->json('isNewCustomer'))->toBeFalse();
        expect((int) $second->json('id'))->toBe((int) $first->json('id'));
    }

    public function test_inactive_account_is_blocked(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $email = 'social-inactive-'.uniqid().'@example.com';
        $this->createCustomer(['email' => $email, 'status' => 0]);

        $this->fakeGoogle($email);

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('ACCOUNT_INACTIVE');
    }
}
