<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\ProductBundleOption;
use Webkul\Product\Models\ProductBundleOptionProduct;

class ProductBundleOptionProductTest extends GraphQLTestCase
{
    /**
     * The productBundleOptionProducts query lists the bundle option products, including a new one.
     */
    public function test_product_bundle_option_products_query_lists_option_products(): void
    {
        $optionProduct = $this->seedBundleOptionProduct();

        $response = $this->graphQL(<<<'GQL'
            query {
              productBundleOptionProducts(first: 100) {
                edges {
                  node {
                    _id
                    qty
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $ids = collect($response->json('data.productBundleOptionProducts.edges'))->pluck('node._id')->all();

        expect($response->json('errors'))->toBeNull()
            ->and($ids)->toContain($optionProduct->id);
    }

    /**
     * The productBundleOptionProduct query returns one option product with its product and option.
     */
    public function test_product_bundle_option_product_query_returns_option_product(): void
    {
        $optionProduct = $this->seedBundleOptionProduct();

        $response = $this->graphQL(<<<GQL
            query {
              productBundleOptionProduct(id: "/api/shop/product-bundle-option-products/{$optionProduct->id}") {
                _id
                qty
                isDefault
                product {
                  _id
                }
                bundleOption {
                  _id
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productBundleOptionProduct._id'))->toBe($optionProduct->id)
            ->and($response->json('data.productBundleOptionProduct.qty'))->toBe(3)
            ->and($response->json('data.productBundleOptionProduct.isDefault'))->toBeTrue()
            ->and($response->json('data.productBundleOptionProduct.product._id'))->toBe($optionProduct->product_id)
            ->and($response->json('data.productBundleOptionProduct.bundleOption._id'))->toBe($optionProduct->product_bundle_option_id);
    }

    /**
     * The productBundleOptionProduct query reports an error for an unknown option product.
     */
    public function test_product_bundle_option_product_query_rejects_an_unknown_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productBundleOptionProduct(id: "/api/shop/product-bundle-option-products/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productBundleOptionProduct'))->toBeNull();
    }

    /**
     * Create a bundle option holding one simple product.
     */
    private function seedBundleOptionProduct(): ProductBundleOptionProduct
    {
        $bundle = $this->createBaseProduct('bundle', ['sku' => 'GQL-BOP-PARENT-'.uniqid()]);
        $child = $this->createBaseProduct('simple', ['sku' => 'GQL-BOP-CHILD-'.uniqid()]);

        $option = ProductBundleOption::create([
            'type' => 'select',
            'is_required' => 1,
            'sort_order' => 1,
            'product_id' => $bundle->id,
        ]);

        return ProductBundleOptionProduct::create([
            'qty' => 3,
            'is_user_defined' => 1,
            'is_default' => 1,
            'sort_order' => 1,
            'product_bundle_option_id' => $option->id,
            'product_id' => $child->id,
        ]);
    }
}
