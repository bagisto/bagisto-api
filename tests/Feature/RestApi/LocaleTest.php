<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Locale;

class LocaleTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/locales';

    // ── GET /locales (collection) ─────────────────────────────

    /**
     * Test get locales returns ok.
     */
    public function test_get_locales_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get locales returns non empty list.
     */
    public function test_get_locales_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test locales have expected fields.
     */
    public function test_locales_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('name');
        expect($first)->toHaveKey('direction');
    }

    /**
     * Test locales id is integer.
     */
    public function test_locales_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    /**
     * Test locales direction is ltr or rtl.
     */
    public function test_locales_direction_is_ltr_or_rtl(): void
    {
        $this->seedRequiredData();

        foreach ($this->publicGet($this->collectionUrl)->json() as $locale) {
            expect($locale['direction'])->toBeIn(['ltr', 'rtl']);
        }
    }

    // ── Pagination ────────────────────────────────────────────

    /**
     * Test items per page limits collection size.
     */
    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        if (Locale::count() < 2) {
            $this->markTestSkipped('Need at least 2 locales to test per_page.');
        }

        $response = $this->publicGet($this->collectionUrl.'?per_page=1');

        $response->assertOk();
        expect(count($response->json()))->toBe(1);
    }

    /**
     * Test page parameter returns different results.
     */
    public function test_page_parameter_returns_different_results(): void
    {
        $this->seedRequiredData();

        if (Locale::count() < 2) {
            $this->markTestSkipped('Need at least 2 locales to test page parameter.');
        }

        $page1 = $this->publicGet($this->collectionUrl.'?per_page=1&page=1')->json();
        $page2 = $this->publicGet($this->collectionUrl.'?per_page=1&page=2')->json();

        expect($page1[0]['id'])->not()->toBe($page2[0]['id']);
    }

    /**
     * Test page beyond total returns empty.
     */
    public function test_page_beyond_total_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?per_page=10&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET /locales/{id} (single) ────────────────────────────

    /**
     * Test get single locale returns ok.
     */
    public function test_get_single_locale_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstLocaleId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    /**
     * Test get single locale returns correct id.
     */
    public function test_get_single_locale_returns_correct_id(): void
    {
        $this->seedRequiredData();
        $id = $this->firstLocaleId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body['id'])->toBe($id);
    }

    /**
     * Test get single locale returns expected fields.
     */
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

    /**
     * Test get nonexistent locale returns error.
     */
    public function test_get_nonexistent_locale_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Bug 5 regression — locales collection used to drop `logoPath` /
     * `logoUrl` entirely on rows where they were null. Every row must now.
     */
    public function test_every_locale_row_carries_logo_path_and_logo_url_keys(): void
    {
        $this->seedRequiredData();

        $body = $this->publicGet($this->collectionUrl)->json();

        $rows = is_array($body) && isset($body[0]) ? $body : ($body['hydra:member'] ?? $body['data'] ?? []);
        expect($rows)->not->toBeEmpty();

        foreach ($rows as $row) {
            expect(array_key_exists('logoPath', $row))->toBeTrue('logoPath missing on row: '.json_encode($row));
            expect(array_key_exists('logoUrl', $row))->toBeTrue('logoUrl missing on row: '.json_encode($row));
        }
    }

    /**
     * Item url.
     */
    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    /**
     * First locale id.
     */
    private function firstLocaleId(): int
    {
        $id = Locale::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No locales found. Run Bagisto seeders.');
        }

        return (int) $id;
    }
}
