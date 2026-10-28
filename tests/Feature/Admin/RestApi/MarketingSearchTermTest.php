<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Marketing\Models\SearchTerm;

class MarketingSearchTermTest extends AdminApiTestCase
{
    /**
     * Test listing requires auth.
     */
    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/search-terms')->assertStatus(401);
    }

    /**
     * Test detail requires auth.
     */
    public function test_detail_requires_auth(): void
    {
        $s = $this->seedSearchTerm();
        $this->publicGet('/api/admin/marketing/search-terms/'.$s->id)->assertStatus(401);
    }

    /**
     * Test update requires auth.
     */
    public function test_update_requires_auth(): void
    {
        $s = $this->seedSearchTerm();
        $this->putJson('/api/admin/marketing/search-terms/'.$s->id, ['term' => 'x'])->assertStatus(401);
    }

    /**
     * Test delete requires auth.
     */
    public function test_delete_requires_auth(): void
    {
        $s = $this->seedSearchTerm();
        $this->deleteJson('/api/admin/marketing/search-terms/'.$s->id)->assertStatus(401);
    }

    /**
     * Test mass delete requires auth.
     */
    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/marketing/search-terms/mass-delete', ['indices' => [1]])->assertStatus(401);
    }

    /**
     * Test listing returns envelope.
     */
    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    /**
     * Test listing returns seeded term.
     */
    public function test_listing_returns_seeded_term(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $s->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['term', 'uses', 'results', 'locale']);
        expect($row['channel'] ?? null)->toBeNull();
    }

    /**
     * Test listing filter by term.
     */
    public function test_listing_filter_by_term(): void
    {
        $admin = $this->createAdmin();
        $unique = 'unique-term-'.uniqid();
        $hit = $this->seedSearchTerm(['term' => $unique]);
        $miss = $this->seedSearchTerm(['term' => 'other-'.uniqid()]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?term=unique-term-&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($hit->id);
        expect($ids)->not()->toContain($miss->id);
    }

    /**
     * Test listing filter by channel.
     */
    public function test_listing_filter_by_channel(): void
    {
        $admin = $this->createAdmin();
        $channelId = core()->getCurrentChannel()->id;
        $s = $this->seedSearchTerm(['channel_id' => $channelId]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?channel_id='.$channelId.'&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($s->id);
    }

    /**
     * Test listing filter by locale.
     */
    public function test_listing_filter_by_locale(): void
    {
        $admin = $this->createAdmin();
        $locale = core()->getCurrentLocale()->code;
        $s = $this->seedSearchTerm(['locale' => $locale]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?locale='.$locale.'&per_page=50');
        $resp->assertOk();
        $ids = collect($resp->json('data'))->pluck('id')->all();
        expect($ids)->toContain($s->id);
    }

    /**
     * Test listing per page capped.
     */
    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test listing sort by uses desc popular.
     */
    public function test_listing_sort_by_uses_desc_popular(): void
    {
        $admin = $this->createAdmin();
        $unique = 'uses-sort-'.uniqid();
        $low = $this->seedSearchTerm([
            'uses' => 1,
            'term' => $unique.'-low',
        ]);
        $high = $this->seedSearchTerm([
            'uses' => 999,
            'term' => $unique.'-high',
        ]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?sort=uses&order=desc&per_page=50&term='.$unique);
        $resp->assertOk();
        $rows = collect($resp->json('data'));
        $highPos = $rows->search(fn ($r) => $r['id'] === $high->id);
        $lowPos = $rows->search(fn ($r) => $r['id'] === $low->id);
        expect($highPos)->not()->toBeFalse();
        expect($lowPos)->not()->toBeFalse();
        expect($highPos)->toBeLessThan($lowPos);
    }

    /**
     * Test listing sort by term asc.
     */
    public function test_listing_sort_by_term_asc(): void
    {
        $admin = $this->createAdmin();
        $this->seedSearchTerm(['term' => 'aaa-'.uniqid()]);
        $this->seedSearchTerm(['term' => 'zzz-'.uniqid()]);

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?sort=term&order=asc&per_page=50');
        $resp->assertOk();
        $terms = collect($resp->json('data'))->pluck('term')->all();
        $sorted = $terms;
        sort($sorted, SORT_FLAG_CASE | SORT_STRING);
        expect($terms)->toEqual($sorted);
    }

    /**
     * Test detail returns term.
     */
    public function test_detail_returns_term(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms/'.$s->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($s->id);
        expect($resp->json('term'))->toBe($s->term);
        if ($s->channel_id) {
            expect($resp->json('channel.id'))->toBe((int) $s->channel_id);
            expect($resp->json('channel'))->toHaveKeys(['id', 'code', 'name']);
        }
    }

    /**
     * Test detail unknown returns 404.
     */
    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/marketing/search-terms/9999999')->assertStatus(404);
    }

    /**
     * Test update term happy.
     */
    public function test_update_term_happy(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm(['term' => 'old']);

        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/'.$s->id, ['term' => 'new-term']);
        $resp->assertOk();
        expect($resp->json('term'))->toBe('new-term');
        expect(SearchTerm::find($s->id)->term)->toBe('new-term');
    }

    /**
     * Test update redirect url.
     */
    public function test_update_redirect_url(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/'.$s->id, [
            'term' => 'still',
            'redirect_url' => 'https://example.com/shoes',
        ]);
        $resp->assertOk();
        expect($resp->json('redirectUrl'))->toBe('https://example.com/shoes');
    }

    /**
     * Test update missing term 422.
     */
    public function test_update_missing_term_422(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/'.$s->id, []);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update invalid redirect url 422.
     */
    public function test_update_invalid_redirect_url_422(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/'.$s->id, [
            'term' => 'x',
            'redirect_url' => 'not a url',
        ]);
        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test update unknown id 404.
     */
    public function test_update_unknown_id_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/9999999', ['term' => 'x']);
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test delete term.
     */
    public function test_delete_term(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminDelete($admin, '/api/admin/marketing/search-terms/'.$s->id);
        expect(in_array($resp->getStatusCode(), [200, 204]))->toBeTrue();
        expect(SearchTerm::find($s->id))->toBeNull();
    }

    /**
     * Test delete unknown 404.
     */
    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/marketing/search-terms/9999999');
        expect($resp->getStatusCode())->toBe(404);
    }

    /**
     * Test mass delete.
     */
    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedSearchTerm();
        $b = $this->seedSearchTerm();

        $resp = $this->postJson('/api/admin/marketing/search-terms/mass-delete', [
            'indices' => [$a->id, $b->id],
        ], $this->adminHeaders($admin));

        $resp->assertOk();
        expect(SearchTerm::find($a->id))->toBeNull();
        expect(SearchTerm::find($b->id))->toBeNull();
    }

    /**
     * Test mass delete skips unknown.
     */
    public function test_mass_delete_skips_unknown(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedSearchTerm();

        $resp = $this->postJson('/api/admin/marketing/search-terms/mass-delete', [
            'indices' => [$a->id, 9999999],
        ], $this->adminHeaders($admin));

        $resp->assertOk();
        expect(SearchTerm::find($a->id))->toBeNull();
        expect($resp->json('deleted'))->toEqual([$a->id]);
    }

    /**
     * Test mass delete empty indices 422.
     */
    public function test_mass_delete_empty_indices_422(): void
    {
        $admin = $this->createAdmin();

        $resp = $this->postJson('/api/admin/marketing/search-terms/mass-delete', [
            'indices' => [],
        ], $this->adminHeaders($admin));

        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Test create requires auth.
     */
    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/marketing/search-terms', ['term' => 'x'])->assertStatus(401);
    }

    /**
     * Test create happy path.
     */
    public function test_create_happy_path(): void
    {
        $this->seedRequiredData();
        $admin = $this->createAdmin();
        $channelId = core()->getCurrentChannel()->id;
        $locale = core()->getCurrentLocale()->code;

        $resp = $this->adminPost($admin, '/api/admin/marketing/search-terms', [
            'term' => 'qa-create-'.uniqid(),
            'redirect_url' => 'https://example.com/qa',
            'channel_id' => $channelId,
            'locale' => $locale,
        ]);

        expect($resp->getStatusCode())->toBe(201);
        expect($resp->json('term'))->not()->toBeNull();
        expect($resp->json('channel.id'))->toBe($channelId);

        $this->assertDatabaseHas('search_terms', [
            'id' => $resp->json('id'),
            'redirect_url' => 'https://example.com/qa',
            'channel_id' => $channelId,
            'locale' => $locale,
        ]);
    }

    /**
     * Test create requires term channel locale.
     */
    public function test_create_requires_term_channel_locale(): void
    {
        $this->seedRequiredData();
        $admin = $this->createAdmin();
        $locale = core()->getCurrentLocale()->code;

        expect($this->adminPost($admin, '/api/admin/marketing/search-terms', [
            'term' => 'x', 'locale' => $locale,
        ])->getStatusCode())->toBe(422);

        expect($this->adminPost($admin, '/api/admin/marketing/search-terms', [
            'channel_id' => core()->getCurrentChannel()->id, 'locale' => $locale,
        ])->getStatusCode())->toBe(422);
    }

    /**
     * Test create rejects invalid redirect url.
     */
    public function test_create_rejects_invalid_redirect_url(): void
    {
        $this->seedRequiredData();
        $admin = $this->createAdmin();

        $resp = $this->adminPost($admin, '/api/admin/marketing/search-terms', [
            'term' => 'x',
            'redirect_url' => 'not-a-url',
            'channel_id' => core()->getCurrentChannel()->id,
            'locale' => core()->getCurrentLocale()->code,
        ]);

        expect($resp->getStatusCode())->toBe(422);
    }

    /**
     * Admin put.
     */
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Admin delete.
     */
    protected function adminDelete($admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Seed search term.
     */
    protected function seedSearchTerm(array $overrides = []): SearchTerm
    {
        $this->seedRequiredData();

        return SearchTerm::factory()->create(array_merge([
            'term' => 'term-'.uniqid(),
            'uses' => 0,
            'results' => 0,
        ], $overrides));
    }
}
