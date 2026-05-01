<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Currency;

class CurrencyTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/currencies';

    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    private function firstCurrencyId(): int
    {
        $id = Currency::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No currencies found. Run Bagisto seeders.');
        }

        return (int) $id;
    }

    // ── GET /currencies (collection) ──────────────────────────

    public function test_get_currencies_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_currencies_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_currencies_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('name');
        expect($first)->toHaveKey('symbol');
        expect($first)->toHaveKey('decimal');
    }

    public function test_currencies_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    public function test_currencies_decimal_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['decimal'])->toBeInt();
    }

    // ── Pagination ────────────────────────────────────────────

    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        if (Currency::count() < 2) {
            $this->markTestSkipped('Need at least 2 currencies to test per_page.');
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=1');

        $response->assertOk();
        expect(count($response->json()))->toBe(1);
    }

    public function test_page_parameter_returns_different_results(): void
    {
        $this->seedRequiredData();

        if (Currency::count() < 2) {
            $this->markTestSkipped('Need at least 2 currencies to test page parameter.');
        }

        $page1 = $this->publicGet($this->collectionUrl.'?per_page=1&page=1')->json();
        $page2 = $this->publicGet($this->collectionUrl.'?per_page=1&page=2')->json();

        expect($page1[0]['id'])->not()->toBe($page2[0]['id']);
    }

    public function test_page_beyond_total_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?per_page=10&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET /currencies/{id} (single) ─────────────────────────

    public function test_get_single_currency_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCurrencyId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    public function test_get_single_currency_returns_correct_id(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCurrencyId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body['id'])->toBe($id);
    }

    public function test_get_single_currency_returns_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCurrencyId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('code');
        expect($body)->toHaveKey('name');
        expect($body)->toHaveKey('symbol');
        expect($body)->toHaveKey('decimal');
    }

    public function test_get_nonexistent_currency_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }
}
