<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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

    public function test_an_unsubscribed_email_can_subscribe_again(): void
    {
        $this->seedRequiredData();
        $email = 'nl-again-'.uniqid().'@example.com';

        DB::table('subscribers_list')->insert([
            'email' => $email,
            'is_subscribed' => 0,
            'token' => 'old-token',
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        $this->publicPost($this->url, ['customerEmail' => $email])->assertSuccessful();

        expect(DB::table('subscribers_list')->where('email', $email)->count())->toBe(1);
        expect((int) DB::table('subscribers_list')->where('email', $email)->value('is_subscribed'))->toBe(1);
    }

    public function test_subscribing_fires_the_core_events_and_flags_the_customer(): void
    {
        $this->seedRequiredData();
        Event::fake(['customer.subscription.before', 'customer.subscription.after']);
        $customer = $this->createCustomer(['subscribed_to_news_letter' => 0]);

        $this->authenticatedPost($customer, $this->url, ['customerEmail' => 'nl-flag-'.uniqid().'@example.com'])
            ->assertSuccessful();

        Event::assertDispatched('customer.subscription.before');
        Event::assertDispatched('customer.subscription.after');
        expect((bool) $customer->fresh()->subscribed_to_news_letter)->toBeTrue();
    }

    public function test_unsubscribe_removes_the_subscription(): void
    {
        $this->seedRequiredData();
        $token = uniqid('unsub-');
        DB::table('subscribers_list')->insert([
            'email' => 'unsub-'.uniqid().'@example.com',
            'is_subscribed' => 1,
            'token' => $token,
            'channel_id' => core()->getCurrentChannel()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->publicPost('/api/shop/newsletters/unsubscribe', ['token' => $token]);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
        $this->assertDatabaseMissing('subscribers_list', ['token' => $token]);
    }

    public function test_unsubscribe_with_an_unknown_token_gives_the_same_answer(): void
    {
        $this->seedRequiredData();

        $response = $this->publicPost('/api/shop/newsletters/unsubscribe', ['token' => 'no-such-token']);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
    }

    public function test_unsubscribe_requires_a_token(): void
    {
        $this->seedRequiredData();

        $this->publicPost('/api/shop/newsletters/unsubscribe', [])->assertStatus(422);
    }
}
