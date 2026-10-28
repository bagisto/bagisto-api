<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AuthParityTest extends RestApiTestCase
{
    /**
     * Customer-guarded collection endpoints: a bare list must refuse without a token.
     *
     * @var list<string>
     */
    private const GUARDED_COLLECTIONS = [
        '/api/shop/customer-profile',
        '/api/shop/customer-addresses',
        '/api/shop/customer-orders',
        '/api/shop/customer-invoices',
        '/api/shop/customer-order-shipments',
        '/api/shop/customer-order-shipment-items',
        '/api/shop/customer-downloadable-products',
        '/api/shop/customer-reviews',
        '/api/shop/wishlists',
        '/api/shop/gdpr-requests',
        '/api/shop/gdpr-data',
        '/api/shop/returns',
        '/api/shop/return-messages',
        '/api/shop/returnable-orders',
        '/api/shop/returnable-items',
        '/api/shop/eu-withdrawals',
        '/api/shop/return-reasons',
        '/api/shop/return-custom-fields',
    ];

    /**
     * Public collection endpoints: a bare list must answer without a token.
     *
     * @var list<string>
     */
    private const PUBLIC_COLLECTIONS = [
        '/api/shop/products',
        '/api/shop/categories',
        '/api/shop/category-trees',
        '/api/shop/attributes',
        '/api/shop/attribute-options',
        '/api/shop/countries',
        '/api/shop/country-states',
        '/api/shop/store-config',
        '/api/shop/theme',
        '/api/shop/sections',
        '/api/shop/pages?url_key=home',
        '/api/shop/reviews',
        '/api/shop/product-downloadable-links',
        '/api/shop/product-downloadable-samples',
        '/api/shop/gdpr-status',
        '/api/shop/eu-withdrawal-status',
    ];

    /**
     * Public POST endpoints that must accept an anonymous caller.
     *
     * @var array<string, array<string, mixed>>
     */
    private const PUBLIC_POSTS = [
        '/api/shop/contact-us' => [
            'name' => 'Api',
            'email' => 'api@example.com',
            'contact' => '+10000000000',
            'message' => 'Anonymous contact must be accepted.',
        ],

        '/api/shop/newsletters' => [
            'customerEmail' => 'anonymous-newsletter@example.com',
        ],
    ];

    // ============================================================================
    // Guarded Endpoints Refuse An Anonymous Caller
    // ============================================================================

    /**
     * Test every customer guarded collection refuses an anonymous caller.
     */
    public function test_every_customer_guarded_collection_refuses_an_anonymous_caller(): void
    {
        $this->enableGdpr();

        foreach (self::GUARDED_COLLECTIONS as $url) {
            $status = $this->publicGet($url)->getStatusCode();

            expect(in_array($status, [401, 403], true))
                ->toBeTrue("{$url} answered {$status} to an anonymous caller; expected 401 or 403");
        }
    }

    /**
     * Test customer profile refuses an anonymous caller.
     */
    public function test_customer_profile_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicGet('/api/shop/customer-profile');

        $response->assertStatus(401);

        expect($response->json('status'))->toBe(401);
        expect($response->json('detail'))->not->toBeEmpty();
    }

    /**
     * Test logout refuses an anonymous caller.
     */
    public function test_logout_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/customer/logout');

        expect($response->getStatusCode())->toBe(201);
        expect($response->json('success'))->toBeFalse();
        expect($response->json('message'))->not->toBeEmpty();
    }

    /**
     * Test order cancel refuses an anonymous caller.
     */
    public function test_order_cancel_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/cancel-order', ['order_id' => 1]);

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('cancel-order answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test reorder refuses an anonymous caller.
     */
    public function test_reorder_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/reorder', ['order_id' => 1]);

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('reorder answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test gdpr request create refuses an anonymous caller.
     */
    public function test_gdpr_request_create_refuses_an_anonymous_caller(): void
    {
        $this->enableGdpr();

        $response = $this->publicPost('/api/shop/gdpr-requests', [
            'type' => 'html',
            'email' => 'api@example.com',
        ]);

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('gdpr-requests answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test return create refuses an anonymous caller.
     */
    public function test_return_create_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/returns', ['order_id' => 1]);

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('returns answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test eu withdrawal create refuses an anonymous caller.
     */
    public function test_eu_withdrawal_create_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/eu-withdrawals', ['order_id' => 1]);

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('eu-withdrawals answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test wishlist toggle refuses an anonymous caller.
     */
    public function test_wishlist_toggle_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/wishlists/toggle', ['product_id' => 1]);

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('wishlists/toggle answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test delete all wishlists refuses an anonymous caller.
     */
    public function test_delete_all_wishlists_refuses_an_anonymous_caller(): void
    {
        $response = $this->publicPost('/api/shop/delete-all-wishlists');

        expect(in_array($response->getStatusCode(), [401, 403], true))
            ->toBeTrue('delete-all-wishlists answered '.$response->getStatusCode().' to an anonymous caller');
    }

    /**
     * Test payment methods refuses a caller without a cart token.
     */
    public function test_payment_methods_refuses_a_caller_without_a_cart_token(): void
    {
        $response = $this->publicGet('/api/shop/payment-methods');

        expect(in_array($response->getStatusCode(), [401, 403, 404], true))
            ->toBeTrue('payment-methods answered '.$response->getStatusCode().' to a caller with no cart token');
    }

    // ============================================================================
    // Public Endpoints Answer An Anonymous Caller
    // ============================================================================

    /**
     * Test every public collection answers an anonymous caller.
     */
    public function test_every_public_collection_answers_an_anonymous_caller(): void
    {
        foreach (self::PUBLIC_COLLECTIONS as $url) {
            $status = $this->publicGet($url)->getStatusCode();

            expect($status)->toBe(200, "{$url} answered {$status} to an anonymous caller; expected 200");
        }
    }

    /**
     * Test public post endpoints answer an anonymous caller.
     */
    public function test_public_post_endpoints_answer_an_anonymous_caller(): void
    {
        $this->seedRequiredData();
        Mail::fake();

        foreach (self::PUBLIC_POSTS as $url => $body) {
            $status = $this->publicPost($url, $body)->getStatusCode();

            expect($status)->toBeGreaterThanOrEqual(200);
            expect($status)->toBeLessThan(300);
        }
    }

    /**
     * Test product detail carries no customer identity.
     */
    public function test_product_detail_carries_no_customer_identity(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        $response->assertOk();

        $json = (string) $response->getContent();

        foreach (['customer_email', 'customer_id', 'password', 'remember_token'] as $secret) {
            expect($json)->not->toContain($secret, "product detail leaked {$secret}");
        }
    }

    /**
     * Test category list carries no customer identity.
     */
    public function test_category_list_carries_no_customer_identity(): void
    {
        $json = (string) $this->publicGet('/api/shop/categories')->getContent();

        foreach (['customer_email', 'customer_id', 'password', 'remember_token'] as $secret) {
            expect($json)->not->toContain($secret, "category list leaked {$secret}");
        }
    }

    /**
     * The GDPR endpoints gate on the core feature before authentication, so the feature must be on
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
