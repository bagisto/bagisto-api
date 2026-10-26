<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Core;
use Webkul\Product\Models\Product;

class ProductTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/products';

    // ============================================================================
    // Basic Collection
    // ============================================================================

    /**
     * Test get products returns ok.
     */
    public function test_get_products_returns_ok(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
    }

    /**
     * Test get products returns array.
     */
    public function test_get_products_returns_array(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl);

        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Search
    // ============================================================================

    /**
     * Test search by sku.
     */
    public function test_search_by_sku(): void
    {
        $product = $this->seedSaleableProduct('simple', 'REST-SEARCH-'.uniqid());

        $response = $this->publicGet($this->collectionUrl.'?query='.$product->sku);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test search with no match returns empty.
     */
    public function test_search_with_no_match_returns_empty(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?query=XYZZY_NO_MATCH_99999');

        $response->assertOk();
        expect($response->json())->toBeArray()->toBeEmpty();
    }

    // ============================================================================
    // Filter By Type
    // ============================================================================

    /**
     * Test filter by type.
     */
    public function test_filter_by_type(): void
    {
        $this->seedSaleableProduct('simple');

        $response = $this->publicGet($this->collectionUrl.'?type=simple');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Filter By Category
    // ============================================================================

    /**
     * Test filter by category id.
     */
    public function test_filter_by_category_id(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?category_id=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test filter by nonexistent category returns empty.
     */
    public function test_filter_by_nonexistent_category_returns_empty(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?category_id=9999999');

        $response->assertOk();
        expect($response->json())->toBeArray()->toBeEmpty();
    }

    // ============================================================================
    // Filter By Attribute(s)
    // ============================================================================

    /**
     * Test filter by single attribute.
     */
    public function test_filter_by_single_attribute(): void
    {
        $attribute = Attribute::where('code', 'color')->first();
        if (
            ! $attribute
            || ! ($option = $attribute->options()->first())
        ) {
            $this->markTestSkipped('Color attribute/options not seeded.');
        }

        $product = $this->seedSaleableProduct('simple', 'REST-COLOR-'.uniqid());
        $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, 'default');

        $response = $this->publicGet($this->collectionUrl.'?color='.$option->id);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test filter by multiple attributes.
     */
    public function test_filter_by_multiple_attributes(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?color=1&size=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Filter By Price Range
    // ============================================================================

    /**
     * Test filter by price range compound.
     */
    public function test_filter_by_price_range_compound(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?price=1,1000');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test filter by price range separate.
     */
    public function test_filter_by_price_range_separate(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?price_from=1&price_to=1000');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Sort
    // ============================================================================

    /**
     * Test sort name asc.
     */
    public function test_sort_name_asc(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=name-asc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test sort name asc actually orders by name.
     */
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

    /**
     * Test sort name desc.
     */
    public function test_sort_name_desc(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=name-desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test sort newest first.
     */
    public function test_sort_newest_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=created_at-desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test sort oldest first.
     */
    public function test_sort_oldest_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=created_at-asc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test sort cheapest first.
     */
    public function test_sort_cheapest_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=price-asc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test sorting by price lists a product once when it has a price index row on another channel.
     */
    public function test_price_sort_lists_a_product_once_across_channels(): void
    {
        $product = $this->seedSaleableProduct();
        $this->seedGuestPriceIndex($product, 50.0);

        DB::table('product_price_indices')->insert([
            'product_id' => $product->id,
            'customer_group_id' => core()->getGuestCustomerGroup()->id,
            'channel_id' => 999,
            'min_price' => 10.0,
            'regular_min_price' => 10.0,
            'max_price' => 10.0,
            'regular_max_price' => 10.0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['price-asc', 'price-desc'] as $sort) {
            $ids = $this->productIds($this->publicGet($this->collectionUrl.'?sort='.$sort.'&per_page=50')->assertOk());

            expect(count(array_keys($ids, $product->id)))->toBeLessThanOrEqual(1);
            expect(count($ids))->toBe(count(array_unique($ids)));
        }
    }

    /**
     * Test sort most expensive first.
     */
    public function test_sort_most_expensive_first(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=price-desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test sort separate sort and order params.
     */
    public function test_sort_separate_sort_and_order_params(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?sort=name&order=desc');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test the listing without a sort follows the storefront default sort configuration.
     */
    public function test_default_sort_follows_storefront_configuration(): void
    {
        $tag = 'srtdef'.uniqid();
        $a = $this->seedSaleableProduct('simple', 'REST-DEF-A-'.$tag);
        $b = $this->seedSaleableProduct('simple', 'REST-DEF-B-'.$tag);
        $c = $this->seedSaleableProduct('simple', 'REST-DEF-C-'.$tag);
        $this->upsertProductAttributeValue($a->id, 'name', 'ZZZ Last', 'en', 'default');
        $this->upsertProductAttributeValue($b->id, 'name', 'AAA First', 'en', 'default');
        $this->upsertProductAttributeValue($c->id, 'name', 'MMM Middle', 'en', 'default');

        $this->storeDefaultSort('name-asc');

        $names = array_column($this->publicGet($this->collectionUrl.'?query='.$tag.'&per_page=50')->assertOk()->json(), 'name');

        expect(array_values(array_intersect($names, ['AAA First', 'MMM Middle', 'ZZZ Last'])))
            ->toBe(['AAA First', 'MMM Middle', 'ZZZ Last']);

        $this->storeDefaultSort('name-desc');

        $names = array_column($this->publicGet($this->collectionUrl.'?query='.$tag.'&per_page=50')->assertOk()->json(), 'name');

        expect(array_values(array_intersect($names, ['AAA First', 'MMM Middle', 'ZZZ Last'])))
            ->toBe(['ZZZ Last', 'MMM Middle', 'AAA First']);
    }

    // ============================================================================
    // Pagination
    // ============================================================================

    /**
     * Test pagination per page.
     */
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
     * ProductRestProvider had no max-per-page cap, so callers could request.
     */
    public function test_per_page_is_capped_at_50(): void
    {
        for ($i = 0; $i < 55; $i++) {
            $this->seedSaleableProduct('simple', 'REST-CAP-'.$i.'-'.uniqid());
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=100');

        $response->assertOk();
        expect(count($response->json()))->toBeLessThanOrEqual(50);
    }

    /**
     * Test pagination page.
     */
    public function test_pagination_page(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->seedSaleableProduct('simple', 'REST-P2-'.$i.'-'.uniqid());
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=1&page=2');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // New / Featured Flags
    // ============================================================================

    /**
     * Test filter new products.
     */
    public function test_filter_new_products(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?new=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test filter featured products.
     */
    public function test_filter_featured_products(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?featured=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test filter new and featured together.
     */
    public function test_filter_new_and_featured_together(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?new=1&featured=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Brand
    // ============================================================================

    /**
     * Test filter by brand.
     */
    public function test_filter_by_brand(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?brand=1');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Matches the storefront shop-page URL shape:
     * https://commerce.bagisto.com/api/products?category_id=2&brand=38.
     */
    public function test_filter_by_category_and_brand_storefront_url_shape(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?category_id=2&brand=38');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // DeepObject Filter[key]=value Shape (Swagger UI)
    // ============================================================================

    /**
     * Test filter deep object shape.
     */
    public function test_filter_deep_object_shape(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet(
            $this->collectionUrl.'?filter%5Bbrand%5D=38&filter%5Bcolor%5D=3'
        );

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // JSON Filter Param (GraphQL Parity)
    // ============================================================================

    /**
     * Test json filter param.
     */
    public function test_json_filter_param(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet(
            $this->collectionUrl.'?filter='.urlencode('{"type":"simple"}')
        );

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Combined Filter + Sort
    // ============================================================================

    /**
     * Test combined search filter and sort.
     */
    public function test_combined_search_filter_and_sort(): void
    {
        $product = $this->seedSaleableProduct('simple', 'REST-COMBO-'.uniqid());

        $response = $this->publicGet(
            $this->collectionUrl.'?query='.$product->sku.'&type=simple&sort=name-asc&per_page=5'
        );

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // List Response Is Slim (Groups Filter)
    // ============================================================================

    /**
     * Test list response excludes heavy relations.
     */
    public function test_list_response_excludes_heavy_relations(): void
    {
        $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?per_page=1');

        $response->assertOk();
        $body = $response->json();
        expect($body)->toBeArray()->not()->toBeEmpty();
        $product = $body[0];
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
        foreach ([
            'id', 'sku', 'type', 'name', 'urlKey', 'status',
            'price', 'formattedPrice', 'minimumPrice', 'maximumPrice',
            'new', 'featured', 'baseImageUrl',
        ] as $lightField) {
            expect($product)->toHaveKey($lightField);
        }
    }

    // ============================================================================
    // Sub-Resources (Under "Product" Tag In Swagger)
    // ============================================================================

    /**
     * Test subresource images.
     */
    public function test_subresource_images(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/images');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource attribute values.
     */
    public function test_subresource_attribute_values(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/attribute-values');

        $response->assertOk();
        expect($response->json())->toBeArray()->not()->toBeEmpty();
    }

    /**
     * Test subresource videos.
     */
    public function test_subresource_videos(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/videos');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource variants.
     */
    public function test_subresource_variants(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/variants');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource reviews.
     */
    public function test_subresource_reviews(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/reviews');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource customizable options.
     */
    public function test_subresource_customizable_options(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/customizable-options');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * The customizable options of a product carry the label of the current locale.
     */
    public function test_subresource_customizable_options_return_the_translated_label(): void
    {
        $product = $this->seedSaleableProduct();

        $optionId = DB::table('product_customizable_options')->insertGetId([
            'product_id' => $product->id,
            'type' => 'text',
            'is_required' => 0,
            'sort_order' => 1,
        ]);

        DB::table('product_customizable_option_translations')->insert([
            [
                'product_customizable_option_id' => $optionId,
                'locale' => 'fr',
                'label' => 'Gravure',
            ],
            [
                'product_customizable_option_id' => $optionId,
                'locale' => 'en',
                'label' => 'Engraving',
            ],
        ]);

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/customizable-options');

        $response->assertOk();
        expect($response->json('0.id'))->toBe($optionId);
        expect($response->json('0.label'))->toBe('Engraving');
    }

    // ============================================================================
    // Type-Specific Sub-Resources (Product Types Tag)
    // ============================================================================

    /**
     * Test subresource bundle options.
     */
    public function test_subresource_bundle_options(): void
    {
        $product = $this->seedSaleableProduct('bundle');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/bundle-options');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource downloadable links.
     */
    public function test_subresource_downloadable_links(): void
    {
        $product = $this->seedSaleableProduct('downloadable');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/downloadable-links');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource downloadable samples.
     */
    public function test_subresource_downloadable_samples(): void
    {
        $product = $this->seedSaleableProduct('downloadable');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/downloadable-samples');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource grouped products.
     */
    public function test_subresource_grouped_products(): void
    {
        $product = $this->seedSaleableProduct('grouped');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/grouped-products');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test subresource booking products.
     */
    public function test_subresource_booking_products(): void
    {
        $product = $this->seedSaleableProduct('booking');

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id.'/booking-products');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    // ============================================================================
    // Single Product GET
    // ============================================================================

    /**
     * Test get single product.
     */
    public function test_get_single_product(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'/'.$product->id);

        $response->assertOk();
        expect($response->json('id'))->toBe($product->id);
    }

    /**
     * Test unknown query params do not filter the list.
     */
    public function test_unknown_query_params_do_not_filter_the_list(): void
    {
        $product = $this->seedSaleableProduct();

        $response = $this->publicGet($this->collectionUrl.'?utm_source=newsletter&fbclid=abc&per_page=50&sort=id-desc');

        $response->assertOk();
        expect($this->productIds($response))->toContain($product->id);
    }

    /**
     * Test price filter reads the price index not the price attribute.
     */
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

    /**
     * Test price filter matches configurable products through the price index.
     */
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

    /**
     * Test price filter converts the requested range to base currency.
     */
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

    /**
     * Test downloadable link price is converted once.
     */
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

    /**
     * Seed saleable product.
     */
    private function seedSaleableProduct(string $type = 'simple', ?string $sku = null): Product
    {
        return $this->createBaseProduct($type, [
            'sku' => $sku ?? ('TEST-REST-'.uniqid()),
        ]);
    }

    /**
     * Seed guest price index.
     */
    private function seedGuestPriceIndex(Product $product, float $minPrice): void
    {
        DB::table('product_price_indices')->updateOrInsert(
            [
                'product_id' => $product->id,
                'customer_group_id' => core()->getGuestCustomerGroup()->id,
                'channel_id' => core()->getCurrentChannel()->id,
            ], [
                'min_price' => $minPrice,
                'regular_min_price' => $minPrice,
                'max_price' => $minPrice,
                'regular_max_price' => $minPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Seed test currency.
     */
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

    /**
     * Product ids.
     */
    private function productIds($response): array
    {
        return collect($response->json())->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Store the storefront default sort for the requested channel and clear the config cache.
     */
    private function storeDefaultSort(string $value): void
    {
        DB::table('core_config')->where('code', 'catalog.products.storefront.sort_by')->delete();

        DB::table('core_config')->insert([
            'code' => 'catalog.products.storefront.sort_by',
            'value' => $value,
            'channel_code' => core()->getRequestedChannelCode(),
            'locale_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->forgetCoreConfigCache();
    }
}
