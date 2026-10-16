<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\ProductDownloadableSample;

class ProductDownloadableSampleTest extends GraphQLTestCase
{
    /**
     * The productDownloadableSamples query lists the downloadable samples, including a new one.
     */
    public function test_product_downloadable_samples_query_lists_samples(): void
    {
        $sample = $this->seedDownloadableSample();

        $response = $this->graphQL(<<<'GQL'
            query {
              productDownloadableSamples(first: 100) {
                edges {
                  node {
                    _id
                    type
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $ids = collect($response->json('data.productDownloadableSamples.edges'))->pluck('node._id')->all();

        expect($response->json('errors'))->toBeNull()
            ->and($ids)->toContain($sample->id);
    }

    /**
     * The productDownloadableSample query returns one sample with its URL.
     */
    public function test_product_downloadable_sample_query_returns_sample(): void
    {
        $sample = $this->seedDownloadableSample();

        $response = $this->graphQL(<<<GQL
            query {
              productDownloadableSample(id: "/api/shop/product-downloadable-samples/{$sample->id}") {
                _id
                type
                url
                product {
                  _id
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productDownloadableSample._id'))->toBe($sample->id)
            ->and($response->json('data.productDownloadableSample.type'))->toBe('url')
            ->and($response->json('data.productDownloadableSample.url'))->toBe('https://example.com/sample.pdf')
            ->and($response->json('data.productDownloadableSample.product._id'))->toBe($sample->product_id);
    }

    /**
     * The productDownloadableSample query reports an error for an unknown sample.
     */
    public function test_product_downloadable_sample_query_rejects_an_unknown_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productDownloadableSample(id: "/api/shop/product-downloadable-samples/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productDownloadableSample'))->toBeNull();
    }

    /**
     * Create a downloadable product with one URL sample.
     */
    private function seedDownloadableSample(): ProductDownloadableSample
    {
        $product = $this->createBaseProduct('downloadable', ['sku' => 'GQL-DL-SAMPLE-'.uniqid()]);

        return ProductDownloadableSample::create([
            'url' => 'https://example.com/sample.pdf',
            'type' => 'url',
            'sort_order' => 1,
            'product_id' => $product->id,
        ]);
    }
}
