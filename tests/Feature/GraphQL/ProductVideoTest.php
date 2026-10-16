<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductVideo;

class ProductVideoTest extends GraphQLTestCase
{
    /**
     * The productVideos query filtered by product returns only that product's videos with their public path.
     */
    public function test_product_videos_query_filters_by_product(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'GQL-Video-'.uniqid()]);
        $other = $this->createBaseProduct('simple', ['sku' => 'GQL-Video-OTHER-'.uniqid()]);

        $video = $this->seedVideo($product);
        $this->seedVideo($other);

        $response = $this->graphQL(<<<'GQL'
            query videos($productId: Int) {
              productVideos(product_id: $productId) {
                totalCount
                edges {
                  node {
                    _id
                    path
                    productId
                    publicPath
                  }
                }
              }
            }
        GQL, ['productId' => $product->id]);

        $response->assertOk();

        $node = $response->json('data.productVideos.edges.0.node');

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productVideos.totalCount'))->toBe(1)
            ->and($node['_id'])->toBe($video->id)
            ->and($node['path'])->toBe($video->path)
            ->and((int) $node['productId'])->toBe($product->id)
            ->and($node['publicPath'])->toEndWith($video->path);
    }

    /**
     * The productVideos query rejects a product id that is not an integer.
     */
    public function test_product_videos_query_rejects_a_non_integer_product_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productVideos(product_id: "abc") {
                totalCount
              }
            }
        GQL);

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productVideos'))->toBeNull();
    }

    /**
     * Attach one video to the product.
     */
    private function seedVideo(Product $product): ProductVideo
    {
        return ProductVideo::create([
            'type' => 'videos',
            'path' => 'product/'.$product->id.'/graphql-video-'.uniqid().'.mp4',
            'product_id' => $product->id,
            'position' => 1,
        ]);
    }
}
