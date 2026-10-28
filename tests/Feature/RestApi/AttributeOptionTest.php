<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\Attribute\Models\AttributeOption;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AttributeOptionTest extends RestApiTestCase
{
    /**
     * The collection url.
     */
    private string $collectionUrl = '/api/shop/attribute-options';

    // ============================================================================
    // GET Collection
    // ============================================================================

    /**
     * Test get attribute options collection returns ok.
     */
    public function test_get_attribute_options_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get attribute options collection returns non empty list.
     */
    public function test_get_attribute_options_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test attribute options collection items have expected fields.
     */
    public function test_attribute_options_collection_items_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first)->toBeArray();
        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('adminName');
        expect($first)->toHaveKey('sortOrder');
    }

    /**
     * Test attribute options collection ids are integers.
     */
    public function test_attribute_options_collection_ids_are_integers(): void
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
     * Test get single attribute option returns ok.
     */
    public function test_get_single_attribute_option_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstOptionId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    /**
     * Test single attribute option has expected fields.
     */
    public function test_single_attribute_option_has_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstOptionId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        $data = $response->json();

        expect($data)->toHaveKey('id');
        expect($data)->toHaveKey('adminName');
        expect($data)->toHaveKey('sortOrder');
        expect($data['id'])->toBe($id);
    }

    /**
     * Test single attribute option exposes translation.
     */
    public function test_single_attribute_option_exposes_translation(): void
    {
        $this->seedRequiredData();
        $id = $this->firstOptionId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        $data = $response->json();

        expect($data)->toHaveKey('translation');
        if ($data['translation'] !== null) {
            expect($data['translation'])->toHaveKey('locale');
            expect($data['translation'])->toHaveKey('label');
        }
    }

    /**
     * Test single attribute option exposes translations array.
     */
    public function test_single_attribute_option_exposes_translations_array(): void
    {
        $this->seedRequiredData();
        $id = $this->firstOptionId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        expect($response->json())->toHaveKey('translations');
        expect($response->json('translations'))->toBeArray();
    }

    /**
     * Test get non existent attribute option returns 404.
     */
    public function test_get_non_existent_attribute_option_returns_404(): void
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
     * First option id.
     */
    private function firstOptionId(): int
    {
        $id = AttributeOption::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No attribute options found. Run Bagisto seeders for attributes.');
        }

        return (int) $id;
    }
}
