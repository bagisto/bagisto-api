<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Shop\Mail\ContactUs;

class ContactUsTest extends RestApiTestCase
{
    private string $url = '/api/shop/contact-us';

    private function validPayload(): array
    {
        return [
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'contact' => '+1234567890',
            'message' => 'I have a question about your products',
        ];
    }

    // ── Success ───────────────────────────────────────────────

    public function test_submit_returns_created(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, $this->validPayload());

        $response->assertCreated();
    }

    public function test_submit_returns_success_true(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, $this->validPayload());

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
    }

    public function test_submit_returns_success_message(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, $this->validPayload());

        $response->assertCreated();
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    public function test_submit_queues_contact_us_mail(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $this->publicPost($this->url, $this->validPayload());

        Mail::assertQueued(ContactUs::class);
    }

    public function test_submit_without_optional_contact_field(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['contact']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
    }

    // ── Validation ────────────────────────────────────────────

    public function test_missing_name_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['name']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    public function test_missing_email_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['email']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    public function test_invalid_email_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        $payload['email'] = 'not-an-email';

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    public function test_missing_message_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['message']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    public function test_empty_body_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, []);

        $response->assertStatus(400);
    }

    // ── GraphQL-style wrapper is rejected ─────────────────────

    public function test_graphql_input_wrapper_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        // Sending {"input": {...}} is the GraphQL format — REST expects flat body.
        $response = $this->publicPost($this->url, [
            'input' => $this->validPayload(),
        ]);

        $response->assertStatus(400);
    }
}
