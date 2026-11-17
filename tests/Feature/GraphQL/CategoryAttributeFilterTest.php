<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CategoryAttributeFilterTest extends GraphQLTestCase
{
    /**
     * The query every test sends.
     */
    private const QUERY = 'query F($categoryId: Int) { categoryAttributeFilters(categoryId: $categoryId) { edges { node { code minPrice maxPrice } } } }';

    /**
     * A category id lists only that category's filterable attributes.
     */
    public function test_category_id_lists_its_own_filterable_attributes(): void
    {
        $this->seedRequiredData();

        $categoryId = (int) DB::table('categories')->value('id');
        $attributeId = (int) DB::table('attributes')->where('code', 'color')->value('id');

        DB::table('category_filterable_attributes')->where('category_id', $categoryId)->delete();
        DB::table('category_filterable_attributes')->insert(['category_id' => $categoryId, 'attribute_id' => $attributeId]);

        $response = $this->graphQL(self::QUERY, ['categoryId' => $categoryId]);

        expect($response->json('errors'))->toBeNull()
            ->and(collect($response->json('data.categoryAttributeFilters.edges'))->pluck('node.code')->all())->toBe(['color']);
    }

    /**
     * An unknown category id is reported in errors.
     */
    public function test_an_unknown_category_id_is_an_error(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(self::QUERY, ['categoryId' => 999999]);

        expect($response->json('errors'))->not->toBeNull();
    }
}
