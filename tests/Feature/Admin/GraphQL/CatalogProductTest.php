<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

/**
 * GraphQL coverage for the adminCatalogProducts query (cursor pagination).
 *
 * These tests verify:
 *   1. The query executes and returns the correct cursor-paginated shape.
 *   2. Seeded products appear in the response (full listing, client-side check).
 *   3. Field shapes: id (IRI), sku, name, type, status are all populated.
 *   4. Authentication is enforced.
 *   5. Filter args (sku, type, status) work as GraphQL variables — the provider
 *      reads them from $context['args'] which API Platform populates from the
 *      query variables object.
 */
class CatalogProductTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    private const LIST_QUERY = <<<'GQL'
        query AdminCatalogProducts($first: Int, $after: String) {
            adminCatalogProducts(first: $first, after: $after) {
                edges {
                    node {
                        id
                        sku
                        name
                        type
                        status
                    }
                }
                pageInfo { hasNextPage endCursor }
                totalCount
            }
        }
    GQL;

    public function test_query_returns_seeded_product(): void
    {
        $admin = $this->createAdmin();
        $sku = 'GQL-CAT-SEED-'.uniqid();
        $product = $this->createBaseProduct('simple', ['sku' => $sku]);
        $this->insertProductFlat($product, ['sku' => $sku, 'name' => 'GQL Seed Product']);

        $response = $this->adminGraphQL(self::LIST_QUERY, ['first' => 50], $admin);
        $response->assertOk();
        $this->assertNull($response->json('errors'));

        $skus = collect($response->json('data.adminCatalogProducts.edges'))
            ->pluck('node.sku')
            ->all();
        $this->assertContains($sku, $skus);
    }

    public function test_query_returns_correct_field_shape(): void
    {
        $admin = $this->createAdmin();
        $sku = 'GQL-CAT-SHAPE-'.uniqid();
        $product = $this->createBaseProduct('simple', ['sku' => $sku]);
        $this->insertProductFlat($product, ['sku' => $sku, 'name' => 'Shape Test']);

        $response = $this->adminGraphQL(self::LIST_QUERY, ['first' => 50], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminCatalogProducts.edges');
        $node = collect($edges)->firstWhere('node.sku', $sku)['node'] ?? null;
        $this->assertNotNull($node, 'Seeded product must appear in edges.');

        $this->assertArrayHasKey('id', $node);
        $this->assertStringContainsString('catalog/products/', $node['id']);
        $this->assertSame($sku, $node['sku']);
        $this->assertSame('Shape Test', $node['name']);
        $this->assertSame('simple', $node['type']);
        $this->assertSame(1, $node['status']);
    }

    public function test_query_returns_pagination_metadata(): void
    {
        $admin = $this->createAdmin();
        $p = $this->createBaseProduct('simple');
        $this->insertProductFlat($p);

        $response = $this->adminGraphQL(self::LIST_QUERY, ['first' => 1], $admin);
        $response->assertOk();

        $data = $response->json('data.adminCatalogProducts');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertGreaterThanOrEqual(1, $data['totalCount']);
    }

    public function test_query_disabled_products_are_included(): void
    {
        $admin = $this->createAdmin();
        $sku = 'GQL-CAT-DISABLED-'.uniqid();
        $product = $this->createBaseProduct('simple', ['sku' => $sku]);
        $this->insertProductFlat($product, ['sku' => $sku, 'status' => 0]);

        $response = $this->adminGraphQL(self::LIST_QUERY, ['first' => 50], $admin);
        $response->assertOk();

        $found = collect($response->json('data.adminCatalogProducts.edges'))
            ->firstWhere('node.sku', $sku);
        $this->assertNotNull($found, 'Disabled product must appear in admin catalog listing.');
        $this->assertSame(0, $found['node']['status']);
    }

    public function test_query_requires_token(): void
    {
        $response = $this->postJson('/api/graphql', [
            'query'     => self::LIST_QUERY,
            'variables' => ['first' => 5],
        ]);

        $this->assertNotEmpty($response->json('errors'));
    }

    public function test_query_filter_by_sku(): void
    {
        $admin = $this->createAdmin();
        $hit = $this->createBaseProduct('simple', ['sku' => 'GQL-CAT-HIT-XX']);
        $miss = $this->createBaseProduct('simple', ['sku' => 'GQL-CAT-OTHER-XX']);
        $this->insertProductFlat($hit, ['sku' => 'GQL-CAT-HIT-XX']);
        $this->insertProductFlat($miss, ['sku' => 'GQL-CAT-OTHER-XX']);

        $query = <<<'GQL'
            query AdminCatalogProducts($first: Int!, $sku: String) {
                adminCatalogProducts(first: $first, sku: $sku) {
                    edges { node { _id sku } }
                }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['first' => 50, 'sku' => 'GQL-CAT-HIT-XX'], $admin);
        $response->assertOk();

        $ids = collect($response->json('data.adminCatalogProducts.edges'))->pluck('node._id')->all();
        $this->assertContains($hit->id, $ids);
        $this->assertNotContains($miss->id, $ids);
    }

    public function test_query_filter_by_type(): void
    {
        $admin = $this->createAdmin();
        $simple = $this->createBaseProduct('simple');
        $configurable = $this->createBaseProduct('configurable');
        $this->insertProductFlat($simple, ['type' => 'simple']);
        $this->insertProductFlat($configurable, ['type' => 'configurable']);

        $query = <<<'GQL'
            query AdminCatalogProducts($first: Int!, $type: String) {
                adminCatalogProducts(first: $first, type: $type) {
                    edges { node { _id type } }
                }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['first' => 50, 'type' => 'configurable'], $admin);
        $response->assertOk();

        $ids = collect($response->json('data.adminCatalogProducts.edges'))->pluck('node._id')->all();
        $this->assertContains($configurable->id, $ids);
        $this->assertNotContains($simple->id, $ids);
    }

    public function test_query_filter_by_status_via_arg(): void
    {
        $admin = $this->createAdmin();
        $enabled = $this->createBaseProduct('simple');
        $disabled = $this->createBaseProduct('simple');
        $this->insertProductFlat($enabled, ['status' => 1]);
        $this->insertProductFlat($disabled, ['status' => 0]);

        $query = <<<'GQL'
            query AdminCatalogProducts($first: Int!, $status: Int) {
                adminCatalogProducts(first: $first, status: $status) {
                    edges { node { _id status } }
                }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['first' => 50, 'status' => 0], $admin);
        $response->assertOk();

        $ids = collect($response->json('data.adminCatalogProducts.edges'))->pluck('node._id')->all();
        $this->assertContains($disabled->id, $ids);
        $this->assertNotContains($enabled->id, $ids);
    }

    public function test_mutation_mass_delete_products_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductMassDeleteInput!) {
              createAdminCatalogProductMassDelete(input: $input) {
                adminCatalogProductMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$a->id, $b->id]],
        ], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertFalse(\DB::table('products')->where('id', $a->id)->exists());
        $this->assertFalse(\DB::table('products')->where('id', $b->id)->exists());
    }

    public function test_mutation_mass_delete_empty_indices_returns_errors(): void
    {
        $admin = $this->createAdmin();
        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductMassDeleteInput!) {
              createAdminCatalogProductMassDelete(input: $input) {
                adminCatalogProductMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => []]], $admin);
        $response->assertOk();
        $this->assertNotNull($response->json('errors'));
    }

    public function test_mutation_mass_update_status_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $this->insertProductFlat($a, ['status' => 1]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductMassUpdateStatusInput!) {
              createAdminCatalogProductMassUpdateStatus(input: $input) {
                adminCatalogProductMassUpdateStatus { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$a->id], 'value' => 0],
        ], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'));
        $this->assertSame(0, (int) \DB::table('product_flat')->where('product_id', $a->id)->value('status'));
    }

    public function test_mutation_mass_update_status_invalid_value_returns_errors(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductMassUpdateStatusInput!) {
              createAdminCatalogProductMassUpdateStatus(input: $input) {
                adminCatalogProductMassUpdateStatus { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$product->id], 'value' => 99],
        ], $admin);

        $response->assertOk();
        $this->assertNotNull($response->json('errors'));
    }

    public function test_mutation_copy_product_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductCopyInput!) {
              createAdminCatalogProductCopy(input: $input) {
                adminCatalogProductCopy { _id sku type success message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['sourceId' => $product->id],
        ], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'));

        $newId = (int) $response->json('data.createAdminCatalogProductCopy.adminCatalogProductCopy._id');
        if ($newId === 0) {
            $count = \DB::table('products')->where('id', '>', $product->id)->count();
            $this->assertGreaterThan(0, $count, 'A new product row should exist after copy.');
        } else {
            $this->assertTrue(\DB::table('products')->where('id', $newId)->exists());
            $this->assertNotSame($product->id, $newId);
        }
    }

    public function test_mutation_copy_refuses_variant(): void
    {
        $admin = $this->createAdmin();
        $parent = $this->createBaseProduct('simple');
        $variant = \Webkul\Product\Models\Product::factory()->create([
            'type'                => 'simple',
            'attribute_family_id' => $parent->attribute_family_id,
            'parent_id'           => $parent->id,
        ]);

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductCopyInput!) {
              createAdminCatalogProductCopy(input: $input) {
                adminCatalogProductCopy { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['sourceId' => $variant->id],
        ], $admin);

        $response->assertOk();
        $this->assertNotNull($response->json('errors'));
    }

    public function test_mutation_create_simple_product_happy_path(): void
    {
        $admin = $this->createAdmin();
        $familyId = (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);
        $sku = 'sp-gql-create-'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductInput!) {
              createAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id sku type }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['sku' => $sku, 'attributeFamilyId' => $familyId],
        ], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'), 'Mutation should succeed: '.json_encode($response->json()));
        $this->assertTrue(\DB::table('products')->where('sku', $sku)->exists(), 'New product row should exist after create.');
    }

    public function test_mutation_create_configurable_missing_super_attributes_errors(): void
    {
        $admin = $this->createAdmin();
        $familyId = (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductInput!) {
              createAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'sku'               => 'sp-gql-config-'.uniqid(),
                'attributeFamilyId' => $familyId,
                'type'              => 'configurable',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertNotNull($response->json('errors'));
    }

    public function test_mutation_create_configurable_happy_path(): void
    {
        $admin = $this->createAdmin();
        $familyId = (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);

        [, , $colorOptions, $sizeOptions] = $this->ensureColorSizeAttributesWithOptions();

        $sku = 'cf-gql-create-'.uniqid();
        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductInput!) {
              createAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id sku type }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'sku'               => $sku,
                'attributeFamilyId' => $familyId,
                'type'              => 'configurable',
                'superAttributes'   => [
                    'color' => $colorOptions,
                    'size'  => $sizeOptions,
                ],
            ],
        ], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'), 'Mutation should succeed: '.json_encode($response->json()));
        $this->assertTrue(\Illuminate\Support\Facades\DB::table('products')->where('sku', $sku)->where('type', 'configurable')->exists());
    }

    public function test_mutation_create_bundle_happy_path(): void
    {
        $admin = $this->createAdmin();
        $familyId = (int) (\Illuminate\Support\Facades\DB::table('attribute_families')->value('id') ?? 1);
        $sku = 'bn-gql-create-'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductInput!) {
              createAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id sku type }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'sku'               => $sku,
                'attributeFamilyId' => $familyId,
                'type'              => 'bundle',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertNull($response->json('errors'), 'Bundle create should succeed: '.json_encode($response->json()));
        $this->assertTrue(\Illuminate\Support\Facades\DB::table('products')->where('sku', $sku)->where('type', 'bundle')->exists());
    }

    /**
     * Copied verbatim from tests/Feature/Admin/RestApi/CatalogProductTest.php.
     * Do NOT move to AdminApiTestCase — shared base class is used by other
     * parallel development windows and must not be modified here.
     */
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

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $this->insertProductFlat($product);

        $newSku = 'gql-upd-'.uniqid();
        $mutation = <<<'GQL'
            mutation($input: updateAdminCatalogProductInput!) {
              updateAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'  => '/api/admin/catalog/products/'.$product->id,
                'sku' => $newSku,
            ],
        ], $admin);

        $response->assertOk();
        $this->assertTrue(\DB::table('products')->where('id', $product->id)->where('sku', $newSku)->exists(), 'Body: '.$response->getContent());
    }

    public function test_mutation_update_duplicate_sku_returns_error(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createBaseProduct('simple');
        $b = $this->createBaseProduct('simple');

        $mutation = <<<'GQL'
            mutation($input: updateAdminCatalogProductInput!) {
              updateAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'  => '/api/admin/catalog/products/'.$a->id,
                'sku' => $b->sku,
            ],
        ], $admin);

        $response->assertOk();
        $this->assertNotNull($response->json('errors'), 'Expected errors[] on duplicate sku.');
    }

    public function test_mutation_update_strips_subresources_and_keeps_them_unchanged(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $imagesBefore = \DB::table('product_images')->where('product_id', $product->id)->count();

        $mutation = <<<'GQL'
            mutation($input: updateAdminCatalogProductInput!) {
              updateAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'     => '/api/admin/catalog/products/'.$product->id,
                'extras' => ['images' => ['ignored.jpg']],
            ],
        ], $admin);

        $response->assertOk();
        $imagesAfter = \DB::table('product_images')->where('product_id', $product->id)->count();
        $this->assertSame($imagesBefore, $imagesAfter, 'Stripped images field must not mutate product_images.');
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $id = $product->id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCatalogProductInput!) {
              deleteAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/catalog/products/'.$id],
        ], $admin);

        $response->assertOk();
        $this->assertFalse(\DB::table('products')->where('id', $id)->exists());
    }

    public function test_mutation_delete_non_existent_returns_error(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCatalogProductInput!) {
              deleteAdminCatalogProduct(input: $input) {
                adminCatalogProduct { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/catalog/products/9999999'],
        ], $admin);

        $response->assertOk();
        $this->assertNotNull($response->json('errors'));
    }
}
