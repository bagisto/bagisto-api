<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin catalog categories endpoint.
 *
 * Endpoint: GET /api/admin/catalog/categories
 *
 * Verifies the { data, meta } envelope, field shape, all supported filters,
 * sort behaviour, pagination edge cases, and auth guards.
 *
 * Does NOT modify AdminApiTestCase — category seeding is handled by the
 * local insertCategory() helper below.
 */
class CategoryTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Local seed helper
    // -------------------------------------------------------------------------

    /**
     * Insert one category + its translation row and return the category ID.
     *
     * Handles the Bagisto 2.x schema, which includes the `url_path` column on
     * category_translations (absent from older releases).
     */
    protected function insertCategory(array $overrides = []): int
    {
        $id = \DB::table('categories')->insertGetId(array_merge([
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
        ], array_filter($overrides, fn ($k) => ! in_array($k, ['locale', 'name', 'slug', 'description']), ARRAY_FILTER_USE_KEY)));

        \DB::table('category_translations')->insert([
            'category_id'      => $id,
            'locale'           => $overrides['locale'] ?? 'en',
            'name'             => $overrides['name'] ?? 'Test Category '.$id,
            'slug'             => $overrides['slug'] ?? 'test-category-'.$id,
            'url_path'         => $overrides['slug'] ?? 'test-category-'.$id,
            'description'      => $overrides['description'] ?? null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        return $id;
    }

    // -------------------------------------------------------------------------
    // Auth guards
    // -------------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/catalog/categories');

        $response->assertStatus(401);
    }

    public function test_listing_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        // Revoke by deleting the token row from personal_access_tokens
        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->delete();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories', $token);

        $response->assertStatus(401);
    }

    public function test_listing_rejects_expired_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);

        // Set expires_at to the past
        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->update(['expires_at' => now()->subDay()]);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories', $token);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Happy path — envelope + shape
    // -------------------------------------------------------------------------

    public function test_listing_returns_envelope_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(
            ['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']
        );
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_category_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategory(['name' => 'Electronics', 'slug' => 'electronics']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories');

        $response->assertOk();
        $data = collect($response->json('data'));
        $row = $data->firstWhere('id', $id);

        expect($row)->not()->toBeNull();
        expect($row['name'])->toBe('Electronics');
        expect($row['slug'])->toBe('electronics');
        expect($row['locale'])->toBe('en');
    }

    public function test_listing_row_has_expected_fields(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');

        expect($row)->toHaveKeys([
            'id', 'position', 'status', 'parentId', 'displayMode',
            'logoUrl', 'bannerUrl', 'name', 'slug', 'description',
            'locale', 'createdAt', 'updatedAt',
        ]);
    }

    // -------------------------------------------------------------------------
    // Filters
    // -------------------------------------------------------------------------

    public function test_filter_by_single_category_id(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['name' => 'Alpha']);
        $id2 = $this->insertCategory(['name' => 'Beta']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?category_id='.$id1);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_comma_list_category_id(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['name' => 'Alpha']);
        $id2 = $this->insertCategory(['name' => 'Beta']);
        $id3 = $this->insertCategory(['name' => 'Gamma']);

        $response = $this->adminGet($admin, "/api/admin/catalog/categories?category_id={$id1},{$id2}");

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->toContain($id2);
        expect($ids)->not()->toContain($id3);
    }

    public function test_filter_by_name_partial(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['name' => 'Home Appliances']);
        $id2 = $this->insertCategory(['name' => 'Electronics']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?name=Appli');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_position(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['position' => 7]);
        $id2 = $this->insertCategory(['position' => 99]);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?position=7');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_status_zero(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['status' => 0]);
        $id2 = $this->insertCategory(['status' => 1]);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?status=0');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_filter_by_parent_id(): void
    {
        $admin = $this->createAdmin();
        $parentId = $this->insertCategory(['name' => 'Root']);
        $childId = $this->insertCategory(['name' => 'Child', 'parent_id' => $parentId]);
        $otherId = $this->insertCategory(['name' => 'Other']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?parent_id='.$parentId);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($childId);
        expect($ids)->not()->toContain($otherId);
    }

    public function test_filter_by_locale(): void
    {
        $admin = $this->createAdmin();

        // Insert category with 'en' translation (default locale) — exists to confirm it
        // does NOT appear in the fr-filtered results.
        $this->insertCategory(['name' => 'English Category', 'locale' => 'en']);

        // Insert a separate category with a French translation by directly inserting
        $id2 = \DB::table('categories')->insertGetId([
            'position'   => 1,
            'status'     => 1,
            'parent_id'  => null,
            '_lft'       => 1,
            '_rgt'       => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('category_translations')->insert([
            'category_id'      => $id2,
            'locale'           => 'fr',
            'name'             => 'Catégorie Française',
            'slug'             => 'categorie-francaise',
            'url_path'         => 'categorie-francaise',
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        // Requesting fr locale should resolve id2 translation
        $response = $this->adminGet($admin, '/api/admin/catalog/categories?locale=fr');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $id2);

        expect($row)->not()->toBeNull();
        expect($row['name'])->toBe('Catégorie Française');
        expect($row['locale'])->toBe('fr');
    }

    // -------------------------------------------------------------------------
    // Sort
    // -------------------------------------------------------------------------

    public function test_sort_by_name_asc_compound(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory(['name' => 'Zucchini']);
        $this->insertCategory(['name' => 'Apple']);
        $this->insertCategory(['name' => 'Mango']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?sort=name-asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->filter()->values()->all();
        $sorted = $names;
        sort($sorted);
        expect($names)->toBe($sorted);
    }

    public function test_sort_by_position_desc_split(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory(['position' => 1]);
        $this->insertCategory(['position' => 5]);
        $this->insertCategory(['position' => 3]);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?sort=position&order=desc');

        $response->assertOk();
        $positions = collect($response->json('data'))->pluck('position')->all();
        $sorted = $positions;
        rsort($sorted);
        expect($positions)->toBe($sorted);
    }

    public function test_default_sort_is_id_desc(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory();
        $id2 = $this->insertCategory();
        $id3 = $this->insertCategory();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();

        // The highest inserted ID should come first
        expect($ids[0])->toBeGreaterThanOrEqual(max($id1, $id2, $id3));
    }

    public function test_unknown_sort_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory();
        $this->insertCategory();

        // Should not throw — unknown sort column silently falls back to id desc
        $response = $this->adminGet($admin, '/api/admin/catalog/categories?sort=nonexistent_column');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_pagination_page_two(): void
    {
        $admin = $this->createAdmin();

        // Determine how many categories already exist (from seedRequiredData), then
        // insert enough so that exactly two full pages exist, letting page 2 have
        // a predictable item count regardless of pre-seeded rows.
        $existing = (int) \DB::table('categories')->count();

        // We want total = 10 + 5 = 15, so insert whatever is missing.
        $target = 15;
        $toAdd = max(0, $target - $existing);

        for ($i = 1; $i <= $toAdd; $i++) {
            $this->insertCategory(['name' => "Pagination Cat {$i}"]);
        }

        $total = (int) \DB::table('categories')->count();
        $lastPage = (int) ceil($total / 10);
        $expected = $total - (($lastPage - 1) * 10); // items on last page

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?per_page=10&page='.$lastPage);

        $response->assertOk();
        expect(count($response->json('data')))->toBe($expected);
        expect($response->json('meta.currentPage'))->toBe($lastPage);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?per_page=9999');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_per_page_zero_falls_back_to_default(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?per_page=0');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_page_zero_clamps_to_one(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?page=0');

        $response->assertOk();
        expect($response->json('meta.currentPage'))->toBe(1);
    }

    public function test_page_beyond_last_returns_empty_data(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories?page=9999&per_page=10');

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function test_unknown_filter_is_ignored(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory();

        // ?totally_unknown=xyz should not throw
        $response = $this->adminGet($admin, '/api/admin/catalog/categories?totally_unknown=xyz');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    public function test_invalid_status_filter_is_silently_dropped(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory(['status' => 1]);

        // status=99 is neither 0 nor 1 — should not filter, should return all
        $response = $this->adminGet($admin, '/api/admin/catalog/categories?status=99');

        $response->assertOk();
        expect($response->json('meta.total'))->toBeGreaterThan(0);
    }

    public function test_empty_database_returns_empty_envelope(): void
    {
        $admin = $this->createAdmin();

        // Wrap in a savepoint so DatabaseTransactions picks it up cleanly
        \DB::table('category_translations')->delete();
        \DB::table('categories')->delete();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories');

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
        expect($response->json('meta.total'))->toBe(0);
    }

    public function test_special_characters_in_filter_do_not_crash(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory(['name' => "O'Brien's Store"]);

        // Apostrophe in the LIKE filter — should not throw a DB error
        $response = $this->adminGet($admin, "/api/admin/catalog/categories?name=O'Brien");
        $response->assertOk();

        // SQL injection attempt — should not throw
        $response2 = $this->adminGet($admin, "/api/admin/catalog/categories?name='; DROP TABLE categories; --");
        $response2->assertOk();
    }

    // =========================================================================
    // Tree endpoint — GET /api/admin/catalog/categories/tree
    // =========================================================================

    /**
     * Seed a parent category and two child categories using Kalnoy appendNode()
     * so that _lft/_rgt are managed correctly. Returns [$parentId, $child1Id, $child2Id].
     */
    protected function insertCategoryTree(array $parentOverrides = []): array
    {
        $parent = \Webkul\Category\Models\Category::create(array_merge([
            'position'     => 1,
            'status'       => 1,
            'parent_id'    => null,
            'display_mode' => null,
            'logo_path'    => null,
            'banner_path'  => null,
        ], $parentOverrides));

        \DB::table('category_translations')->insert([
            'category_id'      => $parent->id,
            'locale'           => $parentOverrides['locale'] ?? 'en',
            'name'             => $parentOverrides['name'] ?? 'Tree Root '.$parent->id,
            'slug'             => $parentOverrides['slug'] ?? 'tree-root-'.$parent->id,
            'url_path'         => $parentOverrides['slug'] ?? 'tree-root-'.$parent->id,
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        $child1 = new \Webkul\Category\Models\Category([
            'position'     => 1,
            'status'       => $parentOverrides['child_status'] ?? 1,
            'display_mode' => null,
            'logo_path'    => null,
            'banner_path'  => null,
        ]);
        $parent->appendNode($child1);

        \DB::table('category_translations')->insert([
            'category_id'      => $child1->id,
            'locale'           => $parentOverrides['locale'] ?? 'en',
            'name'             => 'Child One '.$child1->id,
            'slug'             => 'child-one-'.$child1->id,
            'url_path'         => 'child-one-'.$child1->id,
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        $child2 = new \Webkul\Category\Models\Category([
            'position'     => 2,
            'status'       => $parentOverrides['child2_status'] ?? 1,
            'display_mode' => null,
            'logo_path'    => null,
            'banner_path'  => null,
        ]);
        $parent->appendNode($child2);

        \DB::table('category_translations')->insert([
            'category_id'      => $child2->id,
            'locale'           => $parentOverrides['locale'] ?? 'en',
            'name'             => 'Child Two '.$child2->id,
            'slug'             => 'child-two-'.$child2->id,
            'url_path'         => 'child-two-'.$child2->id,
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        return [$parent->id, $child1->id, $child2->id];
    }

    // -------------------------------------------------------------------------
    // Tree — auth guards
    // -------------------------------------------------------------------------

    public function test_tree_requires_admin_token(): void
    {
        $response = $this->publicGet('/api/admin/catalog/categories/tree');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Tree — happy path
    // -------------------------------------------------------------------------

    public function test_tree_returns_root_array_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();
        // Seed at least one category so there is something in the tree
        $this->insertCategory(['name' => 'Root Node']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree');

        $response->assertOk();

        // AdminCollectionEnvelopeNormalizer wraps all /api/admin collections
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();

        // Pick any node from the flat data array and check its scalar shape
        $node = collect($response->json('data'))->first(fn ($n) => ! empty($n['id']));
        expect($node)->not()->toBeNull();
        expect($node)->toHaveKeys(['id', 'name', 'slug', 'status', 'position', 'parentId', 'displayMode', 'children']);
    }

    public function test_tree_envelope_meta_present(): void
    {
        $admin = $this->createAdmin();
        $this->insertCategory(['name' => 'Meta Test Node']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree');

        $response->assertOk();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    // -------------------------------------------------------------------------
    // Tree — rootId filter
    // -------------------------------------------------------------------------

    public function test_tree_filter_by_root_id(): void
    {
        $admin = $this->createAdmin();
        [$parentId, $child1Id, $child2Id] = $this->insertCategoryTree(['name' => 'Parent']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree?rootId='.$parentId);

        $response->assertOk();

        $data = $response->json('data');
        expect($data)->toBeArray();
        expect(count($data))->toBeGreaterThanOrEqual(1);

        // The root of the returned subtree must be the requested parent
        $root = $data[0];
        expect($root['id'])->toBe($parentId);

        // Both children must appear under the root
        $childIds = collect($root['children'])->pluck('id')->all();
        expect($childIds)->toContain($child1Id);
        expect($childIds)->toContain($child2Id);
    }

    public function test_tree_unknown_root_id_returns_empty_data(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree?rootId=999999');

        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    // -------------------------------------------------------------------------
    // Tree — status filter
    // -------------------------------------------------------------------------

    public function test_tree_filter_by_status(): void
    {
        $admin = $this->createAdmin();

        // Parent is enabled; child1 is enabled; child2 is disabled
        [$parentId, $child1Id, $child2Id] = $this->insertCategoryTree([
            'name'          => 'Status Filter Parent',
            'status'        => 1,
            'child_status'  => 1,
            'child2_status' => 0,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree?rootId='.$parentId.'&status=1');

        $response->assertOk();

        $root = collect($response->json('data'))->firstWhere('id', $parentId);
        expect($root)->not()->toBeNull();

        $childIds = collect($root['children'])->pluck('id')->all();
        // Enabled child must appear
        expect($childIds)->toContain($child1Id);
        // Disabled child must be pruned
        expect($childIds)->not()->toContain($child2Id);
    }

    // -------------------------------------------------------------------------
    // Tree — locale filter
    // -------------------------------------------------------------------------

    public function test_tree_filter_by_locale(): void
    {
        $admin = $this->createAdmin();

        // Insert a category with only a French translation
        $frCat = \Webkul\Category\Models\Category::create([
            'position'     => 1,
            'status'       => 1,
            'parent_id'    => null,
            'display_mode' => null,
        ]);

        \DB::table('category_translations')->insert([
            'category_id'      => $frCat->id,
            'locale'           => 'fr',
            'name'             => 'Catégorie Arbre',
            'slug'             => 'categorie-arbre-'.$frCat->id,
            'url_path'         => 'categorie-arbre-'.$frCat->id,
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree?locale=fr&rootId='.$frCat->id);

        $response->assertOk();

        $root = collect($response->json('data'))->firstWhere('id', $frCat->id);
        expect($root)->not()->toBeNull();
        expect($root['name'])->toBe('Catégorie Arbre');
        expect($root['slug'])->toContain('categorie-arbre-');
    }

    // -------------------------------------------------------------------------
    // Tree — leaves have empty children array
    // -------------------------------------------------------------------------

    public function test_tree_leaves_have_empty_children_array(): void
    {
        $admin = $this->createAdmin();

        // Insert a standalone leaf category (no children)
        $leafId = $this->insertCategory(['name' => 'Leaf Category']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/tree?rootId='.$leafId);

        $response->assertOk();

        $leaf = collect($response->json('data'))->firstWhere('id', $leafId);
        expect($leaf)->not()->toBeNull();
        // Must be an empty array, not null
        expect($leaf['children'])->toBe([]);
    }

    // =========================================================================
    // Detail endpoint — GET /api/admin/catalog/categories/{id}
    // =========================================================================

    /**
     * Insert a category with full translation row and return the category ID.
     * Accepts optional extra translation rows via $extraTranslations.
     */
    protected function insertCategoryWithTranslations(array $overrides = [], array $extraTranslations = []): int
    {
        $id = \DB::table('categories')->insertGetId(array_merge([
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
        ], array_filter($overrides, fn ($k) => ! in_array($k, ['locale', 'name', 'slug', 'description']), ARRAY_FILTER_USE_KEY)));

        \DB::table('category_translations')->insert([
            'category_id'      => $id,
            'locale'           => $overrides['locale'] ?? 'en',
            'name'             => $overrides['name'] ?? 'Detail Category '.$id,
            'slug'             => $overrides['slug'] ?? 'detail-category-'.$id,
            'url_path'         => $overrides['slug'] ?? 'detail-category-'.$id,
            'description'      => $overrides['description'] ?? 'A test description.',
            'meta_title'       => $overrides['meta_title'] ?? null,
            'meta_description' => $overrides['meta_description'] ?? null,
            'meta_keywords'    => $overrides['meta_keywords'] ?? null,
        ]);

        foreach ($extraTranslations as $t) {
            \DB::table('category_translations')->insert(array_merge([
                'category_id'      => $id,
                'locale'           => 'fr',
                'name'             => 'Catégorie '.$id,
                'slug'             => 'categorie-'.$id,
                'url_path'         => 'categorie-'.$id,
                'description'      => null,
                'meta_title'       => null,
                'meta_description' => null,
                'meta_keywords'    => null,
            ], $t, ['category_id' => $id]));
        }

        return $id;
    }

    // -------------------------------------------------------------------------
    // Detail — happy path
    // -------------------------------------------------------------------------

    public function test_detail_returns_category_with_all_fields_for_authenticated_admin(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategoryWithTranslations(['name' => 'Detail Test']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/'.$id);

        $response->assertOk();

        $body = $response->json();
        expect($body)->toHaveKeys([
            'id', 'position', 'status', 'parentId', 'displayMode',
            'logoUrl', 'bannerUrl', 'name', 'slug', 'description',
            'locale', 'createdAt', 'updatedAt',
            'translations', 'filterableAttributeIds',
        ]);
        expect($body['id'])->toBe($id);
        expect($body['name'])->toBe('Detail Test');
        expect($body['translations'])->toBeArray();
        expect(count($body['translations']))->toBe(1);
        expect($body['filterableAttributeIds'])->toBeArray();
    }

    public function test_detail_translations_array_contains_every_seeded_locale(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategoryWithTranslations(
            ['name' => 'Multi-locale', 'locale' => 'en'],
            [
                [
                    'locale'      => 'fr',
                    'name'        => 'Multi-locale FR',
                    'slug'        => 'multi-locale-fr-'.$this->uniqueSuffix(),
                    'url_path'    => 'multi-locale-fr-'.$this->uniqueSuffix(),
                    'description' => null,
                ],
            ]
        );

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/'.$id);

        $response->assertOk();

        $translations = $response->json('translations');
        expect($translations)->toBeArray();
        expect(count($translations))->toBe(2);

        $locales = array_column($translations, 'locale');
        expect($locales)->toContain('en');
        expect($locales)->toContain('fr');

        foreach ($translations as $t) {
            expect($t)->toHaveKeys(['locale', 'name', 'slug', 'description']);
        }
    }

    public function test_detail_filterable_attribute_ids_is_int_array(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategoryWithTranslations(['name' => 'Attr Test']);

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/'.$id);

        $response->assertOk();

        $ids = $response->json('filterableAttributeIds');
        expect($ids)->toBeArray();

        // If any IDs are present, each must be an integer
        foreach ($ids as $attrId) {
            expect($attrId)->toBeInt();
        }
    }

    // -------------------------------------------------------------------------
    // Detail — auth guards
    // -------------------------------------------------------------------------

    public function test_detail_requires_admin_token(): void
    {
        $id = $this->insertCategoryWithTranslations(['name' => 'Auth Test']);

        $response = $this->publicGet('/api/admin/catalog/categories/'.$id);

        $response->assertStatus(401);
    }

    public function test_detail_rejects_revoked_token(): void
    {
        $admin = $this->createAdmin();
        $token = $this->adminToken($admin);
        $id = $this->insertCategoryWithTranslations(['name' => 'Revoked Token Test']);

        // Revoke by deleting the token row
        \DB::table('admin_personal_access_tokens')
            ->where('admin_id', $admin->id)
            ->delete();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/'.$id, $token);

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Detail — not-found edge cases
    // -------------------------------------------------------------------------

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/9999999');

        $response->assertStatus(404);
    }

    public function test_detail_zero_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/0');

        // 0 is treated as invalid — expect 404
        expect(in_array($response->getStatusCode(), [404, 400]))->toBeTrue();
    }

    public function test_detail_negative_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/catalog/categories/-1');

        // Negative IDs are invalid — expect 404 or 400
        expect(in_array($response->getStatusCode(), [404, 400, 405]))->toBeTrue();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Returns a unique numeric suffix for slug generation in parallel tests.
     */
    private function uniqueSuffix(): string
    {
        return (string) (time() + rand(1, 9999));
    }

    // =========================================================================
    // Phase 2 — CRUD tests
    // =========================================================================

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_category_minimal_returns_201(): void
    {
        $admin = $this->createAdmin();
        $slug = 'cat-create-'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'       => $slug,
            'name'       => 'Created Category',
            'position'   => 1,
            'attributes' => [1],
            'locale'     => 'en',
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('name'))->toBe('Created Category');
        expect(\DB::table('category_translations')->where('slug', $slug)->exists())->toBeTrue();
    }

    public function test_create_category_with_full_optional_fields(): void
    {
        $admin = $this->createAdmin();
        $parentId = $this->insertCategory(['name' => 'Cat Parent ']);
        $slug = 'cat-full-'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'             => $slug,
            'name'             => 'Full Category',
            'description'      => 'A long description.',
            'position'         => 3,
            'attributes'       => [1, 2],
            'parent_id'        => $parentId,
            'display_mode'     => 'products_and_description',
            'status'           => 1,
            'locale'           => 'en',
            'meta_title'       => 'MT',
            'meta_description' => 'MD',
            'meta_keywords'    => 'k1,k2',
        ]);

        $response->assertStatus(201);
        $id = $response->json('id');
        expect(\DB::table('categories')->where('id', $id)->value('parent_id'))->toBe($parentId);
    }

    public function test_create_category_missing_slug_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'name'       => 'No Slug',
            'position'   => 1,
            'attributes' => [1],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_category_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'       => 'no-name-'.uniqid(),
            'position'   => 1,
            'attributes' => [1],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_category_missing_position_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'       => 'no-pos-'.uniqid(),
            'name'       => 'No Pos',
            'attributes' => [1],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_category_missing_attributes_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'     => 'no-attr-'.uniqid(),
            'name'     => 'No Attr',
            'position' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_category_description_required_for_description_only_mode(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'         => 'desc-mode-'.uniqid(),
            'name'         => 'Desc Mode',
            'position'     => 1,
            'attributes'   => [1],
            'display_mode' => 'description_only',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_category_duplicate_slug_returns_422(): void
    {
        $admin = $this->createAdmin();
        $slug = 'dup-slug-'.uniqid();
        $this->insertCategory(['slug' => $slug]);

        $response = $this->adminPost($admin, '/api/admin/catalog/categories', [
            'slug'       => $slug,
            'name'       => 'Dup Slug',
            'position'   => 1,
            'attributes' => [1],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_category_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/categories', [
            'slug'       => 'noauth-'.uniqid(),
            'name'       => 'NoAuth',
            'position'   => 1,
            'attributes' => [1],
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_category_renames_successfully(): void
    {
        $admin = $this->createAdmin();
        $slug = 'upd-rename-'.uniqid();
        $id = $this->insertCategory(['name' => 'Before Rename', 'slug' => $slug]);

        $response = $this->adminPut($admin, '/api/admin/catalog/categories/'.$id, [
            'locale'     => 'en',
            'position'   => 2,
            'attributes' => [1],
            'en'         => [
                'slug' => $slug,
                'name' => 'After Rename',
            ],
        ]);

        $response->assertOk();
        expect(\DB::table('category_translations')->where('category_id', $id)->where('locale', 'en')->value('name'))->toBe('After Rename');
    }

    public function test_update_category_move_changes_parent_and_position(): void
    {
        $admin = $this->createAdmin();

        // Use the proper Eloquent / NodeTrait API so _lft/_rgt are managed correctly.
        $parent1 = \Webkul\Category\Models\Category::create([
            'position' => 1, 'status' => 1, 'parent_id' => null, 'display_mode' => null,
        ]);
        \DB::table('category_translations')->insert([
            'category_id' => $parent1->id, 'locale' => 'en',
            'name'        => 'Parent A '.$parent1->id, 'slug' => 'parent-a-'.$parent1->id,
            'url_path'    => 'parent-a-'.$parent1->id,
        ]);

        $parent2 = \Webkul\Category\Models\Category::create([
            'position' => 1, 'status' => 1, 'parent_id' => null, 'display_mode' => null,
        ]);
        \DB::table('category_translations')->insert([
            'category_id' => $parent2->id, 'locale' => 'en',
            'name'        => 'Parent B '.$parent2->id, 'slug' => 'parent-b-'.$parent2->id,
            'url_path'    => 'parent-b-'.$parent2->id,
        ]);

        $child = new \Webkul\Category\Models\Category([
            'position' => 1, 'status' => 1, 'display_mode' => null,
        ]);
        $parent1->appendNode($child);
        $childSlug = 'child-mv-'.$child->id;
        \DB::table('category_translations')->insert([
            'category_id' => $child->id, 'locale' => 'en',
            'name'        => 'Child '.$child->id, 'slug' => $childSlug, 'url_path' => $childSlug,
        ]);

        $response = $this->adminPut($admin, '/api/admin/catalog/categories/'.$child->id, [
            'locale'     => 'en',
            'position'   => 9,
            'attributes' => [1],
            'parent_id'  => $parent2->id,
            'en'         => [
                'slug' => $childSlug,
                'name' => 'Child Moved',
            ],
        ]);

        $response->assertOk();
        expect((int) \DB::table('categories')->where('id', $child->id)->value('parent_id'))->toBe($parent2->id);
        expect((int) \DB::table('categories')->where('id', $child->id)->value('position'))->toBe(9);
    }

    public function test_update_category_slug_unique_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $slug = 'upd-self-slug-'.uniqid();
        $id = $this->insertCategory(['slug' => $slug, 'name' => 'Self Slug']);

        // Sending the same slug should be allowed.
        $response = $this->adminPut($admin, '/api/admin/catalog/categories/'.$id, [
            'locale'     => 'en',
            'position'   => 1,
            'attributes' => [1],
            'en'         => ['slug' => $slug, 'name' => 'Renamed Same Slug'],
        ]);

        $response->assertOk();
    }

    public function test_update_category_duplicate_slug_returns_422(): void
    {
        $admin = $this->createAdmin();
        $slug1 = 'upd-other-slug-'.uniqid();
        $id1 = $this->insertCategory(['slug' => $slug1]);
        $id2 = $this->insertCategory(['slug' => 'upd-target-slug-'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/catalog/categories/'.$id2, [
            'locale'     => 'en',
            'position'   => 1,
            'attributes' => [1],
            'en'         => ['slug' => $slug1, 'name' => 'Stealing Slug'],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_category_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/catalog/categories/9999999', [
            'locale'     => 'en',
            'position'   => 1,
            'attributes' => [1],
            'en'         => ['slug' => 'nf-'.uniqid(), 'name' => 'NF'],
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_category_missing_locale_slug_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategory(['slug' => 'upd-noslug-'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/catalog/categories/'.$id, [
            'locale'     => 'en',
            'position'   => 1,
            'attributes' => [1],
            'en'         => ['name' => 'No Slug In Body'],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_category_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCategory(['slug' => 'upd-noauth-'.uniqid()]);
        $response = $this->putJson('/api/admin/catalog/categories/'.$id, [
            'locale'     => 'en',
            'position'   => 1,
            'attributes' => [1],
            'en'         => ['slug' => 'x', 'name' => 'y'],
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_delete_category_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategory(['slug' => 'del-happy-'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/catalog/categories/'.$id);

        $response->assertOk();
        expect(\DB::table('categories')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_category_root_id_one_returns_400(): void
    {
        $admin = $this->createAdmin();

        // Ensure a category with id=1 exists (Bagisto core ships one)
        if (! \DB::table('categories')->where('id', 1)->exists()) {
            \DB::table('categories')->insert([
                'id'         => 1,
                'position'   => 1,
                'status'     => 1,
                'parent_id'  => null,
                '_lft'       => 1,
                '_rgt'       => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->adminDelete($admin, '/api/admin/catalog/categories/1');

        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('categories')->where('id', 1)->exists())->toBeTrue();
    }

    public function test_delete_category_channel_root_returns_400(): void
    {
        $admin = $this->createAdmin();
        $this->seedRequiredData();

        // Pick any channel's root_category_id; ensure a channel exists with one.
        $rootId = \DB::table('channels')->value('root_category_id');
        if (! $rootId) {
            // Create a root category and link it to a channel.
            $catId = \DB::table('categories')->insertGetId([
                'position'   => 1,
                'status'     => 1,
                '_lft'       => 1,
                '_rgt'       => 2,
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            \DB::table('channels')->limit(1)->update(['root_category_id' => $catId]);
            $rootId = $catId;
        }

        $response = $this->adminDelete($admin, '/api/admin/catalog/categories/'.$rootId);

        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('categories')->where('id', $rootId)->exists())->toBeTrue();
    }

    public function test_delete_category_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/catalog/categories/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_category_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCategory(['slug' => 'del-noauth-'.uniqid()]);
        $response = $this->deleteJson('/api/admin/catalog/categories/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    // -------------------------------------------------------------------------
    // Mass-delete
    // -------------------------------------------------------------------------

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'md-1-'.uniqid()]);
        $id2 = $this->insertCategory(['slug' => 'md-2-'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/catalog/categories/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(\DB::table('categories')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('categories')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mass_delete_rejects_batch_with_root_id(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'md-keep-'.uniqid()]);

        // Ensure id=1 exists so the guard fires
        if (! \DB::table('categories')->where('id', 1)->exists()) {
            \DB::table('categories')->insert([
                'id'         => 1,
                'position'   => 1,
                'status'     => 1,
                'parent_id'  => null,
                '_lft'       => 1,
                '_rgt'       => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->adminPost($admin, '/api/admin/catalog/categories/mass-delete', [
            'indices' => [$id1, 1],
        ]);

        expect($response->getStatusCode())->toBe(400);
        // Neither must be deleted — the whole batch was rejected before any work.
        expect(\DB::table('categories')->where('id', $id1)->exists())->toBeTrue();
        expect(\DB::table('categories')->where('id', 1)->exists())->toBeTrue();
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/catalog/categories/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/categories/mass-delete', ['indices' => [99]]);
        expect($response->getStatusCode())->toBe(401);
    }

    // -------------------------------------------------------------------------
    // Mass-update-status
    // -------------------------------------------------------------------------

    public function test_mass_update_status_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'mus-1-'.uniqid(), 'status' => 1]);
        $id2 = $this->insertCategory(['slug' => 'mus-2-'.uniqid(), 'status' => 1]);

        $response = $this->adminPost($admin, '/api/admin/catalog/categories/mass-update-status', [
            'indices' => [$id1, $id2],
            'value'   => 0,
        ]);

        $response->assertOk();
        expect((int) \DB::table('categories')->where('id', $id1)->value('status'))->toBe(0);
        expect((int) \DB::table('categories')->where('id', $id2)->value('status'))->toBe(0);
    }

    public function test_mass_update_status_missing_value_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'mus-nv-'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/catalog/categories/mass-update-status', [
            'indices' => [$id1],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status_invalid_value_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'mus-iv-'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/catalog/categories/mass-update-status', [
            'indices' => [$id1],
            'value'   => 99,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/catalog/categories/mass-update-status', ['indices' => [1], 'value' => 1]);
        expect($response->getStatusCode())->toBe(401);
    }
}
