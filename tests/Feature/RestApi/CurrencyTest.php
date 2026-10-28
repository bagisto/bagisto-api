<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Currency;

class CurrencyTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/currencies';

    // ============================================================================
    // GET /currencies (Collection)
    // ============================================================================

    /**
     * Test get currencies returns ok.
     */
    public function test_get_currencies_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get currencies returns non empty list.
     */
    public function test_get_currencies_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test currencies have expected fields.
     */
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

    /**
     * Test currencies id is integer.
     */
    public function test_currencies_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    /**
     * Test currencies decimal is integer.
     */
    public function test_currencies_decimal_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['decimal'])->toBeInt();
    }

    // ============================================================================
    // Pagination
    // ============================================================================

    /**
     * Test items per page limits collection size.
     */
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

    /**
     * Test page parameter returns different results.
     */
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

    // ============================================================================
    // GET /currencies/{id} (Single)
    // ============================================================================

    /**
     * Test get single currency returns ok.
     */
    public function test_get_single_currency_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCurrencyId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    /**
     * Test get single currency returns correct id.
     */
    public function test_get_single_currency_returns_correct_id(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCurrencyId();

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body['id'])->toBe($id);
    }

    /**
     * Test get single currency returns expected fields.
     */
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

    /**
     * Test get nonexistent currency returns error.
     */
    public function test_get_nonexistent_currency_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    // ============================================================================
    // Unsupported Methods
    // ============================================================================

    /**
     * A POST on the read-only collection is refused with 405 and the allowed methods, also with debug off.
     */
    public function test_post_on_read_only_collection_returns_method_not_allowed(): void
    {
        $this->seedRequiredData();
        config(['app.debug' => false]);

        $response = $this->postJson($this->collectionUrl, ['code' => 'XYZ'], $this->storefrontHeaders());

        $response->assertStatus(405);
        expect($response->headers->get('Allow'))->toContain('GET');
        expect($response->json('status'))->toBe(405);
        expect($response->json('message'))->toContain('The POST method is not supported for route api/shop/currencies.');
        $this->assertDatabaseMissing('currencies', ['code' => 'XYZ']);
    }

    /**
     * A missing currency keeps API Platform's own 404 problem response with debug off.
     */
    public function test_missing_currency_keeps_the_api_not_found_response_with_debug_off(): void
    {
        $this->seedRequiredData();
        config(['app.debug' => false]);

        $response = $this->publicGet($this->itemUrl(999999));

        $response->assertStatus(404);
        expect($response->json('status'))->toBe(404);
        expect($response->json('detail'))->toBeString()->not->toBeEmpty();
    }

    /**
     * Item url.
     */
    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    /**
     * First currency id.
     */
    private function firstCurrencyId(): int
    {
        $id = Currency::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No currencies found. Run Bagisto seeders.');
        }

        return (int) $id;
    }
}
