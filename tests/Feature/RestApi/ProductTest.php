<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Core;
use Webkul\Product\Models\Product;

/**
 * REST tests for the Product collection endpoint.
 *
 * Mirrors the GraphQL contract (see GraphQL/ProductSearchFilterTest.php):
 *  - Query-param → args translation (sort, pagination, filter)
 *  - Search (SKU + name)
 *  - Filter by type, category, price range, attributes
 *  - Sort (name/created_at/price, asc+desc)
 *  - Pagination via ?page & ?per_page
 *  - new/featured flags
 *
 * The REST provider is a thin adapter over ProductGraphQLProvider, so these
 * tests focus on shape + translation, not the underlying filter logic.
 */
class ProductTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/products';

    private function seedSaleableProduct(string $type = 'simple', ?string $sku = null): Product
    {
        return $this->createBaseProduct($type, [
            'sku' => $sku ?? ('TEST-REST-'.uniqid()),
        ]);
    }

    private function seedGuestPriceIndex(Product $product, float $minPrice): void
    {
        DB::table('product_price_indices')->updateOrInsert(
            [
                'product_id' => $product->id,
                'customer_group_id' => core()->getGuestCustomerGroup()->id,
                'channel_id' => core()->getCurrentChannel()->id,
            ],
            [
                'min_price' => $minPrice,
                'regular_min_price' => $minPrice,
                'max_price' => $minPrice,
                'regular_max_price' => $minPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedTestCurrency(float $rate): string
    {
        $currencyId = DB::table('currencies')->insertGetId([
            'code' => 'TST',
            'name' => 'Test Currency',
            'symbol' => 'T$',
            'decimal' => 2,
            'group_separator' => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
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

        app()->forgetInstance(Core::class);
        Facade::clearResolvedInstance(Core::class);

        return 'TST';
    }

    private function productIds($response): array
    {
        return collect($response->json())->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    // ── Basic collection ─────────────────────────────────────────────

    public function test_get_products_returns_ok(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
    }

    public function test_get_products_returns_array(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl);

        expect($response->json())->toBeArray();
    }

    // ── Search ──────────────────────────────────────────────────────

    public function test_search_by_sku(): void
    {
        $product = $this->seedSaleableProduct('simple', 'REST-SEARCH-'.uniqid());

        $response = $this->publicGet($this->collectionUrl.'?query='.$product->sku);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_search_with_no_match_returns_empty(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?query=XYZZY_NO_MATCH_99999');

        $response->assertOk();
        expect($response->json())->toBeArray()->toBeEmpty();
    }

    // ── Filter by type ──────────────────────────────────────────────

    public function test_filter_by_type(): void
    {
        $this->seedSaleableProduct('simple');

        $response = $this->publicGet($this->collectionUrl.'?type=simple');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Filter by category ──────────────────────────────────────────

    public function test_filter_by_category_id(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?category_id=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_filter_by_nonexistent_category_returns_empty(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?category_id=9999999');

        $response->assertOk();
        expect($response->json())->toBeArray()->toBeEmpty();
    }

    // ── Filter by attribute(s) ───────────────────────────────────────

    public function test_filter_by_single_attribute(): void
    {
        $attribute = Attribute::where('code', 'color')->first();
        if (! $attribute || ! ($option = $attribute->options()->first())) {
            $this->markTestSkipped('Color attribute/options not seeded.');
        }

        $product = $this->seedSaleableProduct('simple', 'REST-COLOR-'.uniqid());
        $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, 'default');

        $response = $this->publicGet($this->collectionUrl.'?color='.$option->id);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_filter_by_multiple_attributes(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?color=1&size=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Filter by price range ───────────────────────────────────────

    public function test_filter_by_price_range_compound(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?price=1,1000');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_filter_by_price_range_separate(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?price_from=1&price_to=1000');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Sort ────────────────────────────────────────────────────────

    public function test_sort_name_asc(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=name-asc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_sort_name_asc_actually_orders_by_name(): void
    {
        $tag = 'srtasc'.uniqid();
        $a = $this->seedSaleableProduct('simple', 'REST-SORT-A-'.$tag);
        $b = $this->seedSaleableProduct('simple', 'REST-SORT-B-'.$tag);
        $c = $this->seedSaleableProduct('simple', 'REST-SORT-C-'.$tag);
        $this->upsertProductAttributeValue($a->id, 'name', 'ZZZ Last', 'en', 'default');
        $this->upsertProductAttributeValue($b->id, 'name', 'AAA First', 'en', 'default');
        $this->upsertProductAttributeValue($c->id, 'name', 'MMM Middle', 'en', 'default');

        $response = $this->publicGet($this->collectionUrl.'?sort=name-asc&query='.$tag.'&per_page=50');

        $response->assertOk();
        $names = array_column($response->json(), 'name');
        $aPos = array_search('ZZZ Last', $names);
        $bPos = array_search('AAA First', $names);
        $cPos = array_search('MMM Middle', $names);
        expect($bPos)->toBeLessThan($cPos);
        expect($cPos)->toBeLessThan($aPos);
    }

    public function test_sort_name_desc(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=name-desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_sort_newest_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=created_at-desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_sort_oldest_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=created_at-asc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_sort_cheapest_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=price-asc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_sort_most_expensive_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=price-desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_sort_separate_sort_and_order_params(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=name&order=desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Pagination ──────────────────────────────────────────────────

    public function test_pagination_per_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->seedSaleableProduct('simple', 'REST-PAGE-'.$i.'-'.uniqid());
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=2');

        $response->assertOk();
        $body = $response->json();
        expect($body)->toBeArray();
        expect(count($body))->toBeLessThanOrEqual(2);
    }

    /**
     * Regression — Bug 5 (e2e wave 2026-05-25):
     * ProductRestProvider had no max-per-page cap, so callers could request
     * arbitrarily large pages (e.g. ?per_page=100 returned 84 items). The
     * package-wide pagination_maximum_items_per_page is 50; the custom
     * provider must enforce that explicitly.
     */
    public function test_per_page_is_capped_at_50(): void
    {
        // Seed enough rows that the cap is what limits the response (not the
        // available data).
        for ($i = 0; $i < 55; $i++) {
            $this->seedSaleableProduct('simple', 'REST-CAP-'.$i.'-'.uniqid());
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=100');

        $response->assertOk();
        expect(count($response->json()))->toBeLessThanOrEqual(50);
    }

    public function test_pagination_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->seedSaleableProduct('simple', 'REST-P2-'.$i.'-'.uniqid());
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=1&page=2');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── new / featured flags ─────────────────────────────────────────

    public function test_filter_new_products(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?new=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_filter_featured_products(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?featured=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_filter_new_and_featured_together(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?new=1&featured=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Brand ───────────────────────────────────────────────────────

    public function test_filter_by_brand(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?brand=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Matches the storefront shop-page URL shape:
     * https://commerce.bagisto.com/api/products?category_id=2&brand=38
     * Even if no product is linked to that brand option, the endpoint must
     * return 200 with an (empty) array — not 404 or 500.
     */
    public function test_filter_by_category_and_brand_storefront_url_shape(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?category_id=2&brand=38');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── deepObject filter[key]=value shape (Swagger UI) ─────────────

    public function test_filter_deep_object_shape(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet(
            $this->collectionUrl.'?filter%5Bbrand%5D=38&filter%5Bcolor%5D=3'
        );

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── JSON filter param (GraphQL parity) ──────────────────────────

    public function test_json_filter_param(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet(
            $this->collectionUrl.'?filter='.urlencode('{"type":"simple"}')
        );

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Combined filter + sort ──────────────────────────────────────

    public function test_combined_search_filter_and_sort(): void
    {
        $product = $this->seedSaleableProduct('simple', 'REST-COMBO-'.uniqid());

        $response = $this->publicGet(
            $this->collectionUrl.'?query='.$product->sku.'&type=simple&sort=name-asc&per_page=5'
        );

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── List response is slim (Groups filter) ───────────────────────

    public function test_list_response_excludes_heavy_relations(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?per_page=1');

        $response->assertOk();
        $body = $response->json();
        expect($body)->toBeArray()->not()->toBeEmpty();
        $product = $body[0];
        // Heavy relation IRI lists must NOT appear in the list endpoint
        foreach ([
            'attributeValues', 'superAttributes', 'variants', 'categories',
            'images', 'videos', 'channels', 'attributeFamily',
            'bookingProducts', 'bundleOptions', 'groupedProducts',
            'downloadableLinks', 'downloadableSamples', 'customizableOptions',
            'approvedReviews', 'reviews', 'relatedProducts', 'upSells', 'crossSells',
            'description', 'descriptionHtml', 'metaTitle', 'metaKeywords', 'metaDescription',
            'cost', 'taxCategoryId', 'productNumber',
        ] as $heavyField) {
            expect($product)->not()->toHaveKey($heavyField);
        }
        // Light fields MUST appear
        foreach ([
            'id', 'sku', 'type', 'name', 'urlKey', 'status',
            'price', 'formattedPrice', 'minimumPrice', 'maximumPrice',
            'new', 'featured', 'baseImageUrl',
        ] as $lightField) {
            expect($product)->toHaveKey($lightField);
        }
    }

    // ── Sub-resources (under "Product" tag in Swagger) ──────────────

    public function test_subresource_images(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/images');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_attribute_values(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/attribute-values');

        $response->assertOk();
        expect($response->json())->toBeArray()->not()->toBeEmpty();
    }

    public function test_subresource_videos(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/videos');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_variants(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/variants');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_reviews(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/reviews');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_customizable_options(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/customizable-options');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Type-specific sub-resources (Product Types tag) ─────────────

    public function test_subresource_bundle_options(): void
    {
        $product = $this->seedSaleableProduct('bundle');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/bundle-options');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_downloadable_links(): void
    {
        $product = $this->seedSaleableProduct('downloadable');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/downloadable-links');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_downloadable_samples(): void
    {
        $product = $this->seedSaleableProduct('downloadable');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/downloadable-samples');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_grouped_products(): void
    {
        $product = $this->seedSaleableProduct('grouped');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/grouped-products');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_subresource_booking_products(): void
    {
        $product = $this->seedSaleableProduct('booking');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/booking-products');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ── Single product GET ──────────────────────────────────────────

    public function test_get_single_product(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id);

        $response->assertOk();
        expect($response->json('id'))->toBe($product->id);
    }

    public function test_unknown_query_params_do_not_filter_the_list(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?utm_source=newsletter&fbclid=abc&per_page=50&sort=id-desc');

        $response->assertOk();
        expect($this->productIds($response))->toContain($product->id);
    }

    public function test_price_filter_reads_the_price_index_not_the_price_attribute(): void
    {
        $product = $this->seedSaleableProduct();
        $this->seedGuestPriceIndex($product, 500.0);

        $inRange = $this->publicGet($this->collectionUrl.'?price=400,600&per_page=50');
        $inRange->assertOk();
        expect($this->productIds($inRange))->toContain($product->id);

        $attributeRange = $this->publicGet($this->collectionUrl.'?price_from=5&price_to=15&per_page=50');
        $attributeRange->assertOk();
        expect($this->productIds($attributeRange))->not->toContain($product->id);
    }

    public function test_price_filter_matches_configurable_products_through_the_price_index(): void
    {
        $product = $this->seedSaleableProduct('configurable');

        DB::table('product_attribute_values')
            ->where('product_id', $product->id)
            ->where('attribute_id', Attribute::query()->where('code', 'price')->value('id'))
            ->delete();

        $this->seedGuestPriceIndex($product, 75.0);

        $response = $this->publicGet($this->collectionUrl.'?price=70,80&per_page=50');

        $response->assertOk();
        expect($this->productIds($response))->toContain($product->id);
    }

    public function test_price_filter_converts_the_requested_range_to_base_currency(): void
    {
        $product = $this->seedSaleableProduct();
        $this->seedGuestPriceIndex($product, 10.0);
        $currency = $this->seedTestCurrency(25.0);

        $converted = $this->getJson(
            $this->collectionUrl.'?price=200,300&per_page=50',
            array_merge($this->storefrontHeaders(), ['X-Currency' => $currency])
        );
        $converted->assertOk();
        expect($this->productIds($converted))->toContain($product->id);

        $unconverted = $this->getJson(
            $this->collectionUrl.'?price=5,15&per_page=50',
            array_merge($this->storefrontHeaders(), ['X-Currency' => $currency])
        );
        $unconverted->assertOk();
        expect($this->productIds($unconverted))->not->toContain($product->id);
    }

    public function test_downloadable_link_price_is_converted_once(): void
    {
        $product = $this->seedSaleableProduct('downloadable');

        $linkId = DB::table('product_downloadable_links')->insertGetId([
            'product_id' => $product->id,
            'type' => 'url',
            'url' => 'https://example.com/file.zip',
            'price' => 4,
            'downloads' => 0,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('product_downloadable_link_translations')->insert([
            'product_downloadable_link_id' => $linkId,
            'locale' => 'en',
            'title' => 'Link',
        ]);

        $currency = $this->seedTestCurrency(25.0);

        $response = $this->getJson(
            $this->collectionUrl.'/'.$product->id,
            array_merge($this->storefrontHeaders(), ['X-Currency' => $currency])
        );

        $response->assertOk();

        $link = collect($response->json('downloadableLinks'))->firstWhere('id', $linkId);

        expect($link)->not->toBeNull()
            ->and((float) $link['price'])->toBe(100.0);
    }
}
