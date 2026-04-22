<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Locale;

class LocaleTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/locales';

    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    private function firstLocaleId(): int
    {
        $id = Locale::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No locales found. Run Bagisto seeders.');
        }

        return (int) $id;
    }

    // ── GET /locales (collection) ─────────────────────────────

    public function test_get_locales_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_locales_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_locales_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('name');
        expect($first)->toHaveKey('direction');
    }

    public function test_locales_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    public function test_locales_direction_is_ltr_or_rtl(): void
    {
        $this->seedRequiredData();

        foreach ($this->publicGet($this->collectionUrl)->json() as $locale) {
            expect($locale['direction'])->toBeIn(['ltr', 'rtl']);
        }
    }

    // ── Pagination ────────────────────────────────────────────

    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        if (Locale::count() < 2) {
            $this->markTestSkipped('Need at least 2 locales to test itemsPerPage.');
        }

        $response = $this->publicGet($this->collectionUrl.'?itemsPerPage=1');

        $response->assertOk();
        expect(count($response->json()))->toBe(1);
    }

    public function test_page_parameter_returns_different_results(): void
    {
        $this->seedRequiredData();

        if (Locale::count() < 2) {
            $this->markTestSkipped('Need at least 2 locales to test page parameter.');
        }

        $page1 = $this->publicGet($this->collectionUrl.'?itemsPerPage=1&page=1')->json();
        $page2 = $this->publicGet($this->collectionUrl.'?itemsPerPage=1&page=2')->json();

        expect($page1[0]['id'])->not()->toBe($page2[0]['id']);
    }

    public function test_page_beyond_total_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?itemsPerPage=10&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET /locales/{id} (single) ────────────────────────────

    public function test_get_single_locale_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstLocaleId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    public function test_get_single_locale_returns_correct_id(): void
    {
        $this->seedRequiredData();
        $id = $this->firstLocaleId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body['id'])->toBe($id);
    }

    public function test_get_single_locale_returns_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstLocaleId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('code');
        expect($body)->toHaveKey('name');
        expect($body)->toHaveKey('direction');
    }

    public function test_get_nonexistent_locale_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }
}
