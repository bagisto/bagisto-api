<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AttributeTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/attributes';

    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    private function firstAttributeId(): int
    {
        $id = Attribute::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No attributes found. Run Bagisto seeders for attributes.');
        }

        return (int) $id;
    }

    // ── GET Collection ────────────────────────────────────────

    public function test_get_attributes_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_attributes_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_attributes_collection_items_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first)->toBeArray();
        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('adminName');
        expect($first)->toHaveKey('type');
    }

    public function test_attributes_collection_ids_are_integers(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first['id'])->toBeInt();
    }

    // ── GET Single ────────────────────────────────────────────

    public function test_get_single_attribute_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstAttributeId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    public function test_single_attribute_has_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstAttributeId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data)->toHaveKey('code');
        expect($data)->toHaveKey('adminName');
        expect($data)->toHaveKey('type');
        expect($data['id'])->toBe($id);
    }

    public function test_single_attribute_exposes_translation(): void
    {
        $this->seedRequiredData();
        $id = $this->firstAttributeId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        $data = $response->json();

        expect($data)->toHaveKey('translation');
        if ($data['translation'] !== null) {
            expect($data['translation'])->toHaveKey('locale');
            expect($data['translation'])->toHaveKey('name');
        }
    }

    public function test_single_attribute_timestamps_are_iso8601(): void
    {
        $this->seedRequiredData();
        $id = $this->firstAttributeId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        expect($response->json('createdAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($response->json('updatedAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    public function test_get_non_existent_attribute_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        $response->assertNotFound();
    }
}
