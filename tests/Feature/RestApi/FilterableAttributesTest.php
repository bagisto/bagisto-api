<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;

class FilterableAttributesTest extends RestApiTestCase
{
    private string $filtersUrl = '/api/shop/categories/attributes';

    private string $optionsUrl = '/api/shop/attribute-options';

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

    private function globalFilterableIds(): array
    {
        return Attribute::query()->where('is_filterable', 1)->orderBy('id')->pluck('id')->all();
    }

    private function responseIds($response): array
    {
        return collect($response->json())->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    private function seedTestCurrency(float $rate): string
    {
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
            'rate' => $rate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('channel_currencies')->insert([
            'channel_id' => core()->getCurrentChannel()->id,
            'currency_id' => $currencyId,
        ]);

        return 'TST';
    }

    public function test_without_category_returns_global_filterable_attributes(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->filtersUrl);

        $response->assertOk();
        expect($this->responseIds($response))->toBe($this->globalFilterableIds());
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

        $response = $this->publicGet($this->filtersUrl.'?category_id='.$category->id);

        $response->assertOk();
        expect($this->responseIds($response))->toBe([$colorId]);
    }

    public function test_category_without_own_attributes_returns_none(): void
    {
        $this->seedRequiredData();

        $category = $this->createCategory();

        $response = $this->publicGet($this->filtersUrl.'?category_id='.$category->id);

        $response->assertOk();
        expect($this->responseIds($response))->toBe([]);
    }

    public function test_unknown_category_id_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->filtersUrl.'?category_id=999999');

        expect($response->getStatusCode())->toBe(404);
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

        $base = $this->publicGet($this->filtersUrl);
        $base->assertOk();

        $currency = $this->seedTestCurrency(25.0);

        $converted = $this->getJson($this->filtersUrl, array_merge($this->storefrontHeaders(), ['X-Currency' => $currency]));
        $converted->assertOk();

        expect((float) $base->json('0.maxPrice'))->toBe(999999.0)
            ->and((float) $converted->json('0.maxPrice'))->toBe(999999.0 * 25);
    }

    public function test_attribute_options_search_matches_label(): void
    {
        $this->seedRequiredData();

        $colorId = (int) Attribute::query()->where('code', 'color')->value('id');

        $response = $this->publicGet($this->optionsUrl.'?attribute_id='.$colorId.'&search=red');

        $response->assertOk();

        $names = collect($response->json())->pluck('adminName')->all();

        expect($names)->not->toBeEmpty();

        foreach ($names as $name) {
            expect(strtolower($name))->toContain('red');
        }
    }

    public function test_attribute_options_scoped_to_attribute(): void
    {
        $this->seedRequiredData();

        $colorId = (int) Attribute::query()->where('code', 'color')->value('id');

        $response = $this->publicGet($this->optionsUrl.'?attribute_id='.$colorId.'&per_page=50');

        $response->assertOk();

        $expected = DB::table('attribute_options')->where('attribute_id', $colorId)->orderBy('id')->pluck('id')->all();

        expect($this->responseIds($response))->toBe($expected);
    }

    public function test_boolean_attribute_returns_yes_and_no_options(): void
    {
        $this->seedRequiredData();

        $booleanId = Attribute::query()->where('type', 'boolean')->value('id');

        if (! $booleanId) {
            $this->markTestSkipped('No boolean attribute seeded.');
        }

        $response = $this->publicGet($this->optionsUrl.'?attribute_id='.$booleanId);

        $response->assertOk();

        $options = collect($response->json())->map(fn ($o) => [(int) $o['id'], $o['adminName']])->all();

        expect($options)->toBe([
            [0, trans('attribute::app.boolean.options.no')],
            [1, trans('attribute::app.boolean.options.yes')],
        ]);
    }

    public function test_attribute_options_unknown_attribute_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->optionsUrl.'?attribute_id=999999');

        expect($response->getStatusCode())->toBe(404);
    }
}
