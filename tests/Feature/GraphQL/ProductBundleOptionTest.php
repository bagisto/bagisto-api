<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\ProductBundleOption;
use Webkul\Product\Models\ProductBundleOptionProduct;

class ProductBundleOptionTest extends GraphQLTestCase
{
    /**
     * The productBundleOptions query lists the bundle options, including a new one.
     */
    public function test_product_bundle_options_query_lists_options(): void
    {
        $option = $this->seedBundleOption();

        $response = $this->graphQL(<<<'GQL'
            query {
              productBundleOptions(first: 100) {
                totalCount
                edges {
                  node {
                    _id
                    type
                    sortOrder
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $node = collect($response->json('data.productBundleOptions.edges'))
            ->pluck('node')
            ->firstWhere('_id', $option->id);

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productBundleOptions.totalCount'))->toBe(ProductBundleOption::count())
            ->and($node['type'])->toBe('select')
            ->and($node['sortOrder'])->toBe(1);
    }

    /**
     * The productBundleOption query returns one option with its parent product and option products.
     */
    public function test_product_bundle_option_query_returns_option(): void
    {
        $option = $this->seedBundleOption();
        $child = $this->createBaseProduct('simple', ['sku' => 'GQL-BUNDLE-CHILD-'.uniqid()]);

        $optionProduct = ProductBundleOptionProduct::create([
            'qty' => 2,
            'is_user_defined' => 1,
            'is_default' => 1,
            'sort_order' => 1,
            'product_bundle_option_id' => $option->id,
            'product_id' => $child->id,
        ]);

        $response = $this->graphQL(<<<GQL
            query {
              productBundleOption(id: "/api/shop/product_bundle_options/{$option->id}") {
                _id
                type
                isRequired
                product {
                  _id
                }
                bundleOptionProducts {
                  edges {
                    node {
                      _id
                      qty
                    }
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productBundleOption._id'))->toBe($option->id)
            ->and($response->json('data.productBundleOption.type'))->toBe('select')
            ->and($response->json('data.productBundleOption.product._id'))->toBe($option->product_id)
            ->and($response->json('data.productBundleOption.bundleOptionProducts.edges.0.node._id'))->toBe($optionProduct->id)
            ->and($response->json('data.productBundleOption.bundleOptionProducts.edges.0.node.qty'))->toBe(2);
    }

    /**
     * The productBundleOption query reports an error for an unknown option.
     */
    public function test_product_bundle_option_query_rejects_an_unknown_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productBundleOption(id: "/api/shop/product_bundle_options/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productBundleOption'))->toBeNull();
    }

    /**
     * Create a bundle product with one required select option.
     */
    private function seedBundleOption(): ProductBundleOption
    {
        $product = $this->createBaseProduct('bundle', ['sku' => 'GQL-BUNDLE-'.uniqid()]);

        return ProductBundleOption::create([
            'type' => 'select',
            'is_required' => 1,
            'sort_order' => 1,
            'product_id' => $product->id,
        ]);
    }
}
