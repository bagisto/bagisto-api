<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;

class CompareAttributeTest extends RestApiTestCase
{
    /**
     * The comparable attributes are listed without name and price.
     */
    public function test_comparable_attributes_are_listed(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/compare-attributes');

        $response->assertOk();

        $codes = array_column($response->json(), 'code');

        expect($codes)->not->toContain('name')
            ->and($codes)->not->toContain('price');

        foreach ($response->json() as $attribute) {
            expect($attribute)->toHaveKeys(['id', 'code', 'adminName', 'name', 'type']);
        }
    }
}
