<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class AuthParityTest extends GraphQLTestCase
{
    // ====
    // Guarded queries refuse an anonymous caller
    // ====

    /**
     * Test every customer guarded query refuses an anonymous caller.
     */
    public function test_every_customer_guarded_query_refuses_an_anonymous_caller(): void
    {
        $this->enableGdpr();

        $guarded = [
            'readCustomerProfile' => '{ readCustomerProfile { firstName } }',
            'getCustomerAddresses' => '{ getCustomerAddresses { edges { node { id } } } }',
            'customerAddress' => '{ customerAddress(id: 1) { id } }',
            'customerOrders' => '{ customerOrders { edges { node { id } } } }',
            'customerOrder' => '{ customerOrder(id: 1) { id } }',
            'customerInvoices' => '{ customerInvoices { edges { node { id } } } }',
            'customerInvoice' => '{ customerInvoice(id: 1) { id } }',
            'customerOrderShipments' => '{ customerOrderShipments { edges { node { id } } } }',
            'customerOrderShipment' => '{ customerOrderShipment(id: 1) { id } }',
            'customerOrderShipmentItems' => '{ customerOrderShipmentItems { edges { node { id } } } }',
            'customerOrderShipmentItem' => '{ customerOrderShipmentItem(id: 1) { id } }',
            'customerDownloadableProducts' => '{ customerDownloadableProducts { edges { node { id } } } }',
            'customerDownloadableProduct' => '{ customerDownloadableProduct(id: 1) { id } }',
            'customerReviews' => '{ customerReviews { edges { node { id } } } }',
            'customerReview' => '{ customerReview(id: 1) { id } }',
            'wishlists' => '{ wishlists { edges { node { id } } } }',
            'wishlist' => '{ wishlist(id: 1) { id } }',
            'gdprRequests' => '{ gdprRequests { edges { node { id } } } }',
            'gdprRequest' => '{ gdprRequest(id: 1) { id } }',
            'gdprData' => '{ gdprData { customer } }',
            'gdprDataPdf' => '{ gdprDataPdf { fileName } }',
            'customerReturns' => '{ customerReturns { edges { node { id } } } }',
            'customerReturn' => '{ customerReturn(id: 1) { id } }',
            'customerReturnMessages' => '{ customerReturnMessages { id } }',
            'returnableOrders' => '{ returnableOrders { id } }',
            'returnableItems' => '{ returnableItems { id } }',
            'euWithdrawals' => '{ euWithdrawals { edges { node { id } } } }',
            'euWithdrawal' => '{ euWithdrawal(id: 1) { id } }',
            'euWithdrawalStatus' => '{ euWithdrawalStatus { status } }',
            'compareItems' => '{ compareItems { edges { node { id } } } }',
            'compareItem' => '{ compareItem(id: 1) { id } }',
            'returnReasons' => '{ returnReasons(resolutionType: "return") { id } }',
            'returnCustomFields' => '{ returnCustomFields { id } }',
            'collectionPaymentMethods' => '{ collectionPaymentMethods { id } }',
        ];

        foreach ($guarded as $name => $selection) {
            $response = $this->graphQL($selection);

            $response->assertOk();

            expect($response->json('errors'))
                ->not->toBeNull("{$name} answered data to an anonymous caller; expected an errors[] entry");
        }
    }

    /**
     * Test logout mutation refuses an anonymous caller.
     */
    public function test_logout_mutation_refuses_an_anonymous_caller(): void
    {
        $mutation = <<<'GQL'
            mutation Logout {
              createLogout(input: {}) {
                logout {
                  success
                  message
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertOk();

        expect($response->json('data.createLogout.logout.success'))->toBeFalse();
        expect($response->json('data.createLogout.logout.message'))->not->toBeEmpty();
    }

    /**
     * Test gdpr request mutation refuses an anonymous caller.
     */
    public function test_gdpr_request_mutation_refuses_an_anonymous_caller(): void
    {
        $mutation = <<<'GQL'
            mutation CreateGdprRequest {
              createGdprRequest(input: {type: "delete", message: "Auth parity"}) {
                gdprRequest {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test customer return mutation refuses an anonymous caller.
     */
    public function test_customer_return_mutation_refuses_an_anonymous_caller(): void
    {
        $mutation = <<<'GQL'
            mutation CreateCustomerReturn {
              createCustomerReturn(input: {orderId: 1}) {
                customerReturn {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test wishlist mutation refuses an anonymous caller.
     */
    public function test_wishlist_mutation_refuses_an_anonymous_caller(): void
    {
        $mutation = <<<'GQL'
            mutation ToggleWishlist {
              createWishlist(input: {productId: 1}) {
                wishlist {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test eu withdrawal mutation refuses an anonymous caller.
     */
    public function test_eu_withdrawal_mutation_refuses_an_anonymous_caller(): void
    {
        $mutation = <<<'GQL'
            mutation CreateEuWithdrawal {
              createEuWithdrawal(input: {orderId: 1}) {
                euWithdrawal {
                  id
                }
              }
            }
        GQL;

        $response = $this->graphQL($mutation);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeNull();
    }

    // ====
    // Public queries answer an anonymous caller
    // ====

    /**
     * Test public queries answer an anonymous caller.
     */
    public function test_public_queries_answer_an_anonymous_caller(): void
    {
        $public = [
            'storeConfig' => '{ storeConfig { guestCheckoutEnabled } }',
            'categories' => '{ categories { edges { node { id } } } }',
            'treeCategories' => '{ treeCategories { id } }',
            'attributes' => '{ attributes { edges { node { id } } } }',
            'attributeOptions' => '{ attributeOptions { edges { node { id } } } }',
            'countries' => '{ countries { edges { node { id } } } }',
            'countryStates' => '{ countryStates(countryId: 1) { edges { node { id } } } }',
            'bookingSlots' => '{ bookingSlots(id: 1, date: "2026-01-01") { id } }',
            'theme' => '{ theme { id } }',
            'sections' => '{ sections { edges { node { id } } } }',
            'pageByUrlKeypages' => '{ pageByUrlKeypages(urlKey: "home") { id } }',
            'gdprStatus' => '{ gdprStatus { enabled } }',
            'productReviews' => '{ productReviews { edges { node { id } } } }',
            'productDownloadableLinks' => '{ productDownloadableLinks { edges { node { id } } } }',
            'productDownloadableSamples' => '{ productDownloadableSamples { edges { node { id } } } }',
        ];

        foreach ($public as $name => $selection) {
            $response = $this->graphQL($selection);

            $response->assertOk();

            expect($response->json('errors'))
                ->toBeNull("{$name} answered ".json_encode($response->json('errors')).' to an anonymous caller');
        }
    }

    /**
     * Test product query carries no customer identity.
     */
    public function test_product_query_carries_no_customer_identity(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $query = <<<'GQL'
            query Product($id: ID!) {
              product(id: $id) {
                id
                name
              }
            }
        GQL;

        $json = (string) $this->graphQL($query, ['id' => $product->id])->getContent();

        foreach (['customer_email', 'customer_id', 'password', 'remember_token'] as $secret) {
            expect($json)->not->toContain($secret, "product query leaked {$secret}");
        }
    }

    /**
     * The GDPR queries gate on the core feature before authentication, so the feature must be on
     * for the refusal to come from the auth check.
     */
    private function enableGdpr(): void
    {
        DB::table('core_config')->where('code', 'general.gdpr.settings.enabled')->delete();
        DB::table('core_config')->insert([
            'code' => 'general.gdpr.settings.enabled',
            'value' => '1',
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->forgetCoreConfigCache();
    }
}
