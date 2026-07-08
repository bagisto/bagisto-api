<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Marketing\Models\SearchTerm;

/**
 * REST coverage for Admin Marketing → Search Terms (Block F3b).
 */
class MarketingSearchTermTest extends AdminApiTestCase
{
    protected function adminPut($admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete($admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function seedSearchTerm(array $overrides = []): SearchTerm
    {
        $this->seedRequiredData();

        return SearchTerm::factory()->create(array_merge([
            'term' => 'term-'.uniqid(),
            'uses' => 0,
            'results' => 0,
        ], $overrides));
    }

    public function test_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/search-terms')->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $s = $this->seedSearchTerm();
        $this->publicGet('/api/admin/marketing/search-terms/'.$s->id)->assertStatus(401);
    }

    public function test_update_requires_auth(): void
    {
        $s = $this->seedSearchTerm();
        $this->putJson('/api/admin/marketing/search-terms/'.$s->id, ['term' => 'x'])->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $s = $this->seedSearchTerm();
        $this->deleteJson('/api/admin/marketing/search-terms/'.$s->id)->assertStatus(401);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/marketing/search-terms/mass-delete', ['indices' => [1]])->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms');
        $resp->assertOk();
        expect($resp->json())->toHaveKeys(['data', 'meta']);
        expect($resp->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_listing_returns_seeded_term(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?per_page=50');
        $resp->assertOk();
        $row = collect($resp->json('data'))->firstWhere('id', $s->id);
        expect($row)->not()->toBeNull();
        expect($row)->toHaveKeys(['term', 'uses', 'results', 'locale']);
        // channel is a detail-only object — null on list rows.
        expect($row['channel'] ?? null)->toBeNull();
    }

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

    public function test_listing_per_page_capped(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?per_page=9999');
        $resp->assertOk();
        expect($resp->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_listing_sort_by_uses_desc_popular(): void
    {
        $admin = $this->createAdmin();
        $unique = 'uses-sort-'.uniqid();
        $low = $this->seedSearchTerm(['uses' => 1, 'term' => $unique.'-low']);
        $high = $this->seedSearchTerm(['uses' => 999, 'term' => $unique.'-high']);

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms?sort=uses&order=desc&per_page=50&term='.$unique);
        $resp->assertOk();
        $rows = collect($resp->json('data'));
        $highPos = $rows->search(fn ($r) => $r['id'] === $high->id);
        $lowPos = $rows->search(fn ($r) => $r['id'] === $low->id);
        expect($highPos)->not()->toBeFalse();
        expect($lowPos)->not()->toBeFalse();
        expect($highPos)->toBeLessThan($lowPos);
    }

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

    public function test_detail_returns_term(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminGet($admin, '/api/admin/marketing/search-terms/'.$s->id);
        $resp->assertOk();
        expect($resp->json('id'))->toBe($s->id);
        expect($resp->json('term'))->toBe($s->term);
        // channel is now a to-one object { id, code, name } (replaces channelId/channelName).
        if ($s->channel_id) {
            expect($resp->json('channel.id'))->toBe((int) $s->channel_id);
            expect($resp->json('channel'))->toHaveKeys(['id', 'code', 'name']);
        }
    }

    public function test_detail_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/marketing/search-terms/9999999')->assertStatus(404);
    }

    public function test_update_term_happy(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm(['term' => 'old']);

        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/'.$s->id, ['term' => 'new-term']);
        $resp->assertOk();
        expect($resp->json('term'))->toBe('new-term');
        expect(SearchTerm::find($s->id)->term)->toBe('new-term');
    }

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

    public function test_update_missing_term_422(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/'.$s->id, []);
        expect($resp->getStatusCode())->toBe(422);
    }

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

    public function test_update_unknown_id_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPut($admin, '/api/admin/marketing/search-terms/9999999', ['term' => 'x']);
        expect($resp->getStatusCode())->toBe(404);
    }

    public function test_delete_term(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $resp = $this->adminDelete($admin, '/api/admin/marketing/search-terms/'.$s->id);
        expect(in_array($resp->getStatusCode(), [200, 204]))->toBeTrue();
        expect(SearchTerm::find($s->id))->toBeNull();
    }

    public function test_delete_unknown_404(): void
    {
        $admin = $this->createAdmin();
        $resp = $this->adminDelete($admin, '/api/admin/marketing/search-terms/9999999');
        expect($resp->getStatusCode())->toBe(404);
    }

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

    public function test_mass_delete_empty_indices_422(): void
    {
        $admin = $this->createAdmin();

        $resp = $this->postJson('/api/admin/marketing/search-terms/mass-delete', [
            'indices' => [],
        ], $this->adminHeaders($admin));

        expect($resp->getStatusCode())->toBe(422);
    }
}
