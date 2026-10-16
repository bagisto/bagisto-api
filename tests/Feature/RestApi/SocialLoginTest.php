<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Customer\Models\Customer;

class SocialLoginTest extends RestApiTestCase
{
    private string $url = '/api/shop/customers/social-login';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.client_id' => 'test-client-id.apps.googleusercontent.com']);

        $this->forgetCoreConfigCache();
    }

    private function enableGoogle(): void
    {
        DB::table('core_config')->where('code', 'customer.settings.social_login.enable_google')->delete();

        DB::table('core_config')->insert([
            'code' => 'customer.settings.social_login.enable_google',
            'value' => '1',
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

    public function test_google_token_is_refused_when_no_client_id_is_configured(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        config(['services.google.client_id' => null]);

        $this->fakeGoogle('social-unconfigured-'.uniqid().'@example.com');

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('PROVIDER_NOT_CONFIGURED');
    }

    public function test_google_token_for_another_app_is_refused(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $this->fakeGoogle('social-other-app-'.uniqid().'@example.com', ['aud' => 'other-app.apps.googleusercontent.com']);

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('SOCIAL_TOKEN_AUDIENCE');
    }

    public function test_google_access_token_for_another_app_is_refused(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        Http::fake(['oauth2.googleapis.com/*' => Http::response(['aud' => 'other-app', 'azp' => 'other-app'], 200)]);

        $response = $this->publicPost($this->url, ['provider' => 'google', 'accessToken' => 'valid']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('SOCIAL_TOKEN_AUDIENCE');
    }

    public function test_facebook_token_for_another_app_is_refused(): void
    {
        $this->seedRequiredData();
        $this->enableProvider('enable_facebook');

        config(['services.facebook.client_id' => '1111', 'services.facebook.client_secret' => 'secret']);

        Http::fake(['graph.facebook.com/*' => Http::response(['data' => ['is_valid' => true, 'app_id' => '9999']], 200)]);

        $response = $this->publicPost($this->url, ['provider' => 'facebook', 'accessToken' => 'valid']);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('code'))->toBe('SOCIAL_TOKEN_AUDIENCE');
    }

    public function test_login_fires_the_after_login_event(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $fired = 0;

        Event::listen('customer.after.login', function () use (&$fired) {
            $fired++;
        });

        $this->fakeGoogle('social-event-'.uniqid().'@example.com');

        $response = $this->publicPost($this->url, ['provider' => 'google', 'idToken' => 'valid']);

        expect($response->json('success'))->toBeTrue();
        expect($fired)->toBe(1);
    }

    private function enableProvider(string $key): void
    {
        DB::table('core_config')->where('code', 'customer.settings.social_login.'.$key)->delete();

        DB::table('core_config')->insert([
            'code' => 'customer.settings.social_login.'.$key,
            'value' => '1',
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->forgetCoreConfigCache();
    }
}
