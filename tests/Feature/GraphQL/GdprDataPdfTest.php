<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class GdprDataPdfTest extends GraphQLTestCase
{
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

    public function test_gdpr_pdf_requires_an_authenticated_customer(): void
    {
        $this->seedRequiredData();
        $this->enableGdpr();

        $response = $this->graphQL($this->gdprPdfQuery());

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_gdpr_pdf_is_unavailable_when_gdpr_is_disabled(): void
    {
        $this->seedRequiredData();
        $customer = $this->createCustomer();

        $response = $this->authenticatedGraphQL($customer, $this->gdprPdfQuery());

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
