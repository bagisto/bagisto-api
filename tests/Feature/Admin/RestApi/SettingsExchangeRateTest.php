<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin Settings → Exchange Rates CRUD endpoints
 * (Block B Wave 1).
 *
 * Endpoints:
 *   GET    /api/admin/settings/exchange-rates
 *   GET    /api/admin/settings/exchange-rates/{id}
 *   POST   /api/admin/settings/exchange-rates
 *   PUT    /api/admin/settings/exchange-rates/{id}
 *   DELETE /api/admin/settings/exchange-rates/{id}
 *   POST   /api/admin/settings/exchange-rates/mass-delete
 */
class SettingsExchangeRateTest extends AdminApiTestCase
{
    protected function insertCurrency(array $overrides = []): int
    {
        return \DB::table('currencies')->insertGetId(array_merge([
            'code'              => 'TC'.substr((string) microtime(true), -4).rand(10, 99),
            'name'              => 'Test Currency',
            'symbol'            => '$',
            'decimal'           => 2,
            'group_separator'   => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $overrides));
    }

    protected function insertExchangeRate(int $targetCurrencyId, float $rate = 1.0): int
    {
        return \DB::table('currency_exchange_rates')->insertGetId([
            'target_currency' => $targetCurrencyId,
            'rate'            => $rate,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/settings/exchange-rates');
        $response->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $cur = $this->insertCurrency();

        $response = $this->postJson('/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate'            => 1.1,
        ]);
        $response->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $this->seedRequiredData();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.05);

        $response = $this->publicGet('/api/admin/settings/exchange-rates/'.$id);
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_row_with_currency_code(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency(['code' => 'EU'.rand(10, 99), 'name' => 'TestEuro']);
        $id = $this->insertExchangeRate($cur, 1.075);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $id);

        expect($row)->not()->toBeNull();
        expect($row['targetCurrency'])->toBe($cur);
        expect((float) $row['rate'])->toBe(1.075);
        expect($row)->toHaveKeys(['id', 'targetCurrency', 'targetCurrencyCode', 'targetCurrencyName', 'rate', 'createdAt', 'updatedAt']);
    }

    public function test_listing_filter_by_target_currency(): void
    {
        $admin = $this->createAdmin();
        $cur1 = $this->insertCurrency();
        $cur2 = $this->insertCurrency();
        $id1 = $this->insertExchangeRate($cur1, 1.1);
        $id2 = $this->insertExchangeRate($cur2, 2.2);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?target_currency='.$cur1);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_listing_filter_by_rate_range(): void
    {
        $admin = $this->createAdmin();
        $idLo = $this->insertExchangeRate($this->insertCurrency(), 0.2);
        $idMid = $this->insertExchangeRate($this->insertCurrency(), 1.5);
        $idHi = $this->insertExchangeRate($this->insertCurrency(), 9.5);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?rate_from=1&rate_to=5&per_page=50');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($idMid);
        expect($ids)->not()->toContain($idLo);
        expect($ids)->not()->toContain($idHi);
    }

    public function test_listing_sort_by_rate_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertExchangeRate($this->insertCurrency(), 9.99);
        $this->insertExchangeRate($this->insertCurrency(), 0.11);
        $this->insertExchangeRate($this->insertCurrency(), 4.44);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?sort=rate-asc&per_page=50');

        $response->assertOk();
        $rates = collect($response->json('data'))->pluck('rate')->map(fn ($r) => (float) $r)->all();
        $sorted = $rates;
        sort($sorted);
        expect($rates)->toBe($sorted);
    }

    public function test_listing_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_listing_page_beyond_last_returns_empty(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?page=9999&per_page=10');
        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    public function test_detail_returns_full_row(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency(['code' => 'DT'.rand(10, 99), 'name' => 'DetailTest']);
        $id = $this->insertExchangeRate($cur, 1.234);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('targetCurrency'))->toBe($cur);
        expect((float) $response->json('rate'))->toBe(1.234);
        expect($response->json('targetCurrencyName'))->toBe('DetailTest');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates/9999999');
        $response->assertStatus(404);
    }

    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate'            => 1.085,
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('targetCurrency'))->toBe($cur);
        expect((float) $response->json('rate'))->toBe(1.085);
        expect(\DB::table('currency_exchange_rates')->where('target_currency', $cur)->exists())->toBeTrue();
    }

    public function test_create_missing_target_currency_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', ['rate' => 1.0]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_rate_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', ['target_currency' => $cur]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_non_positive_rate_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate'            => 0,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_unknown_currency_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => 999999,
            'rate'            => 1.0,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_pair_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate'            => 2.0,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_changes_rate(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'target_currency' => $cur,
            'rate'            => 2.5,
        ]);

        $response->assertOk();
        expect((float) \DB::table('currency_exchange_rates')->where('id', $id)->value('rate'))->toBe(2.5);
    }

    public function test_update_changes_target_currency(): void
    {
        $admin = $this->createAdmin();
        $cur1 = $this->insertCurrency();
        $cur2 = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur1, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'target_currency' => $cur2,
            'rate'            => 1.0,
        ]);

        $response->assertOk();
        expect((int) \DB::table('currency_exchange_rates')->where('id', $id)->value('target_currency'))->toBe($cur2);
    }

    public function test_update_same_target_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'target_currency' => $cur,
            'rate'            => 1.55,
        ]);

        $response->assertOk();
    }

    public function test_update_duplicate_target_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur1 = $this->insertCurrency();
        $cur2 = $this->insertCurrency();
        $id1 = $this->insertExchangeRate($cur1, 1.0);
        $id2 = $this->insertExchangeRate($cur2, 2.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id2, [
            'target_currency' => $cur1,
            'rate'            => 3.0,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/9999999', [
            'target_currency' => $cur,
            'rate'            => 1.0,
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_partial_rate_only_keeps_existing_currency(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'rate' => 7.77,
        ]);

        $response->assertOk();
        expect((float) \DB::table('currency_exchange_rates')->where('id', $id)->value('rate'))->toBe(7.77);
        expect((int) \DB::table('currency_exchange_rates')->where('id', $id)->value('target_currency'))->toBe($cur);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminDelete($admin, '/api/admin/settings/exchange-rates/'.$id);

        $response->assertOk();
        expect(\DB::table('currency_exchange_rates')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/exchange-rates/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->deleteJson('/api/admin/settings/exchange-rates/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertExchangeRate($this->insertCurrency(), 1.0);
        $id2 = $this->insertExchangeRate($this->insertCurrency(), 2.0);

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(\DB::table('currency_exchange_rates')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('currency_exchange_rates')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mass_delete_silently_skips_unknown_ids(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertExchangeRate($this->insertCurrency(), 1.0);

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/mass-delete', [
            'indices' => [$id1, 9999999],
        ]);

        $response->assertOk();
        expect(\DB::table('currency_exchange_rates')->where('id', $id1)->exists())->toBeFalse();
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/exchange-rates/mass-delete', ['indices' => [1]]);
        expect($response->getStatusCode())->toBe(401);
    }
}
