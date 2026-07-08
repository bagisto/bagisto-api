<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ContactUsTest extends GraphQLTestCase
{
    /**
     * Create Contact Us - Basic
     *
     * This mutation does not require authentication - it is available to all visitors
     */
    public function test_create_contact_us_basic(): void
    {
        Mail::fake();

        $query = <<<'GQL'
            mutation createContactUs($input: createContactUsInput!) {
                createContactUs(input: $input) {
                    contactUs {
                        success
                        message
                    }
                }
            }
        GQL;

        $variables = [
            'input' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'contact' => '+1234567890',
                'message' => 'I have a question about your products',
            ],
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('createContactUs', $response->json('data'));
        $this->assertArrayHasKey('contactUs', $response->json('data.createContactUs'));

        $contactUs = $response->json('data.createContactUs.contactUs');

        $this->assertTrue($contactUs['success'], 'Contact Us submission should be successful');
        $this->assertEquals(
            'Your inquiry has been submitted successfully. We will get back to you soon',
            $contactUs['message']
        );
    }

    /**
     * Create Contact Us - With Client Mutation ID
     *
     * This mutation does not require authentication - it is available to all visitors
     */
    public function test_create_contact_us_with_client_mutation_id(): void
    {
        Mail::fake();

        $query = <<<'GQL'
            mutation createContactUs($input: createContactUsInput!) {
                createContactUs(input: $input) {
                    contactUs {
                        success
                        message
                    }
                    clientMutationId
                }
            }
        GQL;

        $variables = [
            'input' => [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'contact' => '+0987654321',
                'message' => 'I would like to inquire about bulk order discounts for your clothing range.',
                'clientMutationId' => 'contact-form-001',
            ],
        ];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('createContactUs', $response->json('data'));
        $this->assertArrayHasKey('contactUs', $response->json('data.createContactUs'));
        $this->assertArrayHasKey('clientMutationId', $response->json('data.createContactUs'));

        $contactUs = $response->json('data.createContactUs.contactUs');

        $this->assertTrue($contactUs['success'], 'Contact Us submission should be successful');
        $this->assertEquals(
            'Your inquiry has been submitted successfully. We will get back to you soon',
            $contactUs['message']
        );

        // Verify clientMutationId is returned correctly
        $this->assertEquals(
            'contact-form-001',
            $response->json('data.createContactUs.clientMutationId')
        );
    }
}
