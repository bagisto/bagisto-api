<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Helpers\Exchange\ExchangeRates;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class SettingsExchangeRateTest extends AdminApiTestCase
{
    /**
     * Test listing requires admin token.
     */
    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/settings/exchange-rates');
        $response->assertStatus(401);
    }

    /**
     * Test create requires auth.
     */
    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $cur = $this->insertCurrency();

        $response = $this->postJson('/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate' => 1.1,
        ]);
        $response->assertStatus(401);
    }

    /**
     * Test detail requires auth.
     */
    public function test_detail_requires_auth(): void
    {
        $this->seedRequiredData();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.05);

        $response = $this->publicGet('/api/admin/settings/exchange-rates/'.$id);
        $response->assertStatus(401);
    }

    /**
     * Test listing returns envelope.
     */
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

    /**
     * Test listing returns seeded row with currency code.
     */
    public function test_listing_returns_seeded_row_with_currency_code(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency([
            'code' => 'EU'.rand(10, 99),
            'name' => 'TestEuro',
        ]);
        $id = $this->insertExchangeRate($cur, 1.075);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates');

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $id);

        expect($row)->not()->toBeNull();
        expect($row['targetCurrency'])->toBe($cur);
        expect((float) $row['rate'])->toBe(1.075);
        expect($row)->toHaveKeys(['id', 'targetCurrency', 'targetCurrencyCode', 'targetCurrencyName', 'rate', 'createdAt', 'updatedAt']);
    }

    /**
     * Test listing filter by target currency.
     */
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

    /**
     * Test listing filter by rate range.
     */
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

    /**
     * Test listing sort by rate asc.
     */
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

    /**
     * Test listing per page above cap clamped.
     */
    public function test_listing_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test listing page beyond last returns empty.
     */
    public function test_listing_page_beyond_last_returns_empty(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates?page=9999&per_page=10');
        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    /**
     * Test detail returns full row.
     */
    public function test_detail_returns_full_row(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency([
            'code' => 'DT'.rand(10, 99),
            'name' => 'DetailTest',
        ]);
        $id = $this->insertExchangeRate($cur, 1.234);

        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('targetCurrency'))->toBe($cur);
        expect((float) $response->json('rate'))->toBe(1.234);
        expect($response->json('targetCurrencyName'))->toBe('DetailTest');
    }

    /**
     * Test detail unknown id returns 404.
     */
    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/exchange-rates/9999999');
        $response->assertStatus(404);
    }

    /**
     * Test create happy path returns 201.
     */
    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate' => 1.085,
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('targetCurrency'))->toBe($cur);
        expect((float) $response->json('rate'))->toBe(1.085);
        expect(\DB::table('currency_exchange_rates')->where('target_currency', $cur)->exists())->toBeTrue();
    }

    /**
     * Test create missing target currency returns 422.
     */
    public function test_create_missing_target_currency_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', ['rate' => 1.0]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create missing rate returns 422.
     */
    public function test_create_missing_rate_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', ['target_currency' => $cur]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create non positive rate returns 422.
     */
    public function test_create_non_positive_rate_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate' => 0,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create unknown currency returns 422.
     */
    public function test_create_unknown_currency_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => 999999,
            'rate' => 1.0,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test create duplicate pair returns 422.
     */
    public function test_create_duplicate_pair_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates', [
            'target_currency' => $cur,
            'rate' => 2.0,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test update changes rate.
     */
    public function test_update_changes_rate(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'target_currency' => $cur,
            'rate' => 2.5,
        ]);

        $response->assertOk();
        expect((float) \DB::table('currency_exchange_rates')->where('id', $id)->value('rate'))->toBe(2.5);
    }

    /**
     * Test update changes target currency.
     */
    public function test_update_changes_target_currency(): void
    {
        $admin = $this->createAdmin();
        $cur1 = $this->insertCurrency();
        $cur2 = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur1, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'target_currency' => $cur2,
            'rate' => 1.0,
        ]);

        $response->assertOk();
        expect((int) \DB::table('currency_exchange_rates')->where('id', $id)->value('target_currency'))->toBe($cur2);
    }

    /**
     * Test update same target excludes self.
     */
    public function test_update_same_target_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id, [
            'target_currency' => $cur,
            'rate' => 1.55,
        ]);

        $response->assertOk();
    }

    /**
     * Test update duplicate target returns 422.
     */
    public function test_update_duplicate_target_returns_422(): void
    {
        $admin = $this->createAdmin();
        $cur1 = $this->insertCurrency();
        $cur2 = $this->insertCurrency();
        $id1 = $this->insertExchangeRate($cur1, 1.0);
        $id2 = $this->insertExchangeRate($cur2, 2.0);

        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/'.$id2, [
            'target_currency' => $cur1,
            'rate' => 3.0,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test update unknown id returns 404.
     */
    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $response = $this->adminPut($admin, '/api/admin/settings/exchange-rates/9999999', [
            'target_currency' => $cur,
            'rate' => 1.0,
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test update partial rate only keeps existing currency.
     */
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

    /**
     * Test delete happy path.
     */
    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->adminDelete($admin, '/api/admin/settings/exchange-rates/'.$id);

        $response->assertOk();
        expect(\DB::table('currency_exchange_rates')->where('id', $id)->exists())->toBeFalse();
    }

    /**
     * Test delete unknown id returns 404.
     */
    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/exchange-rates/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test delete requires auth.
     */
    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);

        $response = $this->deleteJson('/api/admin/settings/exchange-rates/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test mass delete happy path.
     */
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

    /**
     * Test mass delete silently skips unknown ids.
     */
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

    /**
     * Test mass delete empty indices returns 422.
     */
    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test mass delete requires auth.
     */
    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/exchange-rates/mass-delete', ['indices' => [1]]);
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test update rates happy path.
     */
    public function test_update_rates_happy_path(): void
    {
        $this->seedRequiredData();
        $admin = $this->createAdmin();
        $this->fakeExchangeHelper();

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/update-rates');

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('message'))->not->toBeNull();
    }

    /**
     * Test update rates provider failure returns 422.
     */
    public function test_update_rates_provider_failure_returns_422(): void
    {
        $this->seedRequiredData();
        $admin = $this->createAdmin();
        $this->fakeExchangeHelper(function () {
            throw new \Exception('Fixer API key invalid');
        });

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/update-rates');

        expect($response->getStatusCode())->toBe(422);
        expect($response->json('detail') ?? $response->json('message'))->toContain('Fixer API key invalid');
    }

    /**
     * Test update rates requires auth.
     */
    public function test_update_rates_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/exchange-rates/update-rates');
        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test update rates no permission returns 403.
     */
    public function test_update_rates_no_permission_returns_403(): void
    {
        $this->seedRequiredData();
        $role = Role::factory()->create([
            'permission_type' => 'custom',
            'permissions' => [],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $this->fakeExchangeHelper();

        $response = $this->adminPost($admin, '/api/admin/settings/exchange-rates/update-rates');
        expect($response->getStatusCode())->toBe(403);
    }

    /**
     * Insert currency.
     */
    protected function insertCurrency(array $overrides = []): int
    {
        return \DB::table('currencies')->insertGetId(array_merge([
            'code' => 'TC'.substr((string) microtime(true), -4).rand(10, 99),
            'name' => 'Test Currency',
            'symbol' => '$',
            'decimal' => 2,
            'group_separator' => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    /**
     * Insert exchange rate.
     */
    protected function insertExchangeRate(int $targetCurrencyId, float $rate = 1.0): int
    {
        return \DB::table('currency_exchange_rates')->insertGetId([
            'target_currency' => $targetCurrencyId,
            'rate' => $rate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Admin put.
     */
    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    /**
     * Admin delete.
     */
    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    /**
     * Fake exchange helper.
     */
    protected function fakeExchangeHelper(?\Closure $update = null): void
    {
        $this->app->bind(ExchangeRates::class, fn () => new class($update)
        {
            public function __construct(private $update) {}

            public function updateRates()
            {
                if ($this->update) {
                    ($this->update)();
                }
            }
        });
    }
}
