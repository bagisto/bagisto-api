<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class NewsletterTest extends RestApiTestCase
{
    private string $url = '/api/shop/newsletters';

    public function test_subscribe_success(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $email = 'nl-'.uniqid().'@example.com';

        $response = $this->authenticatedPost($customer, $this->url, [
            'customerEmail' => $email,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);

        $json = $response->json();
        expect($json['success'])->toBeTrue();

        expect(DB::table('subscribers_list')
            ->where('email', $email)
            ->where('is_subscribed', 1)
            ->exists())->toBeTrue();
    }

    public function test_subscribe_duplicate_email_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $email = 'nl-dup-'.uniqid().'@example.com';

        $this->authenticatedPost($customer, $this->url, ['customerEmail' => $email])
            ->assertSuccessful();

        $response = $this->authenticatedPost($customer, $this->url, [
            'customerEmail' => $email,
        ]);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    public function test_subscribe_missing_email_returns_error(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $response = $this->authenticatedPost($customer, $this->url, []);

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    public function test_subscribe_requires_auth(): void
    {
        $this->seedRequiredData();
        $email = 'nl-pub-'.uniqid().'@example.com';

        $response = $this->publicPost($this->url, [
            'customerEmail' => $email,
        ]);

        // The processor's try/catch swallows AuthorizationException into a
        // success:false body. Either the HTTP status signals the failure, or
        // the JSON body does.
        $body = $response->json();
        $passed = in_array($response->getStatusCode(), [401, 403, 500], true)
            || ($body['success'] ?? null) === false;
        expect($passed)->toBeTrue();

        expect(DB::table('subscribers_list')
            ->where('email', $email)
            ->exists())->toBeFalse();
    }
}
