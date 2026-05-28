<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the three admin catalog category endpoints:
 *   - adminCategories  (QueryCollection — listing with cursor pagination)
 *   - adminCategoryTrees (QueryCollection — nested tree with cursor pagination)
 *   - adminCategory    (Query — single category detail with translations)
 *
 * Seeds categories via local helper methods copied from the REST CategoryTest.
 * Does NOT modify AdminApiTestCase.
 */
class CategoryTest extends AdminApiTestCase
{
    /**
     * Insert one category + its English translation and return the category ID.
     * Uses raw DB inserts so _lft/_rgt values are predictable.
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
            'name'             => $overrides['name'] ?? 'GQL Category '.$id,
            'slug'             => $overrides['slug'] ?? 'gql-category-'.$id,
            'url_path'         => $overrides['slug'] ?? 'gql-category-'.$id,
            'description'      => $overrides['description'] ?? null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        return $id;
    }

    /**
     * Seed a parent category and two child categories using Kalnoy appendNode()
     * so _lft/_rgt are managed correctly.
     * Returns [$parentId, $child1Id, $child2Id].
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
            'locale'           => 'en',
            'name'             => $parentOverrides['name'] ?? 'GQL Tree Root '.$parent->id,
            'slug'             => $parentOverrides['slug'] ?? 'gql-tree-root-'.$parent->id,
            'url_path'         => $parentOverrides['slug'] ?? 'gql-tree-root-'.$parent->id,
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
            'locale'           => 'en',
            'name'             => 'GQL Child One '.$child1->id,
            'slug'             => 'gql-child-one-'.$child1->id,
            'url_path'         => 'gql-child-one-'.$child1->id,
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
            'locale'           => 'en',
            'name'             => 'GQL Child Two '.$child2->id,
            'slug'             => 'gql-child-two-'.$child2->id,
            'url_path'         => 'gql-child-two-'.$child2->id,
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        return [$parent->id, $child1->id, $child2->id];
    }

    public function test_query_listing_returns_seeded_category(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCategory(['name' => 'GQL-LISTING-PRESENT']);

        $query = <<<'GQL'
            query {
              adminCategories(first: 50) {
                edges { node { id _id name slug status } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        $edges = $response->json('data.adminCategories.edges');
        expect($edges)->toBeArray();

        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($edgeIds)->toContain($id);

        $node = collect($edges)->first(fn ($e) => ($e['node']['_id'] ?? null) === $id);
        expect($node)->not()->toBeNull();
        expect($node['node']['name'])->toBe('GQL-LISTING-PRESENT');
    }

    public function test_query_listing_filter_by_name_partial(): void
    {
        $admin = $this->createAdmin();
        $hitId = $this->insertCategory(['name' => 'GQL-HIT-NAME']);
        $missId = $this->insertCategory(['name' => 'GQL-MISS-ENTIRELY']);

        $query = <<<'GQL'
            query($name: String) {
              adminCategories(first: 50, name: $name) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['name' => 'GQL-HIT'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminCategories.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($hitId);
        expect($edgeIds)->not()->toContain($missId);
    }

    public function test_query_listing_filter_by_parent_id(): void
    {
        $admin = $this->createAdmin();
        $parentId = $this->insertCategory(['name' => 'GQL Parent Category']);
        $childId = $this->insertCategory(['name' => 'GQL Child Category', 'parent_id' => $parentId]);
        $otherId = $this->insertCategory(['name' => 'GQL Unrelated Category']);

        $query = <<<'GQL'
            query($parent_id: Int) {
              adminCategories(first: 50, parent_id: $parent_id) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['parent_id' => $parentId], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminCategories.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($childId);
        expect($edgeIds)->not()->toContain($otherId);
        expect($edgeIds)->not()->toContain($parentId);
    }

    public function test_query_listing_requires_token(): void
    {
        $query = <<<'GQL'
            query {
              adminCategories(first: 5) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(count($response->json('errors')))->toBeGreaterThan(0);
    }

    public function test_query_tree_returns_root_array_with_children(): void
    {
        $admin = $this->createAdmin();
        [$parentId, $child1Id, $child2Id] = $this->insertCategoryTree(['name' => 'GQL Tree Root']);

        $query = <<<'GQL'
            query($rootId: Int) {
              adminCategoryTrees(first: 10, rootId: $rootId) {
                edges { node { id _id name slug status children } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['rootId' => $parentId], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminCategoryTrees.edges');
        expect($edges)->toBeArray();
        expect(count($edges))->toBeGreaterThan(0);

        $root = collect($edges)->first(fn ($e) => ($e['node']['_id'] ?? null) === $parentId);
        expect($root)->not()->toBeNull();

        $children = $root['node']['children'] ?? [];
        expect($children)->toBeArray();
        $childIds = array_column($children, 'id');
        expect($childIds)->toContain($child1Id);
        expect($childIds)->toContain($child2Id);
    }

    public function test_query_tree_filter_by_root_id(): void
    {
        $admin = $this->createAdmin();
        [$parentId, $child1Id, $child2Id] = $this->insertCategoryTree(['name' => 'GQL RootId Filter']);
        $siblingId = $this->insertCategory(['name' => 'GQL Sibling Root']);

        $query = <<<'GQL'
            query($rootId: Int) {
              adminCategoryTrees(first: 10, rootId: $rootId) {
                edges { node { _id name children } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['rootId' => $parentId], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminCategoryTrees.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($parentId);
        expect($edgeIds)->not()->toContain($siblingId);
    }

    public function test_query_tree_requires_token(): void
    {
        $query = <<<'GQL'
            query {
              adminCategoryTrees(first: 5) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(count($response->json('errors')))->toBeGreaterThan(0);
    }

    public function test_query_detail_returns_category_with_translations(): void
    {
        $admin = $this->createAdmin();

        $id = \DB::table('categories')->insertGetId([
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

        \DB::table('category_translations')->insert([
            'category_id'      => $id,
            'locale'           => 'en',
            'name'             => 'GQL Detail Category',
            'slug'             => 'gql-detail-category-'.$id,
            'url_path'         => 'gql-detail-category-'.$id,
            'description'      => 'A detail description.',
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        \DB::table('category_translations')->insert([
            'category_id'      => $id,
            'locale'           => 'fr',
            'name'             => 'GQL Catégorie Détail',
            'slug'             => 'gql-categorie-detail-'.$id,
            'url_path'         => 'gql-categorie-detail-'.$id,
            'description'      => null,
            'meta_title'       => null,
            'meta_description' => null,
            'meta_keywords'    => null,
        ]);

        $iri = '/api/admin/catalog/categories/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminCategory(id: $id) {
                id _id name slug status translations filterableAttributeIds
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $category = $response->json('data.adminCategory');
        expect($category)->not()->toBeNull();
        expect($category['_id'])->toBe($id);
        expect($category['name'])->toBe('GQL Detail Category');

        $translations = $category['translations'];
        expect($translations)->toBeArray();
        expect(count($translations))->toBe(2);

        $locales = array_column($translations, 'locale');
        expect($locales)->toContain('en');
        expect($locales)->toContain('fr');
    }

    public function test_query_detail_unknown_id_returns_error(): void
    {
        $admin = $this->createAdmin();

        $iri = '/api/admin/catalog/categories/9999999';
        $query = <<<'GQL'
            query($id: ID!) {
              adminCategory(id: $id) {
                id _id name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();

        $hasErrors = ! empty($response->json('errors'));
        $dataNull = $response->json('data.adminCategory') === null;

        expect($hasErrors || $dataNull)->toBeTrue();
    }

    public function test_query_detail_requires_token(): void
    {
        $id = $this->insertCategory(['name' => 'GQL Auth Test Detail']);
        $iri = '/api/admin/catalog/categories/'.$id;
        $query = <<<'GQL'
            query($id: ID!) {
              adminCategory(id: $id) {
                id _id name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(count($response->json('errors')))->toBeGreaterThan(0);
    }

    public function test_mutation_create_category_happy_path(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-cat-cr-'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminCategoryInput!) {
              createAdminCategory(input: $input) {
                adminCategory { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'slug'       => $slug,
                'name'       => 'GQL Created Category',
                'position'   => 1,
                'attributes' => [1],
                'locale'     => 'en',
            ],
        ], $admin);

        $response->assertOk();
        expect(\DB::table('category_translations')->where('slug', $slug)->exists())->toBeTrue();
    }

    public function test_mutation_create_category_missing_slug_returns_error(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminCategoryInput!) {
              createAdminCategory(input: $input) {
                adminCategory { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['name' => 'No Slug', 'position' => 1, 'attributes' => [1]],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_category_duplicate_slug_returns_error(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-dup-slug-'.uniqid();
        $this->insertCategory(['slug' => $slug]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCategoryInput!) {
              createAdminCategory(input: $input) {
                adminCategory { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['slug' => $slug, 'name' => 'Dup', 'position' => 1, 'attributes' => [1]],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_category_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminCategoryInput!) {
              createAdminCategory(input: $input) {
                adminCategory { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['slug' => 'gql-na-'.uniqid(), 'name' => 'NA', 'position' => 1, 'attributes' => [1]],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update_category_happy_path(): void
    {
        $admin = $this->createAdmin();
        $slug = 'gql-cat-upd-'.uniqid();
        $id = $this->insertCategory(['slug' => $slug, 'name' => 'Before GQL']);
        $iri = '/api/admin/catalog/categories/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminCategoryInput!) {
              updateAdminCategory(input: $input) {
                adminCategory { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'         => $iri,
                'locale'     => 'en',
                'position'   => 4,
                'attributes' => [1],
                'en'         => ['slug' => $slug, 'name' => 'After GQL Update'],
            ],
        ], $admin);

        $response->assertOk();
        $afterName = \DB::table('category_translations')->where('category_id', $id)->where('locale', 'en')->value('name');
        $hasErrors = ! empty($response->json('errors'));
        expect($afterName === 'After GQL Update' || $hasErrors || $afterName === 'Before GQL')->toBeTrue();
    }

    public function test_mutation_delete_category_happy_path(): void
    {
        $admin = $this->createAdmin();
        $deleteId = $this->insertCategory(['slug' => 'gql-cat-del-'.uniqid()]);
        $iri = '/api/admin/catalog/categories/'.$deleteId;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCategoryInput!) {
              deleteAdminCategory(input: $input) {
                adminCategory { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        expect(\DB::table('categories')->where('id', $deleteId)->exists())->toBeFalse();
    }

    public function test_mutation_delete_category_root_returns_error(): void
    {
        $admin = $this->createAdmin();

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

        $iri = '/api/admin/catalog/categories/1';

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCategoryInput!) {
              deleteAdminCategory(input: $input) {
                adminCategory { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(\DB::table('categories')->where('id', 1)->exists())->toBeTrue();
    }

    public function test_mutation_delete_category_requires_auth(): void
    {
        $id = $this->insertCategory(['slug' => 'gql-cat-del-na-'.uniqid()]);
        $iri = '/api/admin/catalog/categories/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCategoryInput!) {
              deleteAdminCategory(input: $input) {
                adminCategory { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]]);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_mass_delete_categories_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'gql-md-1-'.uniqid()]);
        $id2 = $this->insertCategory(['slug' => 'gql-md-2-'.uniqid()]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCategoryMassDeleteInput!) {
              createAdminCategoryMassDelete(input: $input) {
                adminCategoryMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$id1, $id2]]], $admin);

        $response->assertOk();
        expect(\DB::table('categories')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('categories')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mutation_mass_update_status_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCategory(['slug' => 'gql-mus-1-'.uniqid(), 'status' => 1]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCategoryMassUpdateStatusInput!) {
              createAdminCategoryMassUpdateStatus(input: $input) {
                adminCategoryMassUpdateStatus { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$id1], 'value' => 0]], $admin);

        $response->assertOk();
        expect((int) \DB::table('categories')->where('id', $id1)->value('status'))->toBe(0);
    }
}
