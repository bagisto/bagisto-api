<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class NewsletterTest extends GraphQLTestCase
{
    /**
     * Bug 4 regression — `createNewsletter` mutation lost `customerEmail`
     * because the camelCase JSON key never landed on the snake-case DTO
     * property. Processor now falls back to `$context['args']['input']`.
     * Verifies the hydration path: an authenticated subscribe must succeed.
     */
    public function test_create_newsletter_hydrates_customer_email_via_graphql(): void
    {
        $this->seedRequiredData();

        $customer = $this->createCustomer([
            'token' => md5(uniqid((string) rand(), true)),
        ]);

        $email = 'nl-gql-'.uniqid().'@example.com';

        $mutation = <<<'GQL'
            mutation subscribe($input: createNewsletterInput!) {
              createNewsletter(input: $input) {
                newsletter { success message }
              }
            }
        GQL;

        $response = $this->authenticatedGraphQL($customer, $mutation, [
            'input' => ['customerEmail' => $email],
        ]);

        $response->assertOk();
        // Before the fix the response carried success=false +
        // "customer email field is required". Now success=true.
        expect($response->json('data.createNewsletter.newsletter.success'))->toBeTrue();
        expect(DB::table('subscribers_list')->where('email', $email)->exists())->toBeTrue();
    }

    public function test_an_unsubscribed_email_can_subscribe_again(): void
    {
        $this->seedRequiredData();
        $email = 'nl-again-gql-'.uniqid().'@example.com';

        DB::table('subscribers_list')->insert([
            'email' => $email,
            'is_subscribed' => 0,
            'token' => 'old-token',
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        $response = $this->graphQL('mutation S($input: createNewsletterInput!) { createNewsletter(input: $input) { newsletter { success } } }', [
            'input' => ['customerEmail' => $email],
        ]);

        expect($response->json('data.createNewsletter.newsletter.success'))->toBeTrue();
        expect((int) DB::table('subscribers_list')->where('email', $email)->value('is_subscribed'))->toBe(1);
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

        $mutation = <<<'GQL'
            mutation unsubscribe($input: createNewsletterUnsubscribeInput!) {
              createNewsletterUnsubscribe(input: $input) {
                newsletterUnsubscribe {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation, ['input' => ['token' => $token]]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createNewsletterUnsubscribe.newsletterUnsubscribe.success'))->toBeTrue();
        $this->assertDatabaseMissing('subscribers_list', ['token' => $token]);
    }
}
