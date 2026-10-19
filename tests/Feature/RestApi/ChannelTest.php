<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;

class ChannelTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/channels';

    // ── GET Collection ────────────────────────────────────────

    /**
     * Test get channels collection returns ok.
     */
    public function test_get_channels_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get channels collection returns non empty list.
     */
    public function test_get_channels_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test channels collection items have expected fields.
     */
    public function test_channels_collection_items_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first)->toBeArray();
        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('hostname');
        expect($first)->toHaveKey('theme');
    }

    /**
     * Test channels collection includes null fields.
     */
    public function test_channels_collection_includes_null_fields(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first)->toHaveKey('timezone');
        expect($first)->toHaveKey('logo');
        expect($first)->toHaveKey('favicon');
        expect($first)->not->toHaveKey('allowedIps');
        expect($first)->not->toHaveKey('allowed_ips');
        expect($first)->toHaveKey('logoUrl');
        expect($first)->toHaveKey('faviconUrl');
    }

    /**
     * Test channels collection includes relationships.
     */
    public function test_channels_collection_includes_relationships(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first)->toHaveKey('locales');
        expect($first)->toHaveKey('currencies');
        expect($first)->toHaveKey('defaultLocale');
        expect($first)->toHaveKey('baseCurrency');
        expect($first['locales'])->toBeArray();
        expect($first['currencies'])->toBeArray();
    }

    // ── Pagination ────────────────────────────────────────────

    /**
     * Test items per page limits collection size.
     */
    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        if (Channel::count() < 2) {
            Channel::factory()->create();
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

        if (Channel::count() < 2) {
            Channel::factory()->create();
        }

        $page1 = $this->publicGet($this->collectionUrl.'?per_page=1&page=1');
        $page2 = $this->publicGet($this->collectionUrl.'?per_page=1&page=2');

        $page1->assertOk();
        $page2->assertOk();

        $id1 = $page1->json('0.id');
        $id2 = $page2->json('0.id');

        expect($id1)->not()->toBeNull();
        expect($id2)->not()->toBeNull();
        expect($id1)->not()->toBe($id2);
    }

    /**
     * Test page beyond total returns empty collection.
     */
    public function test_page_beyond_total_returns_empty_collection(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?per_page=1&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET item ──────────────────────────────────────────────

    /**
     * Test get channel by id returns ok.
     */
    public function test_get_channel_by_id_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstChannelId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
    }

    /**
     * Test get channel by id returns expected fields.
     */
    public function test_get_channel_by_id_returns_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstChannelId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        $body = $response->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('code');
        expect($body)->toHaveKey('hostname');
        expect($body)->toHaveKey('timezone');
        expect($body)->toHaveKey('locales');
        expect($body)->toHaveKey('currencies');
    }

    /**
     * Test get nonexistent channel returns error.
     */
    public function test_get_nonexistent_channel_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Item url.
     */
    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    /**
     * First channel id.
     */
    private function firstChannelId(): int
    {
        $id = Channel::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No channels found. Run Bagisto seeders for channels.');
        }

        return (int) $id;
    }
}
