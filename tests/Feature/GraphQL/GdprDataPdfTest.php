<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class GdprDataPdfTest extends GraphQLTestCase
{
    /**
     * Test gdpr pdf is available over graphql for the authenticated customer.
     */
    public function test_gdpr_pdf_is_available_over_graphql_for_the_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $this->enableGdpr();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGraphQL($customer, $this->gdprPdfQuery());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.gdprDataPdf');

        expect($data['fileName'])->toBe('customerInfo.pdf')
            ->and($data['mimeType'])->toBe('application/pdf')
            ->and(base64_decode($data['content']))->toStartWith('%PDF');
    }

    /**
     * Test gdpr pdf requires an authenticated customer.
     */
    public function test_gdpr_pdf_requires_an_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $this->enableGdpr();

        $response = $this->graphQL($this->gdprPdfQuery());

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test gdpr pdf is unavailable when gdpr is disabled.
     */
    public function test_gdpr_pdf_is_unavailable_when_gdpr_is_disabled(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGraphQL($customer, $this->gdprPdfQuery());

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
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

    /**
     * Gdpr pdf query.
     */
    private function gdprPdfQuery(): string
    {
        return <<<'GQL'
            query {
              gdprDataPdf {
                fileName
                mimeType
                size
                content
              }
            }
        GQL;
    }
}
