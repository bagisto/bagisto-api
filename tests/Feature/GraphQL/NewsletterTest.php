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
}
