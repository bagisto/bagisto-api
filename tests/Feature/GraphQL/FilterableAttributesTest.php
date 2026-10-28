<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;

class FilterableAttributesTest extends GraphQLTestCase
{
    private string $filtersQuery = <<<'GQL'
        query filters($categoryId: Int, $categorySlug: String) {
          categoryAttributeFilters(categoryId: $categoryId, categorySlug: $categorySlug, first: 50) {
            edges {
              node {
                _id
                code
                maxPrice
                minPrice
              }
            }
            totalCount
          }
        }
    GQL;

    private string $optionsQuery = <<<'GQL'
        query options($attributeId: Int, $search: String) {
          attributeOptions(attributeId: $attributeId, search: $search, first: 50) {
            edges {
              node {
                _id
                adminName
                translation {
                  label
                }
              }
            }
            totalCount
          }
        }
    GQL;

    private function createCategory(): Category
    {
        $category = Category::factory()->create(['status' => 1, 'position' => 1]);

        CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Filter Category '.$category->id,
            'slug' => 'filter-category-'.$category->id,
        ]);

        return $category->fresh();
    }

    private function nodeIds($response, string $path): array
    {
        return collect($response->json($path.'.edges'))->map(fn ($e) => (int) $e['node']['_id'])->sort()->values()->all();
    }

    public function test_category_id_returns_the_category_filterable_attributes(): void
    {
        $this->seedRequiredData();

        $category = $this->createCategory();
        $colorId = (int) Attribute::query()->where('code', 'color')->value('id');

        DB::table('category_filterable_attributes')->insert([
            'category_id' => $category->id,
            'attribute_id' => $colorId,
        ]);

        $response = $this->graphQL($this->filtersQuery, ['categoryId' => $category->id]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull()
            ->and($this->nodeIds($response, 'data.categoryAttributeFilters'))->toBe([$colorId]);
    }

    public function test_each_filter_lists_only_its_own_options(): void
    {
        $this->seedRequiredData();

        $category = $this->createCategory();

        $attributeIds = Attribute::query()->whereIn('code', ['color', 'size'])->pluck('id', 'code');

        foreach ($attributeIds as $code => $attributeId) {
            $this->createAttributeOption($attributeId, 'Only '.$code);

            DB::table('category_filterable_attributes')->insert([
                'category_id' => $category->id,
                'attribute_id' => $attributeId,
            ]);
        }

        $response = $this->graphQL(<<<'GQL'
            query filters($categoryId: Int) {
              categoryAttributeFilters(categoryId: $categoryId, first: 50) {
                edges { node { _id code options(first: 50) { edges { node { _id adminName } } } } }
              }
            }
        GQL, ['categoryId' => $category->id]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $filters = collect($response->json('data.categoryAttributeFilters.edges'))->keyBy('node.code');

        foreach ($attributeIds as $code => $attributeId) {
            $optionIds = collect($filters[$code]['node']['options']['edges'])->map(fn ($edge) => (int) $edge['node']['_id'])->sort()->values()->all();

            $expected = DB::table('attribute_options')->where('attribute_id', $attributeId)->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();

            expect($optionIds)->toBe($expected)
                ->and(collect($filters[$code]['node']['options']['edges'])->pluck('node.adminName'))->toContain('Only '.$code);
        }
    }

    public function test_attribute_options_with_first_zero_return_an_empty_page(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL('{ attributeOptions(first: 0) { edges { node { _id } } totalCount } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.attributeOptions.edges'))->toBe([])
            ->and($response->json('data.attributeOptions.totalCount'))->toBe(DB::table('attribute_options')->count());
    }

    public function test_category_without_own_attributes_returns_none(): void
    {
        $this->seedRequiredData();

        $category = $this->createCategory();

        $byId = $this->graphQL($this->filtersQuery, ['categoryId' => $category->id]);
        $bySlug = $this->graphQL($this->filtersQuery, ['categorySlug' => 'filter-category-'.$category->id]);

        expect($byId->json('errors'))->toBeNull()
            ->and($this->nodeIds($byId, 'data.categoryAttributeFilters'))->toBe([])
            ->and($this->nodeIds($bySlug, 'data.categoryAttributeFilters'))->toBe([]);
    }

    public function test_unknown_category_id_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->filtersQuery, ['categoryId' => 999999]);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_max_price_is_converted_to_the_requested_currency(): void
    {
        $this->seedRequiredData();

        $product = $this->createBaseProduct('simple', ['sku' => 'FILTER-PRICE-'.uniqid()]);

        DB::table('product_price_indices')->updateOrInsert(
            [
                'product_id' => $product->id,
                'customer_group_id' => core()->getGuestCustomerGroup()->id,
                'channel_id' => core()->getCurrentChannel()->id,
            ],
            [
                'min_price' => 999999,
                'regular_min_price' => 999999,
                'max_price' => 999999,
                'regular_max_price' => 999999,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $currencyId = DB::table('currencies')->insertGetId([
            'code' => 'TST',
            'name' => 'Test Currency',
            'symbol' => 'T$',
            'decimal' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('currency_exchange_rates')->insert([
            'target_currency' => $currencyId,
            'rate' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('channel_currencies')->insert([
            'channel_id' => core()->getCurrentChannel()->id,
            'currency_id' => $currencyId,
        ]);

        $response = $this->graphQL($this->filtersQuery, [], ['X-Currency' => 'TST']);

        expect($response->json('errors'))->toBeNull()
            ->and((float) $response->json('data.categoryAttributeFilters.edges.0.node.maxPrice'))->toBe(999999.0 * 25);
    }

    public function test_attribute_options_search_matches_label(): void
    {
        $this->seedRequiredData();

        $colorId = (int) Attribute::query()->where('code', 'color')->value('id');

        $response = $this->graphQL($this->optionsQuery, ['attributeId' => $colorId, 'search' => 'red']);

        expect($response->json('errors'))->toBeNull();

        $names = collect($response->json('data.attributeOptions.edges'))->pluck('node.adminName')->all();

        expect($names)->not->toBeEmpty();

        foreach ($names as $name) {
            expect(strtolower($name))->toContain('red');
        }
    }

    public function test_boolean_attribute_returns_yes_and_no_options(): void
    {
        $this->seedRequiredData();

        $booleanId = Attribute::query()->where('type', 'boolean')->value('id');

        if (! $booleanId) {
            $this->markTestSkipped('No boolean attribute seeded.');
        }

        $response = $this->graphQL($this->optionsQuery, ['attributeId' => (int) $booleanId]);

        expect($response->json('errors'))->toBeNull();

        $options = collect($response->json('data.attributeOptions.edges'))
            ->map(fn ($e) => [(int) $e['node']['_id'], $e['node']['translation']['label']])
            ->all();

        expect($options)->toBe([
            [0, trans('attribute::app.boolean.options.no')],
            [1, trans('attribute::app.boolean.options.yes')],
        ]);
    }
}
