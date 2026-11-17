<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ProductConfigurableConfigTest extends GraphQLTestCase
{
    /**
     * The query every test sends.
     */
    private const QUERY = 'query P($id: ID!) { product(id: $id) { _id type configurableConfig } }';

    /**
     * A configurable product carries the option picker configuration as JSON.
     */
    public function test_a_configurable_product_carries_its_option_configuration(): void
    {
        $product = $this->createBaseProduct('configurable');

        $response = $this->graphQL(self::QUERY, ['id' => '/api/shop/products/'.$product->id]);

        expect($response->json('errors'))->toBeNull();

        $config = json_decode((string) $response->json('data.product.configurableConfig'), true);

        expect($config)->toBeArray()
            ->and($config)->toHaveKeys(['attributes', 'index']);
    }

    /**
     * A simple product carries no option configuration.
     */
    public function test_a_simple_product_has_no_option_configuration(): void
    {
        $product = $this->createBaseProduct('simple');

        $response = $this->graphQL(self::QUERY, ['id' => '/api/shop/products/'.$product->id]);

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.product.configurableConfig'))->toBeNull();
    }
}
