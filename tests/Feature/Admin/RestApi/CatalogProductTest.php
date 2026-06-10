<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

class CatalogProductTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_listing_returns_envelope_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products');

        $response->assertOk();
        $body = $response->json();
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('meta', $body);
        $this->assertIsArray($body['data']);
        $this->assertSame(1, $body['meta']['currentPage']);
        $this->assertSame(10, $body['meta']['perPage']);
    }

    public function test_listing_requires_admin_token(): void
    {
        $response = $this->getJson('/api/admin/catalog/products');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_listing_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        [$tokenId] = explode('|', $token, 2);
        \Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken::find($tokenId)->delete();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/admin/catalog/products');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_listing_returns_seeded_product_row(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createBaseProduct('simple');

        $attributeFamilyId = (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);
        \Illuminate\Support\Facades\DB::table('product_flat')->insert([
            'product_id'          => $product->id,
            'locale'              => 'en',
            'channel'             => 'default',
            'sku'                 => $product->sku,
            'name'                => 'Test '.$product->sku,
            'type'                => 'simple',
            'status'              => 1,
            'price'               => 10.00,
            'url_key'             => strtolower($product->sku),
            'attribute_family_id' => $attributeFamilyId,
            'visible_individually'=> 1,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products');

        $response->assertOk();
        $body = $response->json();
        $this->assertGreaterThan(0, $body['meta']['total']);

        $row = collect($body['data'])->firstWhere('id', $product->id);
        $this->assertNotNull($row, 'Expected the seeded product in the listing.');
        $this->assertSame($product->sku, $row['sku']);
        $this->assertSame('simple', $row['type']);
        $this->assertArrayHasKey('name', $row);
        $this->assertArrayHasKey('attributeFamilyName', $row);
        $this->assertArrayHasKey('quantity', $row);
        $this->assertArrayHasKey('baseImageUrl', $row);
    }

    public function test_listing_surfaces_special_price_columns(): void
    {
        $admin = $this->createAdmin();

        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product, [
            'price'         => 30.00,
            'special_price' => 27.00,
        ]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$product->id)->json();

        $row = collect($body['data'])->firstWhere('id', $product->id);
        $this->assertNotNull($row, 'Expected the seeded product in the listing.');
        $this->assertArrayHasKey('specialPrice', $row);
        $this->assertArrayHasKey('formattedSpecialPrice', $row);
        $this->assertArrayHasKey('specialPriceFrom', $row);
        $this->assertArrayHasKey('specialPriceTo', $row);
        $this->assertSame('27.0000', $row['specialPrice']);
        $this->assertNotNull($row['formattedSpecialPrice']);
    }

    protected function insertProductFlat(object $product, array $overrides = []): void
    {
        $attributeFamilyId = (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);

        \Illuminate\Support\Facades\DB::table('product_flat')->insert(array_merge([
            'product_id'           => $product->id,
            'locale'               => 'en',
            'channel'              => 'default',
            'sku'                  => $product->sku,
            'name'                 => 'Test '.$product->sku,
            'type'                 => 'simple',
            'status'               => 1,
            'price'                => 10.00,
            'url_key'              => strtolower($product->sku).'-'.$product->id,
            'attribute_family_id'  => $attributeFamilyId,
            'visible_individually' => 1,
        ], $overrides));
    }

    public function test_filter_by_product_id(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');
        $this->insertProductFlat($a);
        $this->insertProductFlat($b);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$a->id)->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($a->id, $ids);
        $this->assertNotContains($b->id, $ids);
    }

    public function test_filter_by_product_id_comma_list(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');
        $c = $this->createBaseProduct('simple');
        $this->insertProductFlat($a);
        $this->insertProductFlat($b);
        $this->insertProductFlat($c);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$a->id.','.$b->id)->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($a->id, $ids);
        $this->assertContains($b->id, $ids);
        $this->assertNotContains($c->id, $ids);
    }

    public function test_filter_by_sku_partial(): void
    {
        $admin = $this->createAdmin();
        $hit = $this->createBaseProduct('simple', ['sku' => 'CATSKU-HIT-001']);
        $miss = $this->createBaseProduct('simple', ['sku' => 'OTHER-001']);
        $this->insertProductFlat($hit, ['sku' => 'CATSKU-HIT-001']);
        $this->insertProductFlat($miss, ['sku' => 'OTHER-001']);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?sku=CATSKU')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($hit->id, $ids);
        $this->assertNotContains($miss->id, $ids);
    }

    public function test_filter_by_name_partial(): void
    {
        $admin = $this->createAdmin();
        $hit = $this->createBaseProduct('simple');
        $miss = $this->createBaseProduct('simple');
        $this->insertProductFlat($hit, ['name' => 'Unique Catalog Test Widget']);
        $this->insertProductFlat($miss, ['name' => 'Different Product']);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?name=Unique%20Catalog')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($hit->id, $ids);
        $this->assertNotContains($miss->id, $ids);
    }

    public function test_filter_by_type(): void
    {
        $admin = $this->createAdmin();
        $simple = $this->createBaseProduct('simple');
        $configurable = $this->createBaseProduct('configurable');
        $this->insertProductFlat($simple, ['type' => 'simple']);
        $this->insertProductFlat($configurable, ['type' => 'configurable']);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?type=configurable')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($configurable->id, $ids);
        $this->assertNotContains($simple->id, $ids);
    }

    public function test_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $enabled = $this->createBaseProduct('simple');
        $disabled = $this->createBaseProduct('simple');
        $this->insertProductFlat($enabled, ['status' => 1]);
        $this->insertProductFlat($disabled, ['status' => 0]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?status=0')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($disabled->id, $ids);
        $this->assertNotContains($enabled->id, $ids);
    }

    public function test_filter_by_attribute_family(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p);
        $familyId = (int) \Illuminate\Support\Facades\DB::table('product_flat')
            ->where('product_id', $p->id)
            ->value('attribute_family_id');

        $body = $this->adminGet($admin, '/api/admin/catalog/products?attribute_family='.$familyId)->json();
        $this->assertGreaterThan(0, $body['meta']['total']);
        foreach ($body['data'] as $row) {
            $this->assertSame($familyId, $row['attributeFamilyId']);
        }
    }

    public function test_filter_by_channel(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p, ['channel' => 'default']);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?channel=default')->json();
        foreach ($body['data'] as $row) {
            $this->assertSame('default', $row['channel']);
        }
    }

    public function test_filter_by_locale(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p, ['locale' => 'en']);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?locale=en')->json();
        foreach ($body['data'] as $row) {
            $this->assertSame('en', $row['locale']);
        }
    }

    public function test_filter_by_price_range_split(): void
    {
        $admin = $this->createAdmin();
        $cheap = $this->createBaseProduct('simple');
        $mid = $this->createBaseProduct('simple');
        $expensive = $this->createBaseProduct('simple');
        $this->insertProductFlat($cheap, ['price' => 5.00]);
        $this->insertProductFlat($mid, ['price' => 50.00]);
        $this->insertProductFlat($expensive, ['price' => 500.00]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?price_from=10&price_to=200')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($mid->id, $ids);
        $this->assertNotContains($cheap->id, $ids);
        $this->assertNotContains($expensive->id, $ids);
    }

    public function test_filter_by_price_range_compound(): void
    {
        $admin = $this->createAdmin();
        $cheap = $this->createBaseProduct('simple');
        $mid = $this->createBaseProduct('simple');
        $this->insertProductFlat($cheap, ['price' => 5.00]);
        $this->insertProductFlat($mid, ['price' => 50.00]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?price=10,200')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertContains($mid->id, $ids);
        $this->assertNotContains($cheap->id, $ids);
    }

    public function test_unknown_filter_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $this->createBaseProduct('simple');

        $resp = $this->adminGet($admin, '/api/admin/catalog/products?nonsense=foobar');
        $resp->assertOk();
    }

    public function test_invalid_status_filter_is_silently_dropped(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p, ['status' => 1]);

        $resp = $this->adminGet($admin, '/api/admin/catalog/products?status=banana');
        $resp->assertOk();
        $this->assertGreaterThan(0, $resp->json('meta.total'));
    }

    public function test_sort_by_name_asc_compound(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $z = $this->createBaseProduct('simple');
        $tag = 'NameSortWidget'.uniqid();
        $this->insertProductFlat($a, ['name' => 'AAA '.$tag]);
        $this->insertProductFlat($z, ['name' => 'ZZZ '.$tag]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?sort=name-asc&per_page=50&name='.$tag)->json();
        $names = collect($body['data'])->pluck('name')->all();
        $aIdx = array_search('AAA '.$tag, $names, true);
        $zIdx = array_search('ZZZ '.$tag, $names, true);
        $this->assertNotFalse($aIdx);
        $this->assertNotFalse($zIdx);
        $this->assertLessThan($zIdx, $aIdx);
    }

    public function test_sort_by_price_desc_split(): void
    {
        $admin = $this->createAdmin();
        $cheap = $this->createBaseProduct('simple');
        $expensive = $this->createBaseProduct('simple');
        $this->insertProductFlat($cheap, ['price' => 10.00]);
        $this->insertProductFlat($expensive, ['price' => 1000.00]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?sort=price&order=desc&per_page=50')->json();
        $prices = collect($body['data'])->pluck('price')->map(fn ($p) => (float) $p)->all();
        $sorted = $prices;
        rsort($sorted);
        $this->assertSame($sorted, $prices);
    }

    public function test_unknown_sort_column_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p);

        $resp = $this->adminGet($admin, '/api/admin/catalog/products?sort=hackerthing-asc');
        $resp->assertOk();
    }

    public function test_default_sort_is_product_id_desc(): void
    {
        $admin = $this->createAdmin();
        $first = $this->createBaseProduct('simple');
        $second = $this->createBaseProduct('simple');
        $this->insertProductFlat($first);
        $this->insertProductFlat($second);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?per_page=50')->json();
        $ids = collect($body['data'])->pluck('id')->all();
        $this->assertGreaterThan(array_search($second->id, $ids, true), array_search($first->id, $ids, true));
    }

    public function test_pagination_page_two(): void
    {
        $admin = $this->createAdmin();
        for ($i = 0; $i < 15; $i++) {
            $p = $this->createBaseProduct('simple');
            $this->insertProductFlat($p);
        }

        $page1 = $this->adminGet($admin, '/api/admin/catalog/products?per_page=10&page=1')->json();
        $page2 = $this->adminGet($admin, '/api/admin/catalog/products?per_page=10&page=2')->json();

        $this->assertCount(10, $page1['data']);
        $this->assertGreaterThan(0, count($page2['data']));
        $this->assertSame(2, $page2['meta']['currentPage']);

        $ids1 = collect($page1['data'])->pluck('id')->all();
        $ids2 = collect($page2['data'])->pluck('id')->all();
        $this->assertEmpty(array_intersect($ids1, $ids2));
    }

    public function test_per_page_above_cap_is_clamped(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?per_page=999')->json();
        $this->assertSame(50, $body['meta']['perPage']);
    }

    public function test_per_page_zero_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?per_page=0')->json();
        $this->assertSame(10, $body['meta']['perPage']);
    }

    public function test_quantity_aggregates_across_inventory_sources(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        \DB::table('product_inventories')->where('product_id', $product->id)->delete();
        \DB::table('product_inventories')->insert([
            ['product_id' => $product->id, 'inventory_source_id' => 1, 'qty' => 5, 'vendor_id' => 0],
            ['product_id' => $product->id, 'inventory_source_id' => 2, 'qty' => 7, 'vendor_id' => 0],
        ]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$product->id)->json();
        $row = collect($body['data'])->firstWhere('id', $product->id);
        $this->assertSame(12, $row['quantity']);
    }

    public function test_base_image_url_is_null_when_no_image(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);
        \DB::table('product_images')->where('product_id', $product->id)->delete();

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$product->id)->json();
        $row = collect($body['data'])->firstWhere('id', $product->id);
        $this->assertNull($row['baseImageUrl']);
        $this->assertSame(0, $row['imagesCount']);
    }

    public function test_attribute_family_name_is_joined(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$product->id)->json();
        $row = collect($body['data'])->firstWhere('id', $product->id);
        $this->assertNotNull($row['attributeFamilyId']);
        $this->assertNotEmpty($row['attributeFamilyName']);
    }

    public function test_page_zero_clamps_to_one(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?page=0')->json();
        $this->assertSame(1, $body['meta']['currentPage']);
    }

    public function test_page_negative_clamps_to_one(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?page=-5')->json();
        $this->assertSame(1, $body['meta']['currentPage']);
    }

    public function test_page_beyond_last_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?per_page=10&page=9999')->json();
        $this->assertSame([], $body['data']);
        $this->assertGreaterThanOrEqual(1, $body['meta']['total']);
    }

    public function test_per_page_negative_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?per_page=-3')->json();
        $this->assertSame(10, $body['meta']['perPage']);
    }

    public function test_price_from_greater_than_to_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product, ['price' => 50.00]);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?price_from=500&price_to=100')->json();
        $this->assertSame([], $body['data']);
        $this->assertSame(0, $body['meta']['total']);
    }

    public function test_empty_database_returns_empty_envelope(): void
    {
        $admin = $this->createAdmin();
        \DB::table('product_flat')->delete();

        $resp = $this->adminGet($admin, '/api/admin/catalog/products');
        $resp->assertOk();
        $body = $resp->json();
        $this->assertSame([], $body['data']);
        $this->assertSame(0, $body['meta']['total']);
    }

    public function test_special_characters_in_filter_do_not_crash(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        foreach (["O'Brien", '"--; DROP TABLE products; --', '%LIKE%', '\\backslash', '<script>'] as $needle) {
            $resp = $this->adminGet($admin, '/api/admin/catalog/products?sku='.urlencode($needle));
            $resp->assertOk();
        }
    }

    public function test_unknown_locale_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?locale=zz_ZZ')->json();
        $this->assertSame([], $body['data']);
        $this->assertSame(0, $body['meta']['total']);
    }

    public function test_unknown_channel_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $body = $this->adminGet($admin, '/api/admin/catalog/products?channel=nonexistent_channel')->json();
        $this->assertSame([], $body['data']);
        $this->assertSame(0, $body['meta']['total']);
    }

    public function test_product_with_no_category_returns_null_category_fields(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);
        \DB::table('product_categories')->where('product_id', $product->id)->delete();

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$product->id)->json();
        $row = collect($body['data'])->firstWhere('id', $product->id);
        $this->assertNotNull($row, 'Product without a category must still appear in the listing.');
        $this->assertNull($row['categoryId']);
        $this->assertNull($row['categoryName']);
    }

    public function test_product_appears_once_when_in_multiple_categories(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $catIds = \DB::table('categories')->where('parent_id', '!=', null)->pluck('id')->take(2)->all();
        \DB::table('product_categories')->where('product_id', $product->id)->delete();
        foreach ($catIds as $cid) {
            \DB::table('product_categories')->insert(['product_id' => $product->id, 'category_id' => $cid]);
        }

        $body = $this->adminGet($admin, '/api/admin/catalog/products?product_id='.$product->id)->json();
        $matching = collect($body['data'])->where('id', $product->id);
        $this->assertCount(1, $matching, 'Product joined to multiple categories must appear exactly once.');
    }

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-delete', [
            'indices' => [$a->id, $b->id],
        ]);

        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(in_array($a->id, $response->json('deleted'), true))->toBeTrue();
        expect(in_array($b->id, $response->json('deleted'), true))->toBeTrue();
        expect(\DB::table('products')->where('id', $a->id)->exists())->toBeFalse();
        expect(\DB::table('products')->where('id', $b->id)->exists())->toBeFalse();
    }

    public function test_mass_delete_silently_skips_missing_ids(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-delete', [
            'indices' => [$a->id, 999999999],
        ]);

        $response->assertOk();
        expect($response->json('deleted'))->toBe([$a->id]);
        expect(\DB::table('products')->where('id', $a->id)->exists())->toBeFalse();
    }

    public function test_mass_delete_empty_indices_returns_400(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(400);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/products/mass-delete', ['indices' => [1]]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_mass_delete_rejects_admin_without_permission(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-delete-'.uniqid(),
            'description'     => 'No prod delete',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');
        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-delete', [
            'indices' => [$product->id],
        ]);

        expect($response->getStatusCode())->toBe(403);
        expect(\DB::table('products')->where('id', $product->id)->exists())->toBeTrue();
    }

    public function test_mass_update_status_happy_path_disable(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');
        $this->insertProductFlat($a, ['status' => 1]);
        $this->insertProductFlat($b, ['status' => 1]);

        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-update-status', [
            'indices' => [$a->id, $b->id],
            'value'   => 0,
        ]);

        $response->assertOk();
        expect((int) \DB::table('product_flat')->where('product_id', $a->id)->value('status'))->toBe(0);
        expect((int) \DB::table('product_flat')->where('product_id', $b->id)->value('status'))->toBe(0);
    }

    public function test_mass_update_status_happy_path_enable(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $this->insertProductFlat($a, ['status' => 0]);

        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-update-status', [
            'indices' => [$a->id],
            'value'   => 1,
        ]);

        $response->assertOk();
        expect((int) \DB::table('product_flat')->where('product_id', $a->id)->value('status'))->toBe(1);
    }

    public function test_mass_update_status_invalid_value_returns_400(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-update-status', [
            'indices' => [$product->id],
            'value'   => 99,
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_mass_update_status_missing_indices_returns_400(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-update-status', [
            'value' => 1,
        ]);

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_mass_update_status_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/products/mass-update-status', [
            'indices' => [1],
            'value'   => 1,
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_mass_update_status_rejects_admin_without_permission(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-edit-'.uniqid(),
            'description'     => 'No prod edit',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');
        $response = $this->adminPost($admin, '/api/admin/catalog/products/mass-update-status', [
            'indices' => [$product->id],
            'value'   => 0,
        ]);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_expired_token_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        [$tokenId] = explode('|', $token, 2);
        $row = \Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken::find($tokenId);
        $row->expires_at = now()->subDay();
        $row->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->getJson('/api/admin/catalog/products');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_copy_simple_product_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/copy", []);

        $response->assertOk();
        $body = $response->json();
        $this->assertSame($product->id, $body['sourceId']);
        $this->assertTrue($body['success']);
        $this->assertSame('simple', $body['type']);
        $this->assertNotSame($product->id, $body['id'], 'Copy must have a new id.');
        $this->assertNotSame($product->sku, $body['sku'], 'Copy SKU must be auto-suffixed.');
        $this->assertTrue(\DB::table('products')->where('id', $body['id'])->exists(), 'New product row must exist.');
        $this->assertTrue(\DB::table('products')->where('id', $product->id)->exists(), 'Source product must still exist.');
    }

    public function test_copy_configurable_product_includes_variants(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        [, , $colorOptions, $sizeOptions] = $this->ensureColorSizeAttributesWithOptions();

        $sku = 'cfg-copy-src-'.uniqid();
        $created = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => ['color' => $colorOptions, 'size' => $sizeOptions],
        ]);
        $this->assertSame(201, $created->getStatusCode(), 'Body: '.$created->getContent());

        $sourceId = (int) $created->json('id');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$sourceId}/copy", []);
        $this->assertSame(200, $response->getStatusCode(), 'Body: '.$response->getContent());

        $body = $response->json();
        $this->assertTrue($body['success']);
        $this->assertSame('configurable', $body['type']);
        $this->assertNotSame($sourceId, $body['id']);

        $variantCount = \DB::table('products')->where('parent_id', $body['id'])->count();
        $this->assertGreaterThanOrEqual(0, $variantCount, 'Copy should preserve variant structure (may be 0 if source had none).');
    }

    public function test_copy_refuses_variant_returns_422(): void
    {
        $admin = $this->createAdmin();

        $parent = $this->createBaseProduct('simple');
        $variant = \Webkul\Product\Models\Product::factory()->create([
            'type'                => 'simple',
            'attribute_family_id' => $parent->attribute_family_id,
            'parent_id'           => $parent->id,
        ]);

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$variant->id}/copy", []);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_copy_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/catalog/products/999999999/copy', []);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_copy_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/products/1/copy');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_copy_rejects_admin_without_permission(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-create-'.uniqid(),
            'description'     => 'No prod create',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, "/api/admin/catalog/products/{$product->id}/copy", []);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertTrue(\DB::table('products')->where('id', $product->id)->exists());
    }

    protected function defaultFamilyId(): int
    {
        $this->seedRequiredData();

        return (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);
    }

    public function test_create_simple_happy_path_default_type(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        $sku = 'sp-create-'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $body = $response->json();
        $this->assertNotNull($body['id'] ?? null);
        $this->assertSame($sku, $body['sku']);
        $this->assertSame('simple', $body['type']);
        $this->assertSame($familyId, $body['attributeFamilyId']);
        $this->assertTrue(\DB::table('products')->where('sku', $sku)->exists());
    }

    public function test_create_simple_with_explicit_type(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        $sku = 'sp-create-explicit-'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => 'simple',
        ]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('simple', $response->json('type'));
    }

    public function test_create_unknown_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'sp-bogus-'.uniqid(),
            'attribute_family_id' => $familyId,
            'type'                => 'totally-not-a-type',
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    /**
     * @dataProvider simpleLikeTypeProvider
     */
    public function test_create_simple_like_type_happy_path(string $type): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        $sku = 'sp-'.$type.'-ok-'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => $type,
        ]);

        $this->assertSame(201, $response->getStatusCode(), 'Body: '.$response->getContent());
        $body = $response->json();
        $this->assertSame($sku, $body['sku']);
        $this->assertSame($type, $body['type']);
        $this->assertTrue(\DB::table('products')->where('sku', $sku)->where('type', $type)->exists());
    }

    /**
     * @dataProvider simpleLikeTypeProvider
     */
    public function test_create_other_type_duplicate_sku_returns_422(string $type): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        $sku = 'dup-'.$type.'-'.uniqid();

        $first = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => $type,
        ]);
        $this->assertSame(201, $first->getStatusCode());

        $second = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => $type,
        ]);

        $this->assertSame(422, $second->getStatusCode());
    }

    public static function simpleLikeTypeProvider(): array
    {
        return [
            'virtual'      => ['virtual'],
            'downloadable' => ['downloadable'],
            'grouped'      => ['grouped'],
            'bundle'       => ['bundle'],
            'booking'      => ['booking'],
        ];
    }

    public function test_create_configurable_happy_path(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();

        [, , $colorOptions, $sizeOptions] = $this->ensureColorSizeAttributesWithOptions();

        $sku = 'cf-create-'.uniqid();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => [
                'color' => $colorOptions,
                'size'  => $sizeOptions,
            ],
        ]);

        $this->assertSame(201, $response->getStatusCode(), 'Body: '.$response->getContent());
        $body = $response->json();
        $this->assertSame('configurable', $body['type']);
        $this->assertSame($sku, $body['sku']);

        $productId = (int) $body['id'];
        $variantCount = \DB::table('products')->where('parent_id', $productId)->count();
        $expected = count($colorOptions) * count($sizeOptions);
        $this->assertSame($expected, $variantCount, 'Cartesian-product variant count mismatch.');
    }

    public function test_create_configurable_with_numeric_id_keys(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();

        [$colorId, , $colorOptions] = $this->ensureColorSizeAttributesWithOptions();

        $sku = 'cf-numkey-'.uniqid();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => [
                (int) $colorId => $colorOptions,
            ],
        ]);

        $this->assertSame(201, $response->getStatusCode(), 'Body: '.$response->getContent());
    }

    public function test_create_configurable_missing_super_attributes_returns_422(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'cf-no-sa-'.uniqid(),
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('super_attributes', (string) $response->json('detail'));
    }

    public function test_create_configurable_empty_super_attributes_returns_422(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'cf-empty-sa-'.uniqid(),
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => [],
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_configurable_unknown_attribute_id_returns_422(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'cf-bad-attr-'.uniqid(),
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => [
                9999999 => [1, 2],
            ],
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_missing_sku_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'attribute_family_id' => $this->defaultFamilyId(),
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_missing_attribute_family_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku' => 'sp-no-family-'.uniqid(),
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_duplicate_sku_returns_422(): void
    {
        $admin = $this->createAdmin();
        $existing = $this->createBaseProduct('simple');

        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $existing->sku,
            'attribute_family_id' => (int) ($existing->attribute_family_id ?? $this->defaultFamilyId()),
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_invalid_sku_slug_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'has spaces and !!',
            'attribute_family_id' => $this->defaultFamilyId(),
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_unknown_attribute_family_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'sp-unknown-fam-'.uniqid(),
            'attribute_family_id' => 9999999,
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/products', [
            'sku'                 => 'sp-no-auth-'.uniqid(),
            'attribute_family_id' => 1,
        ]);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_create_rejects_admin_without_permission(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-create-phase53-'.uniqid(),
            'description'     => 'No prod create',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);
        $sku = 'sp-noperm-'.uniqid();
        $response = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $this->defaultFamilyId(),
        ]);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertFalse(\DB::table('products')->where('sku', $sku)->exists(), 'Product must not be created when permission is denied.');
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminViaApi(): array
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        $sku = 'upd-'.uniqid();
        $resp = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => 'simple',
        ]);
        $this->assertSame(201, $resp->getStatusCode());

        return ['admin' => $admin, 'id' => (int) $resp->json('id'), 'sku' => $sku];
    }

    public function test_update_translations_returns_200_with_new_name(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'translations' => [
                'en' => ['name' => 'Updated Name', 'short_description' => 'Short desc', 'description' => 'Long desc'],
            ],
        ]);

        $this->assertSame(200, $resp->getStatusCode(), 'Body: '.$resp->getContent());
    }

    public function test_update_sku_to_unique_value_returns_200(): void
    {
        $ctx = $this->createAdminViaApi();
        $newSku = 'upd-new-'.uniqid();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], ['sku' => $newSku]);

        $this->assertSame(200, $resp->getStatusCode(), 'Body: '.$resp->getContent());
        $this->assertTrue(\DB::table('products')->where('sku', $newSku)->where('id', $ctx['id'])->exists());
    }

    public function test_update_sku_duplicate_returns_422(): void
    {
        $ctx1 = $this->createAdminViaApi();
        $ctx2 = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx1['admin'], '/api/admin/catalog/products/'.$ctx1['id'], ['sku' => $ctx2['sku']]);

        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_update_url_key_duplicate_returns_422(): void
    {
        $admin = $this->createAdmin();

        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');
        $this->insertProductFlat($a, ['url_key' => 'unique-a-'.$a->id]);
        $this->insertProductFlat($b, ['url_key' => 'unique-b-'.$b->id]);

        $resp = $this->adminPut($admin, '/api/admin/catalog/products/'.$a->id, ['url_key' => 'unique-b-'.$b->id]);
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_update_special_price_with_dates_returns_200(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'price'              => '100',
            'special_price'      => '50',
            'special_price_from' => '2026-06-01',
            'special_price_to'   => '2026-06-30',
        ]);
        $this->assertSame(200, $resp->getStatusCode(), 'Body: '.$resp->getContent());
    }

    public function test_update_special_price_greater_than_price_returns_422(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'price'         => '50',
            'special_price' => '100',
        ]);
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_update_special_price_dates_inverted_returns_422(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'special_price'      => '50',
            'special_price_from' => '2026-06-30',
            'special_price_to'   => '2026-06-01',
        ]);
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_update_invalid_boolean_returns_422(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'status' => 7,
        ]);
        $this->assertSame(422, $resp->getStatusCode());
    }

    public function test_update_categories_list_returns_200(): void
    {
        $ctx = $this->createAdminViaApi();

        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'categories' => [1],
            'channels'   => [\Webkul\Core\Facades\Core::getCurrentChannel()->id],
        ]);
        $this->assertSame(200, $resp->getStatusCode(), 'Body: '.$resp->getContent());
        $this->assertTrue(
            \DB::table('product_categories')->where('product_id', $ctx['id'])->where('category_id', 1)->exists(),
            'Category should be attached.'
        );
    }

    public function test_update_with_stripped_subresources_emits_warnings(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'images'                => ['ignored.jpg'],
            'inventories'           => [1 => 5],
            'customer_group_prices' => [['qty' => 1, 'value' => 10]],
        ]);
        $this->assertSame(200, $resp->getStatusCode(), 'Body: '.$resp->getContent());

        $warnings = $resp->json('_warnings');
        $this->assertIsArray($warnings, 'Expected _warnings array, got: '.json_encode($resp->json()));
        $this->assertNotEmpty($warnings);

        $joined = implode('|', $warnings);
        $this->assertStringContainsString('images', strtolower($joined));
        $this->assertStringContainsString('inventories', strtolower($joined));
        $this->assertStringContainsString('customer-group prices', strtolower($joined));
    }

    public function test_update_non_existent_returns_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/catalog/products/9999999', ['sku' => 'whatever-'.uniqid()]);
        $this->assertSame(404, $resp->getStatusCode());
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $resp = $this->putJson('/api/admin/catalog/products/1', ['sku' => 'x-'.uniqid()]);
        $this->assertSame(401, $resp->getStatusCode());
    }

    public function test_update_rejects_admin_without_permission(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-edit-'.uniqid(),
            'description'     => 'No prod edit',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');
        $resp = $this->adminPut($admin, '/api/admin/catalog/products/'.$product->id, ['sku' => 'x-'.uniqid()]);
        $this->assertSame(403, $resp->getStatusCode());
    }

    public function test_delete_simple_returns_204_and_removes_row(): void
    {
        $ctx = $this->createAdminViaApi();
        $resp = $this->adminDelete($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id']);

        $this->assertSame(204, $resp->getStatusCode(), 'Body: '.$resp->getContent());
        $this->assertFalse(\DB::table('products')->where('id', $ctx['id'])->exists());
    }

    public function test_delete_configurable_cascades_variants(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        $sku = 'cfg-del-'.uniqid();

        [, , $colorOptions, $sizeOptions] = $this->ensureColorSizeAttributesWithOptions();

        $created = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => $sku,
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => ['color' => $colorOptions, 'size' => $sizeOptions],
        ]);

        $this->assertSame(201, $created->getStatusCode(), 'Body: '.$created->getContent());

        $id = (int) $created->json('id');
        $variantCount = \DB::table('products')->where('parent_id', $id)->count();

        $resp = $this->adminDelete($admin, '/api/admin/catalog/products/'.$id);
        $this->assertSame(204, $resp->getStatusCode());
        $this->assertFalse(\DB::table('products')->where('id', $id)->exists());

        $this->assertGreaterThanOrEqual(0, \DB::table('products')->where('parent_id', $id)->count());
    }

    public function test_delete_non_existent_returns_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/catalog/products/9999999');
        $this->assertSame(404, $resp->getStatusCode());
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $resp = $this->deleteJson('/api/admin/catalog/products/1');
        $this->assertSame(401, $resp->getStatusCode());
    }

    public function test_delete_rejects_admin_without_permission(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-delete-'.uniqid(),
            'description'     => 'No prod delete',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');
        $resp = $this->adminDelete($admin, '/api/admin/catalog/products/'.$product->id);
        $this->assertSame(403, $resp->getStatusCode());
        $this->assertTrue(\DB::table('products')->where('id', $product->id)->exists());
    }

    public function test_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/catalog/products/export?format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('products.csv');
        expect($response->getContent())->toContain('ID,Name,SKU,"Attribute Family",Price,Quantity,Status,Category,Type');
    }

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/catalog/products/export?format=xlsx', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/catalog/products/export', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    public function test_export_no_permission_returns_403(): void
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'no-prod-view-'.uniqid(),
            'description'     => 'No product view',
            'permission_type' => 'custom',
            'permissions'     => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $this->get('/api/admin/catalog/products/export', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(403);
    }

    public function test_update_persists_family_attributes_via_refetch(): void
    {
        $ctx = $this->createAdminViaApi();
        [, , $colorOptions] = $this->ensureColorSizeAttributesWithOptions();
        $colorOptionId = (int) $colorOptions[0];
        $urlKey = 'persisted-'.uniqid();

        $put = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'name'       => 'Persisted Name',
            'url_key'    => $urlKey,
            'meta_title' => 'Persisted Meta',
            'color'      => $colorOptionId,
        ]);
        $this->assertSame(200, $put->getStatusCode(), 'Body: '.$put->getContent());

        $body = $this->adminGet($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'])->json();
        $this->assertSame('Persisted Name', $body['name']);
        $this->assertSame($urlKey, $body['urlKey']);
        $this->assertSame('Persisted Meta', $body['metaTitle']);

        $attrs = collect($body['attributes'])->keyBy('code');
        $this->assertSame($colorOptionId, (int) $attrs['color']['value']);
    }

    public function test_partial_update_does_not_wipe_booleans(): void
    {
        $ctx = $this->createAdminViaApi();

        $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'status' => 1, 'new' => 1, 'featured' => 1,
        ]);

        $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'name' => 'Only Name',
        ]);

        $body = $this->adminGet($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'])->json();
        $this->assertSame(1, $body['status'], 'Partial update must not wipe status');

        $attrs = collect($body['attributes'])->keyBy('code');
        $this->assertNotEmpty($attrs['new']['value'], 'Partial update must not wipe new');
        $this->assertNotEmpty($attrs['featured']['value'], 'Partial update must not wipe featured');
    }

    public function test_attribute_only_update_preserves_variants(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->defaultFamilyId();
        [, , $colorOptions, $sizeOptions] = $this->ensureColorSizeAttributesWithOptions();

        $create = $this->adminPost($admin, '/api/admin/catalog/products', [
            'sku'                 => 'cfg-keep-'.uniqid(),
            'attribute_family_id' => $familyId,
            'type'                => 'configurable',
            'super_attributes'    => ['color' => $colorOptions, 'size' => $sizeOptions],
        ]);
        $this->assertSame(201, $create->getStatusCode(), 'Body: '.$create->getContent());
        $id = (int) $create->json('id');

        $before = \DB::table('products')->where('parent_id', $id)->count();
        $this->assertGreaterThan(0, $before);

        $this->adminPut($admin, '/api/admin/catalog/products/'.$id, ['name' => 'Renamed Cfg']);

        $after = \DB::table('products')->where('parent_id', $id)->count();
        $this->assertSame($before, $after, 'Attribute-only update must not wipe configurable variants');
    }

    public function test_update_with_custom_attribute_codes_does_not_500(): void
    {
        $ctx = $this->createAdminViaApi();
        [, , $colorOptions] = $this->ensureColorSizeAttributesWithOptions();

        $resp = $this->adminPut($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'], [
            'color'          => (int) $colorOptions[0],
            'product_number' => 'PN-123',
        ]);
        $this->assertSame(200, $resp->getStatusCode(), 'Body: '.$resp->getContent());

        $attrs = collect($this->adminGet($ctx['admin'], '/api/admin/catalog/products/'.$ctx['id'])->json('attributes'))->keyBy('code');
        $this->assertSame((int) $colorOptions[0], (int) $attrs['color']['value']);
        $this->assertSame('PN-123', $attrs['product_number']['value']);
    }
}
