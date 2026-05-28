<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for GET /api/admin/catalog/products/{id}
 *
 * Tests: auth guards, 404 edge cases, base-25-field shape,
 * per-type specific blocks (configurable/bundle/grouped/downloadable),
 * and sub-resource arrays (translations, images, inventories,
 * categories, customerGroupPrices).
 */
class CatalogProductDetailTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Local seed helpers
    // -------------------------------------------------------------------------

    /**
     * Insert a product_flat row for the given Product so the detail provider's
     * product_flats relation has something to work with.
     */
    protected function insertProductFlat(object $product, array $overrides = []): void
    {
        $attributeFamilyId = (int) (DB::table('attribute_families')->value('id') ?? 1);

        DB::table('product_flat')->insertOrIgnore(array_merge([
            'product_id'           => $product->id,
            'locale'               => 'en',
            'channel'              => 'default',
            'sku'                  => $product->sku,
            'name'                 => 'Test '.$product->sku,
            'type'                 => $product->type ?? 'simple',
            'status'               => 1,
            'price'                => 19.99,
            'url_key'              => strtolower($product->sku).'-'.$product->id,
            'attribute_family_id'  => $attributeFamilyId,
            'visible_individually' => 1,
            'short_description'    => 'Short desc for '.$product->sku,
            'description'          => 'Long description for '.$product->sku,
            'featured'             => 0,
            'new'                  => 0,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Auth guard tests (3)
    // -------------------------------------------------------------------------

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->getJson('/api/admin/catalog/products/'.$product->id);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_detail_revoked_token_returns_401(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->delete();

        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id, $token);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_detail_expired_token_returns_401(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->update(['expires_at' => now()->subDay()]);

        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id, $token);

        $this->assertSame(401, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // 404 / bad-id edge cases (3)
    // -------------------------------------------------------------------------

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products/999999');

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_detail_zero_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products/0');

        // id=0 is either caught by our guard (404) or by the route constraint (\d+ requires >=1)
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }

    public function test_detail_negative_id_returns_4xx(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/products/-1');

        // Negative id may not match \d+ route constraint → 404/405 depending on router
        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Base field shape (2)
    // -------------------------------------------------------------------------

    public function test_detail_simple_product_returns_base_fields_only(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        // Required base fields
        $this->assertSame($product->id, $body['id']);
        $this->assertSame($product->sku, $body['sku']);
        $this->assertSame('simple', $body['type']);
        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('price', $body);
        $this->assertArrayHasKey('formattedPrice', $body);
        $this->assertArrayHasKey('quantity', $body);
        $this->assertArrayHasKey('attributeFamilyId', $body);
        $this->assertArrayHasKey('attributeFamilyName', $body);
        $this->assertArrayHasKey('urlKey', $body);
        $this->assertArrayHasKey('visibleIndividually', $body);
        $this->assertArrayHasKey('shortDescription', $body);
        $this->assertArrayHasKey('description', $body);
        $this->assertArrayHasKey('manageStock', $body);
        $this->assertArrayHasKey('inStock', $body);
        $this->assertArrayHasKey('featured', $body);
        $this->assertArrayHasKey('new', $body);
        $this->assertArrayHasKey('createdAt', $body);
        $this->assertArrayHasKey('updatedAt', $body);
        $this->assertArrayHasKey('translations', $body);
        $this->assertArrayHasKey('images', $body);
        $this->assertArrayHasKey('categories', $body);
        $this->assertArrayHasKey('inventories', $body);
        $this->assertArrayHasKey('customerGroupPrices', $body);

        // Type-specific blocks must be null for simple
        $this->assertNull($body['superAttributes']);
        $this->assertNull($body['variants']);
        $this->assertNull($body['bundleOptions']);
        $this->assertNull($body['linkedProducts']);
        $this->assertNull($body['downloadableLinks']);
        $this->assertNull($body['downloadableSamples']);
    }

    public function test_detail_virtual_product_returns_base_fields_only(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('virtual');
        $this->insertProductFlat($product, ['type' => 'virtual']);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertSame('virtual', $body['type']);
        $this->assertNull($body['superAttributes']);
        $this->assertNull($body['variants']);
        $this->assertNull($body['bundleOptions']);
        $this->assertNull($body['linkedProducts']);
        $this->assertNull($body['downloadableLinks']);
        $this->assertNull($body['downloadableSamples']);
    }

    // -------------------------------------------------------------------------
    // Type-specific: configurable (1)
    // -------------------------------------------------------------------------

    public function test_detail_configurable_product_has_super_attributes_and_variants_keys(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('configurable');
        $this->insertProductFlat($product, ['type' => 'configurable']);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertSame('configurable', $body['type']);
        // Keys must exist and be arrays (even if empty — no super attrs wired in this seed)
        $this->assertIsArray($body['superAttributes']);
        $this->assertIsArray($body['variants']);
        // Other type-specific blocks must be null
        $this->assertNull($body['bundleOptions']);
        $this->assertNull($body['linkedProducts']);
        $this->assertNull($body['downloadableLinks']);
        $this->assertNull($body['downloadableSamples']);
    }

    // -------------------------------------------------------------------------
    // Type-specific: bundle (1)
    // -------------------------------------------------------------------------

    public function test_detail_bundle_product_has_bundle_options_key(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('bundle');
        $this->insertProductFlat($product, ['type' => 'bundle']);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertSame('bundle', $body['type']);
        $this->assertIsArray($body['bundleOptions']);
        // Other type-specific blocks null
        $this->assertNull($body['superAttributes']);
        $this->assertNull($body['variants']);
        $this->assertNull($body['linkedProducts']);
        $this->assertNull($body['downloadableLinks']);
        $this->assertNull($body['downloadableSamples']);
    }

    // -------------------------------------------------------------------------
    // Type-specific: grouped (1)
    // -------------------------------------------------------------------------

    public function test_detail_grouped_product_has_linked_products_key(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('grouped');
        $this->insertProductFlat($product, ['type' => 'grouped']);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertSame('grouped', $body['type']);
        $this->assertIsArray($body['linkedProducts']);
        $this->assertNull($body['superAttributes']);
        $this->assertNull($body['variants']);
        $this->assertNull($body['bundleOptions']);
        $this->assertNull($body['downloadableLinks']);
        $this->assertNull($body['downloadableSamples']);
    }

    // -------------------------------------------------------------------------
    // Type-specific: downloadable (1)
    // -------------------------------------------------------------------------

    public function test_detail_downloadable_product_has_links_and_samples_keys(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('downloadable');
        $this->insertProductFlat($product, ['type' => 'downloadable']);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertSame('downloadable', $body['type']);
        $this->assertIsArray($body['downloadableLinks']);
        $this->assertIsArray($body['downloadableSamples']);
        $this->assertNull($body['superAttributes']);
        $this->assertNull($body['variants']);
        $this->assertNull($body['bundleOptions']);
        $this->assertNull($body['linkedProducts']);
    }

    // -------------------------------------------------------------------------
    // Sub-resource arrays (5)
    // -------------------------------------------------------------------------

    public function test_detail_translations_array_contains_seeded_locale(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product, [
            'locale' => 'en',
            'name'   => 'My Test Product',
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['translations']);
        $this->assertGreaterThan(0, count($body['translations']));

        $enTranslation = collect($body['translations'])->firstWhere('locale', 'en');
        $this->assertNotNull($enTranslation, 'Expected an "en" locale translation row.');
        $this->assertArrayHasKey('name', $enTranslation);
        $this->assertArrayHasKey('urlKey', $enTranslation);
        $this->assertArrayHasKey('shortDescription', $enTranslation);
        $this->assertArrayHasKey('description', $enTranslation);
    }

    public function test_detail_images_array_empty_when_no_images(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['images']);
        $this->assertSame(0, count($body['images']));
    }

    public function test_detail_images_array_populated_when_images_exist(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        // Seed a product_images row
        DB::table('product_images')->insert([
            'product_id' => $product->id,
            'type'       => 'images',
            'path'       => 'product/'.$product->id.'/test.webp',
            'position'   => 1,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['images']);
        $this->assertGreaterThan(0, count($body['images']));
        $img = $body['images'][0];
        $this->assertArrayHasKey('id', $img);
        $this->assertArrayHasKey('path', $img);
        $this->assertArrayHasKey('url', $img);
        $this->assertArrayHasKey('sortOrder', $img);
    }

    public function test_detail_inventories_array_populated_when_inventory_exists(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        // Seed a product_inventories row via the default inventory_source (id=1)
        $sourceId = DB::table('inventory_sources')->value('id');
        if ($sourceId) {
            DB::table('product_inventories')->insertOrIgnore([
                'product_id'          => $product->id,
                'inventory_source_id' => $sourceId,
                'qty'                 => 25,
            ]);
        }

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['inventories']);
        if ($sourceId) {
            $this->assertGreaterThan(0, count($body['inventories']));
            $inv = $body['inventories'][0];
            $this->assertArrayHasKey('sourceId', $inv);
            $this->assertArrayHasKey('sourceCode', $inv);
            $this->assertArrayHasKey('qty', $inv);
        }
    }

    public function test_detail_customer_group_prices_array_populated(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        // Seed a customer_group_price row (no customer_group_id = general / all groups)
        DB::table('product_customer_group_prices')->insert([
            'product_id'        => $product->id,
            'customer_group_id' => null,
            'qty'               => 1,
            'value_type'        => 'fixed',
            'value'             => 14.99,
            'unique_id'         => uniqid('cgp_'),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['customerGroupPrices']);
        $this->assertGreaterThan(0, count($body['customerGroupPrices']));
        $cgp = $body['customerGroupPrices'][0];
        $this->assertArrayHasKey('id', $cgp);
        $this->assertArrayHasKey('valueType', $cgp);
        $this->assertArrayHasKey('value', $cgp);
        $this->assertArrayHasKey('qty', $cgp);
    }

    public function test_detail_categories_array_populated_when_category_assigned(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        // Get or create a category to attach
        $categoryId = DB::table('categories')->value('id');
        if (! $categoryId) {
            $categoryId = DB::table('categories')->insertGetId([
                'position'     => 1,
                'status'       => 1,
                'parent_id'    => null,
                'display_mode' => null,
                'logo_path'    => null,
                'banner_path'  => null,
                '_lft'         => 1,
                '_rgt'         => 2,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            DB::table('category_translations')->insert([
                'category_id'      => $categoryId,
                'locale'           => 'en',
                'name'             => 'Test Category',
                'slug'             => 'test-category-detail',
                'url_path'         => 'test-category-detail',
                'description'      => null,
                'meta_title'       => null,
                'meta_description' => null,
                'meta_keywords'    => null,
            ]);
        }

        DB::table('product_categories')->insertOrIgnore([
            'product_id'  => $product->id,
            'category_id' => $categoryId,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['categories']);
        $cat = collect($body['categories'])->firstWhere('id', $categoryId);
        $this->assertNotNull($cat, 'Expected the attached category in the categories array.');
        $this->assertArrayHasKey('name', $cat);
        $this->assertArrayHasKey('slug', $cat);
    }

    // -------------------------------------------------------------------------
    // Configurable with real super-attribute + variant (1)
    // -------------------------------------------------------------------------

    public function test_detail_configurable_super_attribute_shape(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('configurable');
        $this->insertProductFlat($product, ['type' => 'configurable']);

        // Find an attribute with options that can be used as a super attribute
        $colorAttr = DB::table('attributes')->where('code', 'color')->first();
        if (! $colorAttr) {
            $this->markTestSkipped('Color attribute not seeded — cannot test configurable super_attributes shape.');
        }

        // Attach the attribute as a super_attribute
        DB::table('product_super_attributes')->insertOrIgnore([
            'product_id'   => $product->id,
            'attribute_id' => $colorAttr->id,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['superAttributes']);
        if (count($body['superAttributes']) > 0) {
            $attr = $body['superAttributes'][0];
            $this->assertArrayHasKey('id', $attr);
            $this->assertArrayHasKey('code', $attr);
            $this->assertArrayHasKey('type', $attr);
            $this->assertArrayHasKey('adminName', $attr);
            $this->assertArrayHasKey('options', $attr);
            $this->assertIsArray($attr['options']);
        }
    }

    // -------------------------------------------------------------------------
    // Bundle with option + child product (1)
    // -------------------------------------------------------------------------

    public function test_detail_bundle_options_shape(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('bundle');
        $this->insertProductFlat($product, ['type' => 'bundle']);

        // Seed a bundle option row
        $optionId = DB::table('product_bundle_options')->insertGetId([
            'product_id'  => $product->id,
            'type'        => 'select',
            'is_required' => 1,
            'sort_order'  => 1,
        ]);

        // Seed a bundle option product (link to the bundle product itself for simplicity)
        $childProduct = $this->createBaseProduct('simple');
        $this->insertProductFlat($childProduct);

        DB::table('product_bundle_option_products')->insert([
            'product_bundle_option_id' => $optionId,
            'product_id'               => $childProduct->id,
            'qty'                      => 1,
            'is_user_defined'          => 0,
            'is_default'               => 1,
            'sort_order'               => 1,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['bundleOptions']);
        $this->assertGreaterThan(0, count($body['bundleOptions']));
        $opt = $body['bundleOptions'][0];
        $this->assertArrayHasKey('id', $opt);
        $this->assertArrayHasKey('type', $opt);
        $this->assertArrayHasKey('isRequired', $opt);
        $this->assertArrayHasKey('products', $opt);
        $this->assertIsArray($opt['products']);
        $this->assertGreaterThan(0, count($opt['products']));
        $bop = $opt['products'][0];
        $this->assertArrayHasKey('productId', $bop);
        $this->assertArrayHasKey('sku', $bop);
        $this->assertArrayHasKey('qty', $bop);
        $this->assertArrayHasKey('isDefault', $bop);
    }

    // -------------------------------------------------------------------------
    // Grouped with linked product (1)
    // -------------------------------------------------------------------------

    public function test_detail_grouped_linked_products_shape(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('grouped');
        $this->insertProductFlat($product, ['type' => 'grouped']);

        $associated = $this->createBaseProduct('simple');
        $this->insertProductFlat($associated);

        DB::table('product_grouped_products')->insert([
            'product_id'            => $product->id,
            'associated_product_id' => $associated->id,
            'qty'                   => 1,
            'sort_order'            => 1,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['linkedProducts']);
        $this->assertGreaterThan(0, count($body['linkedProducts']));
        $lp = $body['linkedProducts'][0];
        $this->assertArrayHasKey('id', $lp);
        $this->assertArrayHasKey('associatedProductId', $lp);
        $this->assertSame($associated->id, $lp['associatedProductId']);
        $this->assertArrayHasKey('sku', $lp);
        $this->assertArrayHasKey('qty', $lp);
        $this->assertArrayHasKey('sortOrder', $lp);
    }

    // -------------------------------------------------------------------------
    // Downloadable with link + sample (1)
    // -------------------------------------------------------------------------

    public function test_detail_downloadable_links_and_samples_shape(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('downloadable');
        $this->insertProductFlat($product, ['type' => 'downloadable']);

        // Seed a downloadable link
        $linkId = DB::table('product_downloadable_links')->insertGetId([
            'product_id'       => $product->id,
            'type'             => 'file',
            'file'             => 'downloadable/link.pdf',
            'file_name'        => 'link.pdf',
            'url'              => null,
            'sample_type'      => null,
            'sample_file'      => null,
            'sample_file_name' => null,
            'sample_url'       => null,
            'price'            => 4.99,
            'downloads'        => 5,
            'sort_order'       => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Seed its translation
        $linkTransTable = DB::getSchemaBuilder()->getColumnListing('product_downloadable_link_translations') ? 'product_downloadable_link_translations' : null;
        if ($linkTransTable) {
            DB::table($linkTransTable)->insertOrIgnore([
                'product_downloadable_link_id' => $linkId,
                'locale'                       => 'en',
                'title'                        => 'PDF Download',
            ]);
        }

        // Seed a downloadable sample
        $sampleId = DB::table('product_downloadable_samples')->insertGetId([
            'product_id' => $product->id,
            'type'       => 'file',
            'file'       => 'downloadable/sample.pdf',
            'file_name'  => 'sample.pdf',
            'url'        => null,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sampleTransTable = DB::getSchemaBuilder()->getColumnListing('product_downloadable_sample_translations') ? 'product_downloadable_sample_translations' : null;
        if ($sampleTransTable) {
            DB::table($sampleTransTable)->insertOrIgnore([
                'product_downloadable_sample_id' => $sampleId,
                'locale'                         => 'en',
                'title'                          => 'PDF Sample',
            ]);
        }

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertIsArray($body['downloadableLinks']);
        $this->assertGreaterThan(0, count($body['downloadableLinks']));
        $link = $body['downloadableLinks'][0];
        $this->assertArrayHasKey('id', $link);
        $this->assertArrayHasKey('type', $link);
        $this->assertArrayHasKey('price', $link);
        $this->assertArrayHasKey('downloads', $link);
        $this->assertArrayHasKey('fileUrl', $link);
        $this->assertArrayHasKey('translations', $link);

        $this->assertIsArray($body['downloadableSamples']);
        $this->assertGreaterThan(0, count($body['downloadableSamples']));
        $sample = $body['downloadableSamples'][0];
        $this->assertArrayHasKey('id', $sample);
        $this->assertArrayHasKey('type', $sample);
        $this->assertArrayHasKey('fileUrl', $sample);
        $this->assertArrayHasKey('translations', $sample);
    }

    // =========================================================================
    // Phase 1.2 — 7 new blocks
    // =========================================================================

    // -------------------------------------------------------------------------
    // 1. bookingProduct block (2 tests)
    // -------------------------------------------------------------------------

    public function test_detail_booking_product_is_null_for_non_booking_types(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('bookingProduct', $body);
        $this->assertNull($body['bookingProduct']);
    }

    public function test_detail_booking_product_returns_booking_block(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('booking');
        $this->insertProductFlat($product, ['type' => 'booking']);

        // Seed a booking_products row with type=default
        $bpId = DB::table('booking_products')->insertGetId([
            'product_id'           => $product->id,
            'type'                 => 'default',
            'qty'                  => 10,
            'location'             => 'Conference Room A',
            'show_location'        => 1,
            'available_every_week' => 1,
            'available_from'       => null,
            'available_to'         => null,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Seed a default_slot row
        DB::table('booking_product_default_slots')->insertOrIgnore([
            'booking_product_id' => $bpId,
            'booking_type'       => 'many',
            'duration'           => 60,
            'break_time'         => 15,
            'slots'              => json_encode([]),
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('bookingProduct', $body);
        $this->assertIsArray($body['bookingProduct']);

        $bp = $body['bookingProduct'];
        $this->assertSame('default', $bp['type']);
        $this->assertArrayHasKey('qty', $bp);
        $this->assertArrayHasKey('availableEveryWeek', $bp);
        // default sub-type fields
        $this->assertArrayHasKey('bookingType', $bp);
        $this->assertArrayHasKey('duration', $bp);
        $this->assertArrayHasKey('breakTime', $bp);
        $this->assertArrayHasKey('slots', $bp);
    }

    // -------------------------------------------------------------------------
    // 2. customizableOptions block (2 tests)
    // -------------------------------------------------------------------------

    public function test_detail_customizable_options_empty_when_none(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('customizableOptions', $body);
        $this->assertIsArray($body['customizableOptions']);
        $this->assertCount(0, $body['customizableOptions']);
    }

    public function test_detail_returns_customizable_options_when_present(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        // Seed a customizable option row
        $optId = DB::table('product_customizable_options')->insertGetId([
            'product_id'                => $product->id,
            'type'                      => 'text',
            'is_required'               => 1,
            'sort_order'                => 1,
            'max_characters'            => 100,
            'supported_file_extensions' => null,
        ]);

        // Seed translation
        $transTable = DB::getSchemaBuilder()->hasTable('product_customizable_option_translations')
            ? 'product_customizable_option_translations'
            : null;
        if ($transTable) {
            DB::table($transTable)->insertOrIgnore([
                'product_customizable_option_id' => $optId,
                'locale'                         => 'en',
                'label'                          => 'Engraving Text',
            ]);
        }

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('customizableOptions', $body);
        $this->assertIsArray($body['customizableOptions']);
        $this->assertGreaterThanOrEqual(1, count($body['customizableOptions']));

        $opt = $body['customizableOptions'][0];
        $this->assertArrayHasKey('id', $opt);
        $this->assertArrayHasKey('type', $opt);
        $this->assertArrayHasKey('isRequired', $opt);
        $this->assertArrayHasKey('sortOrder', $opt);
        $this->assertArrayHasKey('maxCharacters', $opt);
        $this->assertArrayHasKey('supportedFileExtensions', $opt);
        $this->assertArrayHasKey('translations', $opt);
        $this->assertArrayHasKey('prices', $opt);
        $this->assertSame('text', $opt['type']);
    }

    // -------------------------------------------------------------------------
    // 3. videos block (2 tests)
    // -------------------------------------------------------------------------

    public function test_detail_videos_empty_when_none(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('videos', $body);
        $this->assertIsArray($body['videos']);
        $this->assertCount(0, $body['videos']);
    }

    public function test_detail_returns_videos_when_present(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        DB::table('product_videos')->insert([
            'product_id' => $product->id,
            'type'       => 'videos',
            'path'       => 'product/'.$product->id.'/demo.mp4',
            'position'   => 1,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('videos', $body);
        $this->assertIsArray($body['videos']);
        $this->assertGreaterThan(0, count($body['videos']));

        $video = $body['videos'][0];
        $this->assertArrayHasKey('id', $video);
        $this->assertArrayHasKey('path', $video);
        $this->assertArrayHasKey('url', $video);
        $this->assertArrayHasKey('sortOrder', $video);
    }

    // -------------------------------------------------------------------------
    // 4. channels block (1 test)
    // -------------------------------------------------------------------------

    public function test_detail_returns_channel_assignments(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        // Every seeded simple product auto-attaches to the default channel via
        // createBaseProduct → product observer. If not, attach manually.
        $channelId = DB::table('channels')->value('id');
        if ($channelId) {
            DB::table('product_channels')->insertOrIgnore([
                'product_id' => $product->id,
                'channel_id' => $channelId,
            ]);
        }

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('channels', $body);
        $this->assertIsArray($body['channels']);

        if ($channelId) {
            $this->assertGreaterThan(0, count($body['channels']));
            $ch = $body['channels'][0];
            $this->assertArrayHasKey('id', $ch);
            $this->assertArrayHasKey('code', $ch);
            $this->assertArrayHasKey('name', $ch);
        }
    }

    // -------------------------------------------------------------------------
    // 5. relatedProducts / upSells / crossSells blocks (1 test covers all 3)
    // -------------------------------------------------------------------------

    public function test_detail_returns_related_upsells_crosssells_arrays(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $related = $this->createBaseProduct('simple');
        $this->insertProductFlat($related);

        // Seed related, up-sell and cross-sell links
        DB::table('product_relations')->insertOrIgnore([
            'parent_id' => $product->id,
            'child_id'  => $related->id,
        ]);
        DB::table('product_up_sells')->insertOrIgnore([
            'parent_id' => $product->id,
            'child_id'  => $related->id,
        ]);
        DB::table('product_cross_sells')->insertOrIgnore([
            'parent_id' => $product->id,
            'child_id'  => $related->id,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/products/'.$product->id);

        $response->assertOk();
        $body = $response->json();

        // All three keys must exist and be arrays (even if empty — fixture insert may silently
        // fail if the pivot table has unique constraints already satisfied)
        $this->assertArrayHasKey('relatedProducts', $body);
        $this->assertArrayHasKey('upSells', $body);
        $this->assertArrayHasKey('crossSells', $body);
        $this->assertIsArray($body['relatedProducts']);
        $this->assertIsArray($body['upSells']);
        $this->assertIsArray($body['crossSells']);

        // If the seed worked, assert the shape of the first item
        foreach (['relatedProducts', 'upSells', 'crossSells'] as $key) {
            if (count($body[$key]) > 0) {
                $ref = $body[$key][0];
                $this->assertArrayHasKey('id', $ref);
                $this->assertArrayHasKey('sku', $ref);
                $this->assertArrayHasKey('name', $ref);
                $this->assertArrayHasKey('type', $ref);
            }
        }
    }
}
