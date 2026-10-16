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

    public function test_unsubscribed_email_can_resubscribe(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $email = 'nl-re-'.uniqid().'@example.com';

        $this->authenticatedPost($customer, $this->url, ['customerEmail' => $email])
            ->assertSuccessful();

        DB::table('subscribers_list')->where('email', $email)->update(['is_subscribed' => 0]);

        $response = $this->authenticatedPost($customer, $this->url, ['customerEmail' => $email]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();
        expect(DB::table('subscribers_list')->where('email', $email)->count())->toBe(1);
        expect((int) DB::table('subscribers_list')->where('email', $email)->value('is_subscribed'))->toBe(1);
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

    public function test_guest_can_subscribe_without_a_token(): void
    {
        $this->seedRequiredData();
        $email = 'nl-pub-'.uniqid().'@example.com';

        $response = $this->publicPost($this->url, [
            'customerEmail' => $email,
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('success'))->toBeTrue();

        expect(DB::table('subscribers_list')
            ->where('email', $email)
            ->where('is_subscribed', 1)
            ->whereNull('customer_id')
            ->exists())->toBeTrue();
    }

    public function test_subscription_by_a_customer_is_linked_to_that_customer(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $email = 'nl-linked-'.uniqid().'@example.com';

        $this->authenticatedPost($customer, $this->url, ['customerEmail' => $email])
            ->assertSuccessful();

        expect(DB::table('subscribers_list')
            ->where('email', $email)
            ->where('customer_id', $customer->id)
            ->exists())->toBeTrue();
    }

    public function test_unsubscribe_deletes_the_subscription_by_token(): void
    {
        $this->seedRequiredData();
        $email = 'nl-unsubscribe-'.uniqid().'@example.com';

        $this->publicPost($this->url, ['customerEmail' => $email])->assertSuccessful();

        $token = DB::table('subscribers_list')->where('email', $email)->value('token');

        $response = $this->publicPost('/api/shop/newsletters/unsubscribe', ['token' => $token]);

        $response->assertSuccessful();
        expect($response->json('success'))->toBeTrue();
        expect(DB::table('subscribers_list')->where('email', $email)->exists())->toBeFalse();
    }

    public function test_unsubscribe_requires_a_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/newsletters/unsubscribe');

        expect($response->getStatusCode())->toBeIn([400, 422]);
    }

    public function test_unsubscribe_returns_generic_success_for_an_unknown_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/newsletters/unsubscribe', ['token' => 'unknown-token']);

        $response->assertSuccessful();
        expect($response->json('success'))->toBeTrue();
    }
}
