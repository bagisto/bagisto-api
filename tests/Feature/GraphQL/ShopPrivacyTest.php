<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Sales\Models\Order;

class ShopPrivacyTest extends GraphQLTestCase
{
    /**
     * Test newsletter can be unsubscribed through graphql.
     */
    public function test_newsletter_can_be_unsubscribed_through_graphql(): void
    {
        $this->seedRequiredData();
        $email = 'gql-unsubscribe-'.uniqid().'@example.com';

        $subscribe = $this->graphQL(<<<'GQL'
            mutation createNewsletter($email: String!) {
              createNewsletter(input: {customerEmail: $email}) {
                newsletter { success }
              }
            }
            GQL, ['email' => $email]);

        $subscribe->assertOk();
        expect($subscribe->json('errors'))->toBeNull();

        $token = (string) DB::table('subscribers_list')->where('email', $email)->value('token');

        $response = $this->graphQL(<<<'GQL'
            mutation createNewsletterUnsubscription($token: String!) {
              createNewsletterUnsubscription(input: {token: $token}) {
                newsletterUnsubscription { success }
              }
            }
            GQL, ['token' => $token]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.createNewsletterUnsubscription.newsletterUnsubscription.success'))->toBeTrue()
            ->and(DB::table('subscribers_list')->where('email', $email)->exists())->toBeFalse();
    }

    /**
     * Test gdpr data is available through graphql for the authenticated customer.
     */
    public function test_gdpr_data_is_available_through_graphql_for_the_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $this->enableGdpr();
        $customer = $this->createCustomer();

        Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => core()->getCurrentChannel()->id,
            'is_guest' => 0,
        ]);

        $response = $this->authenticatedGraphQL($customer, <<<'GQL'
            query {
              gdprData {
                customer
                addresses
                orders
              }
            }
            GQL);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.gdprData.customer.email'))->toBe($customer->email)
            ->and($response->json('data.gdprData.orders'))->toHaveCount(1);
    }

    /**
     * Enable gdpr.
     */
    private function enableGdpr(): void
    {
        DB::table('core_config')->updateOrInsert(
            [
                'code' => 'general.gdpr.settings.enabled',
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => core()->getRequestedLocaleCode(),
            ],
            ['value' => '1']
        );
        $this->forgetCoreConfigCache();
    }
}
