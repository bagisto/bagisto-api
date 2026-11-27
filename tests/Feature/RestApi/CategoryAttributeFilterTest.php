<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class CategoryAttributeFilterTest extends RestApiTestCase
{
    /**
     * Without a category every filterable attribute is listed with the price range.
     */
    public function test_store_wide_filterable_attributes_are_listed(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/categories/attributes');

        $response->assertOk();

        $filterable = DB::table('attributes')->where('is_filterable', 1)->pluck('code')->all();

        expect(array_column($response->json(), 'code'))->toEqualCanonicalizing($filterable);

        foreach ($response->json() as $attribute) {
            expect($attribute)->toHaveKeys(['minPrice', 'maxPrice', 'options']);
        }
    }

    /**
     * A category lists only its own filterable attributes.
     */
    public function test_a_category_lists_its_own_filterable_attributes(): void
    {
        $this->seedRequiredData();

        $categoryId = (int) DB::table('categories')->value('id');
        $attributeId = (int) DB::table('attributes')->where('code', 'color')->value('id');

        DB::table('category_filterable_attributes')->where('category_id', $categoryId)->delete();
        DB::table('category_filterable_attributes')->insert(['category_id' => $categoryId, 'attribute_id' => $attributeId]);

        $response = $this->publicGet('/api/shop/categories/attributes?category_id='.$categoryId);

        $response->assertOk();

        expect(array_column($response->json(), 'code'))->toBe(['color']);
    }

    /**
     * An unknown category answers 404.
     */
    public function test_an_unknown_category_returns_404(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/categories/attributes?category_id=999999')->assertNotFound();
    }
}
