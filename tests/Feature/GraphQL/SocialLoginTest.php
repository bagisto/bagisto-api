<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class SocialLoginTest extends GraphQLTestCase
{
    private string $mutation = <<<'GQL'
        mutation socialLogin($input: createSocialLoginInput!) {
          createSocialLogin(input: $input) {
            socialLogin {
              id
              _id
              token
              apiToken
              firstName
              lastName
              email
              isNewCustomer
              success
              message
              code
            }
          }
        }
    GQL;

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

    private function fakeGoogle(string $email): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response([
                'sub' => 'g-'.substr(md5($email), 0, 12),
                'email' => $email,
                'email_verified' => 'true',
                'name' => 'Nadia Rahman',
                'aud' => 'test-client-id.apps.googleusercontent.com',
            ], 200),
        ]);
    }

    private function node(array $input): array
    {
        return (array) $this->graphQL($this->mutation, ['input' => $input])
            ->json('data.createSocialLogin.socialLogin');
    }

    public function test_unsupported_provider_is_rejected(): void
    {
        $this->seedRequiredData();

        $node = $this->node(['provider' => 'github', 'idToken' => 'x']);

        expect($node['success'])->toBeFalse();
        expect($node['code'])->toBe('PROVIDER_NOT_SUPPORTED');
    }

    public function test_missing_token_is_rejected(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $node = $this->node(['provider' => 'google']);

        expect($node['success'])->toBeFalse();
        expect($node['code'])->toBe('SOCIAL_TOKEN_REQUIRED');
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        Http::fake(['oauth2.googleapis.com/*' => Http::response(['error' => 'invalid_token'], 400)]);

        $node = $this->node(['provider' => 'google', 'idToken' => 'bogus']);

        expect($node['success'])->toBeFalse();
        expect($node['code'])->toBe('SOCIAL_TOKEN_INVALID');
    }

    public function test_new_customer_is_created_and_gets_a_bearer_token(): void
    {
        $this->seedRequiredData();
        $this->enableGoogle();

        $email = 'social-gql-'.uniqid().'@example.com';
        $this->fakeGoogle($email);

        $node = $this->node(['provider' => 'google', 'idToken' => 'valid']);

        expect($node['success'])->toBeTrue();
        expect($node['token'])->not->toBeNull();
        expect($node['email'])->toBe($email);
        expect($node['firstName'])->toBe('Nadia');
        expect($node['isNewCustomer'])->toBeTrue();

        $this->assertDatabaseHas('customers', ['email' => $email, 'status' => 1]);
    }
}
