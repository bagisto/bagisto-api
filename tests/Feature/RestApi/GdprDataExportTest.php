<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Sales\Models\Order;

class GdprDataExportTest extends RestApiTestCase
{
    /**
     * Test gdpr data contains only the authenticated customers data.
     */
    public function test_gdpr_data_contains_only_the_authenticated_customers_data(): void
    {
        $this->seedRequiredData();
        $this->setGdprEnabled(true);
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer();

        Order::factory()->create([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'channel_id' => core()->getCurrentChannel()->id,
            'is_guest' => 0,
        ]);
        Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'customer_email' => $otherCustomer->email,
            'channel_id' => core()->getCurrentChannel()->id,
            'is_guest' => 0,
        ]);

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-data');

        $response->assertOk()
            ->assertJsonPath('customer.id', $customer->id)
            ->assertJsonPath('customer.email', $customer->email);

        expect($response->json('orders'))->toHaveCount(1)
            ->and($response->json('customer'))->not->toHaveKey('password')
            ->and($response->json('customer'))->not->toHaveKey('apiToken');
    }

    /**
     * Test gdpr data requires authentication.
     */
    public function test_gdpr_data_requires_authentication(): void
    {
        $this->seedRequiredData();
        $this->setGdprEnabled(true);

        $response = $this->publicGet('/api/shop/gdpr-data');

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    /**
     * Test gdpr data is disabled when the core feature is off.
     */
    public function test_gdpr_data_is_disabled_when_the_core_feature_is_off(): void
    {
        $this->seedRequiredData();
        $this->setGdprEnabled(false);
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-data');

        expect($response->getStatusCode())->toBeIn([400, 404]);
    }

    /**
     * Test gdpr pdf is downloaded for the authenticated customer.
     */
    public function test_gdpr_pdf_is_downloaded_for_the_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $this->setGdprEnabled(true);
        $customer = $this->createCustomer();

        $response = $this->authenticatedGet($customer, '/api/shop/gdpr-data/pdf');

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('application/pdf')
            ->and($response->headers->get('Content-Disposition'))->toContain('customerInfo.pdf');
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
    }

    /**
     * Set gdpr enabled.
     */
    private function setGdprEnabled(bool $enabled): void
    {
        DB::table('core_config')->where('code', 'general.gdpr.settings.enabled')->delete();
        DB::table('core_config')->insert([
            'code' => 'general.gdpr.settings.enabled',
            'value' => $enabled ? '1' : '0',
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => core()->getRequestedLocaleCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->forgetCoreConfigCache();
    }
}
