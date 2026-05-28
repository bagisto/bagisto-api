<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Marketing → Search Synonyms CRUD (Block F3c).
 */
class MarketingSearchSynonymTest extends AdminApiTestCase
{
    protected function insertSynonym(array $overrides = []): int
    {
        return \DB::table('search_synonyms')->insertGetId(array_merge([
            'name'       => 'syn-'.uniqid(),
            'terms'      => 'shirt,tshirt,tee',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'Limited '.uniqid(),
            'description'     => 'no synonym perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name'  => 'API Syn '.uniqid(),
            'terms' => 'foo,bar,baz',
        ], $overrides);
    }

    // --- Listing ------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/marketing/search-synonyms');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertSynonym();

        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertSynonym();

        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'terms']);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym(['name' => 'UniqueSynName-X']);
        $this->insertSynonym(['name' => 'Other Synonym']);

        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms?name=UniqueSynName');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_terms(): void
    {
        $admin = $this->createAdmin();
        $hit = $this->insertSynonym(['terms' => 'banana,plantain,bnna']);
        $miss = $this->insertSynonym(['terms' => 'apple,pear,fruit']);

        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms?terms=plantain');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($hit);
        expect($ids)->not->toContain($miss);
    }

    public function test_sort_by_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertSynonym(['name' => 'zzz-srt-syn']);
        $this->insertSynonym(['name' => 'aaa-srt-syn']);

        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms?sort=name&order=asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $idxA = array_search('aaa-srt-syn', $names, true);
        $idxZ = array_search('zzz-srt-syn', $names, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // --- Detail -------------------------------------------------------------

    public function test_detail_returns_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym(['name' => 'Detail Syn', 'terms' => 'a,b,c']);

        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('Detail Syn');
        expect($response->json('terms'))->toBe('a,b,c');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/search-synonyms/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertSynonym();
        $response = $this->publicGet('/api/admin/marketing/search-synonyms/'.$id);
        $response->assertStatus(401);
    }

    // --- Create -------------------------------------------------------------

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms', $this->basePayload([
            'name'  => 'Created Synonym',
            'terms' => 'one,two,three',
        ]));

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('search_synonyms', ['id' => $id, 'name' => 'Created Synonym']);
        expect($response->json('terms'))->toBe('one,two,three');
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['name']);
        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_terms_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['terms']);
        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms', $payload);
        $response->assertStatus(422);
    }

    public function test_create_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/marketing/search-synonyms', $this->basePayload());
        $response->assertStatus(401);
    }

    public function test_create_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms', $this->basePayload());
        $response->assertStatus(403);
    }

    // --- Update -------------------------------------------------------------

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym(['name' => 'old-name', 'terms' => 'old,terms']);

        $response = $this->adminPut($admin, '/api/admin/marketing/search-synonyms/'.$id, [
            'name'  => 'updated-name',
            'terms' => 'new,terms,here',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('search_synonyms', ['id' => $id, 'name' => 'updated-name', 'terms' => 'new,terms,here']);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/marketing/search-synonyms/999999', [
            'name'  => 'x',
            'terms' => 'y',
        ]);
        $response->assertStatus(404);
    }

    public function test_update_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym();
        $response = $this->adminPut($admin, '/api/admin/marketing/search-synonyms/'.$id, [
            'terms' => 'just,terms',
        ]);
        $response->assertStatus(422);
    }

    public function test_update_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertSynonym();
        $response = $this->adminPut($admin, '/api/admin/marketing/search-synonyms/'.$id, [
            'name'  => 'x',
            'terms' => 'y',
        ]);
        $response->assertStatus(403);
    }

    // --- Delete -------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym();

        $response = $this->adminDelete($admin, '/api/admin/marketing/search-synonyms/'.$id);

        $response->assertOk();
        $this->assertDatabaseMissing('search_synonyms', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/marketing/search-synonyms/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertSynonym();
        $response = $this->deleteJson('/api/admin/marketing/search-synonyms/'.$id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertSynonym();
        $response = $this->adminDelete($admin, '/api/admin/marketing/search-synonyms/'.$id);
        $response->assertStatus(403);
    }

    // --- Mass delete --------------------------------------------------------

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertSynonym();
        $b = $this->insertSynonym();

        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms/mass-delete', [
            'indices' => [$a, $b],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('search_synonyms', ['id' => $a]);
        $this->assertDatabaseMissing('search_synonyms', ['id' => $b]);
    }

    public function test_mass_delete_skips_unknown_ids(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertSynonym();

        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms/mass-delete', [
            'indices' => [$a, 999999],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('search_synonyms', ['id' => $a]);
        expect($response->json('deleted'))->toContain($a);
    }

    public function test_mass_delete_empty_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms/mass-delete', [
            'indices' => [],
        ]);
        $response->assertStatus(422);
    }

    public function test_mass_delete_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $a = $this->insertSynonym();

        $response = $this->adminPost($admin, '/api/admin/marketing/search-synonyms/mass-delete', [
            'indices' => [$a],
        ]);
        $response->assertStatus(403);
    }
}
