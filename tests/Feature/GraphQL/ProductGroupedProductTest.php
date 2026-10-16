<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ProductGroupedProductTest extends GraphQLTestCase
{
    /**
     * The productGroupedProducts query lists the grouped product rows, including a new one.
     */
    public function test_product_grouped_products_query_lists_rows(): void
    {
        ['id' => $id] = $this->seedGroupedRow();

        $response = $this->graphQL(<<<'GQL'
            query {
              productGroupedProducts(first: 100) {
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

        $ids = collect($response->json('data.productGroupedProducts.edges'))->pluck('node._id')->all();

        expect($response->json('errors'))->toBeNull()
            ->and($ids)->toContain($id);
    }

    /**
     * The productGroupedProduct query returns one row with its parent and associated products.
     */
    public function test_product_grouped_product_query_returns_row(): void
    {
        $row = $this->seedGroupedRow();

        $response = $this->graphQL(<<<GQL
            query {
              productGroupedProduct(id: "/api/shop/product_grouped_products/{$row['id']}") {
                _id
                qty
                sortOrder
                product {
                  _id
                }
                associatedProduct {
                  _id
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productGroupedProduct._id'))->toBe($row['id'])
            ->and($response->json('data.productGroupedProduct.qty'))->toBe(4)
            ->and($response->json('data.productGroupedProduct.sortOrder'))->toBe(1)
            ->and($response->json('data.productGroupedProduct.product._id'))->toBe($row['parent_id'])
            ->and($response->json('data.productGroupedProduct.associatedProduct._id'))->toBe($row['associated_id']);
    }

    /**
     * The productGroupedProduct query reports an error for an unknown row.
     */
    public function test_product_grouped_product_query_rejects_an_unknown_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productGroupedProduct(id: "/api/shop/product_grouped_products/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productGroupedProduct'))->toBeNull();
    }

    /**
     * Create a grouped product holding one simple product and return the row and product ids.
     */
    private function seedGroupedRow(): array
    {
        $parent = $this->createBaseProduct('grouped', ['sku' => 'GQL-GROUP-PARENT-'.uniqid()]);
        $associated = $this->createBaseProduct('simple', ['sku' => 'GQL-GROUP-ASSOC-'.uniqid()]);

        $id = (int) DB::table('product_grouped_products')->insertGetId([
            'product_id' => $parent->id,
            'associated_product_id' => $associated->id,
            'qty' => 4,
            'sort_order' => 1,
        ]);

        return [
            'id' => $id,
            'parent_id' => $parent->id,
            'associated_id' => $associated->id,
        ];
    }
}
