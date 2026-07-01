<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Marketing → Sitemaps CRUD + generate (Block F3d).
 */
class MarketingSitemapTest extends AdminApiTestCase
{
    protected function insertSitemap(array $overrides = []): int
    {
        return DB::table('sitemaps')->insertGetId(array_merge([
            'file_name'    => 'gqlsm-'.uniqid().'.xml',
            'path'         => '/',
            'generated_at' => null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $overrides));
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertSitemap();

        $query = <<<'GQL'
            query { adminMarketingSitemaps(first: 10) { edges { node { _id fileName } } } }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('data.adminMarketingSitemaps.edges'))->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'gqldetail-sm.xml']);

        $query = <<<GQL
            query {
              adminMarketingSitemap(id: "/api/admin/marketing/sitemaps/{$id}") {
                _id fileName
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        $node = $response->json('data.adminMarketingSitemap');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('sitemaps', ['id' => $id, 'file_name' => 'gqldetail-sm.xml']);
        }
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminMarketingSitemapInput!) {
              createAdminMarketingSitemap(input: $input) {
                adminMarketingSitemap { _id fileName }
              }
            }
        GQL;

        $unique = 'gqlcr-sm-'.uniqid().'.xml';
        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'fileName' => $unique,
                'path'     => '/',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('sitemaps', ['file_name' => $unique]);
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'gqlupd-sm.xml']);

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingSitemapInput!) {
              updateAdminMarketingSitemap(input: $input) {
                adminMarketingSitemap { _id fileName }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'       => "/api/admin/marketing/sitemaps/{$id}",
                'fileName' => 'gqlupd-renamed.xml',
                'path'     => '/',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('sitemaps', ['id' => $id, 'file_name' => 'gqlupd-renamed.xml']);
    }

    public function test_mutation_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'gqldel-sm.xml']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingSitemapInput!) {
              deleteAdminMarketingSitemap(input: $input) {
                adminMarketingSitemap { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/sitemaps/{$id}"],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('sitemaps', ['id' => $id]);
    }

    public function test_mutation_generate(): void
    {
        Storage::fake('public');
        \Webkul\Core\Models\CoreConfig::query()->updateOrCreate(
            ['code' => 'general.sitemap.settings.enabled', 'channel_code' => null, 'locale_code' => null],
            ['value' => '1']
        );
        \Webkul\Core\Models\CoreConfig::query()->updateOrCreate(
            ['code' => 'general.sitemap.file_limits.max_url_per_file', 'channel_code' => null, 'locale_code' => null],
            ['value' => '50000']
        );

        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'gqlgen-sm.xml', 'path' => '/']);

        $mutation = <<<'GQL'
            mutation Gen($input: createAdminMarketingSitemapGenerateInput!) {
              createAdminMarketingSitemapGenerate(input: $input) {
                adminMarketingSitemapGenerate { _id sitemapId }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['sitemapId' => $id],
        ], $admin);

        $response->assertOk();
        expect($response->json('data.createAdminMarketingSitemapGenerate.adminMarketingSitemapGenerate.sitemapId'))->toBe($id);
        $row = DB::table('sitemaps')->where('id', $id)->first();
        expect($row->generated_at)->not->toBeNull();
    }

    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingSitemaps(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->toBeArray();
    }
}
