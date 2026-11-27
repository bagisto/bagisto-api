<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CompareAttributeTest extends GraphQLTestCase
{
    /**
     * The comparable attributes resolve with their multi-word fields.
     */
    public function test_comparable_attributes_resolve(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL('query { compareAttributes { _id code adminName name type } }');

        expect($response->json('errors'))->toBeNull();

        foreach ($response->json('data.compareAttributes') as $attribute) {
            expect($attribute['code'])->not->toBeIn(['name', 'price'])
                ->and($attribute['adminName'])->not->toBeNull();
        }
    }
}
