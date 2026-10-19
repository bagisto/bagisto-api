<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Shop\Mail\ContactUs;

class ContactUsTest extends RestApiTestCase
{
    /**
     * The url.
     */
    private string $url = '/api/shop/contact-us';

    // ── Success ───────────────────────────────────────────────

    /**
     * Test submit returns created.
     */
    public function test_submit_returns_created(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, $this->validPayload());

        $response->assertCreated();
    }

    /**
     * Test submit returns success true.
     */
    public function test_submit_returns_success_true(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, $this->validPayload());

        $response->assertCreated();
        expect($response->json('success'))->toBeTrue();
    }

    /**
     * Test submit returns success message.
     */
    public function test_submit_returns_success_message(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, $this->validPayload());

        $response->assertCreated();
        expect($response->json('message'))->toBeString()->not()->toBeEmpty();
    }

    /**
     * Test submit queues contact us mail.
     */
    public function test_submit_queues_contact_us_mail(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $this->publicPost($this->url, $this->validPayload());

        Mail::assertQueued(ContactUs::class);
    }

    /**
     * Test submit without optional contact field.
     */
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

    /**
     * Test missing name returns 400.
     */
    public function test_missing_name_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['name']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    /**
     * Test missing email returns 400.
     */
    public function test_missing_email_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['email']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    /**
     * Test invalid email returns 400.
     */
    public function test_invalid_email_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        $payload['email'] = 'not-an-email';

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    /**
     * Test missing message returns 400.
     */
    public function test_missing_message_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $payload = $this->validPayload();
        unset($payload['message']);

        $response = $this->publicPost($this->url, $payload);

        $response->assertStatus(400);
    }

    /**
     * Test empty body returns 400.
     */
    public function test_empty_body_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, []);

        $response->assertStatus(400);
    }

    // ── GraphQL-style wrapper is rejected ─────────────────────

    /**
     * Test graphql input wrapper returns 400.
     */
    public function test_graphql_input_wrapper_returns_400(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        $response = $this->publicPost($this->url, [
            'input' => $this->validPayload(),
        ]);

        $response->assertStatus(400);
    }

    /**
     * Valid payload.
     */
    private function validPayload(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'contact' => '+1234567890',
            'message' => 'I have a question about your products',
        ];
    }
}
