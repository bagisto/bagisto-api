<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AttributeTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/attributes';

    // ============================================================================
    // GET Collection
    // ============================================================================

    /**
     * Test get attributes collection returns ok.
     */
    public function test_get_attributes_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get attributes collection returns non empty list.
     */
    public function test_get_attributes_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test attributes collection items have expected fields.
     */
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

    /**
     * Test attributes collection ids are integers.
     */
    public function test_attributes_collection_ids_are_integers(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first['id'])->toBeInt();
    }

    // ============================================================================
    // GET Single
    // ============================================================================

    /**
     * Test get single attribute returns ok.
     */
    public function test_get_single_attribute_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstAttributeId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    /**
     * Test single attribute has expected fields.
     */
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

    /**
     * Test single attribute exposes translation.
     */
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

    /**
     * Test single attribute timestamps are iso8601.
     */
    public function test_single_attribute_timestamps_are_iso8601(): void
    {
        $this->seedRequiredData();
        $id = $this->firstAttributeId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        expect($response->json('createdAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($response->json('updatedAt'))->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    /**
     * Test get non existent attribute returns 404.
     */
    public function test_get_non_existent_attribute_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        $response->assertNotFound();
    }

    /**
     * Item url.
     */
    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    /**
     * First attribute id.
     */
    private function firstAttributeId(): int
    {
        $id = Attribute::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No attributes found. Run Bagisto seeders for attributes.');
        }

        return (int) $id;
    }
}
