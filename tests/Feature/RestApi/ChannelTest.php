<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Channel;

class ChannelTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/channels';

    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    private function firstChannelId(): int
    {
        $id = Channel::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No channels found. Run Bagisto seeders for channels.');
        }

        return (int) $id;
    }

    // ── GET Collection ────────────────────────────────────────

    public function test_get_channels_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_channels_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

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

    public function test_channels_collection_includes_null_fields(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        // normalizationContext: skip_null_values => false must preserve null keys.
        expect($first)->toHaveKey('timezone');
        expect($first)->toHaveKey('logo');
        expect($first)->toHaveKey('favicon');
        expect($first)->toHaveKey('allowedIps');
        expect($first)->toHaveKey('logoUrl');
        expect($first)->toHaveKey('faviconUrl');
    }

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

    public function test_page_beyond_total_returns_empty_collection(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?per_page=1&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET item ──────────────────────────────────────────────

    public function test_get_channel_by_id_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstChannelId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
    }

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

    public function test_get_nonexistent_channel_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }
}
