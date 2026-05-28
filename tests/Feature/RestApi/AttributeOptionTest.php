<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\Attribute\Models\AttributeOption;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class AttributeOptionTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/attribute-options';

    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    private function firstOptionId(): int
    {
        $id = AttributeOption::query()->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No attribute options found. Run Bagisto seeders for attributes.');
        }

        return (int) $id;
    }

    // ── GET Collection ────────────────────────────────────────

    public function test_get_attribute_options_collection_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_attribute_options_collection_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

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

    public function test_attribute_options_collection_ids_are_integers(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        $first = $response->json(0);

        expect($first['id'])->toBeInt();
    }

    // ── GET Single ────────────────────────────────────────────

    public function test_get_single_attribute_option_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstOptionId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

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

    public function test_single_attribute_option_exposes_translations_array(): void
    {
        $this->seedRequiredData();
        $id = $this->firstOptionId();

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
        expect($response->json())->toHaveKey('translations');
        expect($response->json('translations'))->toBeArray();
    }

    public function test_get_non_existent_attribute_option_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        $response->assertNotFound();
    }
}
