<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductImage;

class ProductImageTest extends GraphQLTestCase
{
    /**
     * The productImages query filtered by product returns only that product's images with their public path.
     */
    public function test_product_images_query_filters_by_product(): void
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'GQL-Image-'.uniqid()]);
        $other = $this->createBaseProduct('simple', ['sku' => 'GQL-Image-OTHER-'.uniqid()]);

        $image = $this->seedImage($product);
        $this->seedImage($other);

        $response = $this->graphQL(<<<'GQL'
            query images($productId: Int) {
              productImages(product_id: $productId) {
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

        $node = $response->json('data.productImages.edges.0.node');

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productImages.totalCount'))->toBe(1)
            ->and($node['_id'])->toBe($image->id)
            ->and($node['path'])->toBe($image->path)
            ->and((int) $node['productId'])->toBe($product->id)
            ->and($node['publicPath'])->toEndWith($image->path);
    }

    /**
     * The productImages query rejects a product id that is not an integer.
     */
    public function test_product_images_query_rejects_a_non_integer_product_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productImages(product_id: "abc") {
                totalCount
              }
            }
        GQL);

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productImages'))->toBeNull();
    }

    /**
     * Attach one image to the product.
     */
    private function seedImage(Product $product): ProductImage
    {
        return ProductImage::create([
            'type' => 'images',
            'path' => 'product/'.$product->id.'/graphql-image-'.uniqid().'.jpg',
            'product_id' => $product->id,
            'position' => 1,
        ]);
    }
}
