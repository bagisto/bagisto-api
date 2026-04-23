<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;

class CategoryTest extends RestApiTestCase
{
    private string $collectionUrl = '/api/shop/categories';

    private function itemUrl(int $id): string
    {
        return $this->collectionUrl.'/'.$id;
    }

    private function firstCategory(): Category
    {
        $category = Category::query()->orderBy('id')->first();

        if (! $category) {
            $this->markTestSkipped('No categories found. seedRequiredData must run first.');
        }

        return $category;
    }

    private function createCategoryWithTranslation(array $categoryAttrs = [], array $translationAttrs = []): Category
    {
        $category = Category::factory()->create(array_merge([
            'status'   => 1,
            'position' => 1,
        ], $categoryAttrs));

        CategoryTranslation::factory()->create(array_merge([
            'category_id' => $category->id,
            'locale'      => 'en',
            'name'        => 'Test Category '.$category->id,
            'slug'        => 'test-category-'.$category->id,
        ], $translationAttrs));

        return $category->fresh();
    }

    // ── GET Collection ────────────────────────────────────────

    public function test_get_categories_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
    }

    public function test_get_categories_returns_array(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_categories_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_categories_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('position');
        expect($first)->toHaveKey('status');
        expect($first)->toHaveKey('displayMode');
        expect($first)->toHaveKey('createdAt');
        expect($first)->toHaveKey('updatedAt');
    }

    public function test_categories_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    public function test_categories_includes_translation(): void
    {
        $this->seedRequiredData();
        $this->createCategoryWithTranslation(['parent_id' => null]);

        $items = $this->publicGet($this->collectionUrl)->json();

        $withTranslation = collect($items)->first(fn ($item) => ! empty($item['translation']));
        expect($withTranslation)->not()->toBeNull();
    }

    public function test_categories_translation_has_expected_fields(): void
    {
        $this->seedRequiredData();
        $category = $this->createCategoryWithTranslation(['parent_id' => null], [
            'name' => 'My Category',
            'slug' => 'my-category',
        ]);

        $body = $this->publicGet($this->itemUrl($category->id))->json();

        expect($body['translation'])->toHaveKey('name');
        expect($body['translation'])->toHaveKey('slug');
    }

    public function test_categories_includes_children(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->collectionUrl)->json(0);

        expect($first)->toHaveKey('children');
    }

    // ── Pagination ────────────────────────────────────────────

    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        if (Category::count() < 2) {
            $this->createCategoryWithTranslation(['parent_id' => null]);
        }

        $response = $this->publicGet($this->collectionUrl.'?itemsPerPage=1');

        $response->assertOk();
        expect(count($response->json()))->toBe(1);
    }

    public function test_page_parameter_returns_different_results(): void
    {
        $this->seedRequiredData();

        while (Category::count() < 2) {
            $this->createCategoryWithTranslation(['parent_id' => null]);
        }

        $page1 = $this->publicGet($this->collectionUrl.'?itemsPerPage=1&page=1')->json();
        $page2 = $this->publicGet($this->collectionUrl.'?itemsPerPage=1&page=2')->json();

        $id1 = $page1[0]['id'] ?? null;
        $id2 = $page2[0]['id'] ?? null;

        expect($id1)->not()->toBeNull();
        expect($id2)->not()->toBeNull();
        expect($id1)->not()->toBe($id2);
    }

    public function test_page_beyond_total_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->collectionUrl.'?itemsPerPage=10&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ── GET /categories/{id} ──────────────────────────────────

    public function test_get_single_category_returns_ok(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCategory()->id;

        $response = $this->publicGet($this->itemUrl($id));

        $response->assertOk();
    }

    public function test_get_single_category_returns_correct_id(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCategory()->id;

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body['id'])->toBe($id);
    }

    public function test_get_single_category_returns_expected_fields(): void
    {
        $this->seedRequiredData();
        $id = $this->firstCategory()->id;

        $body = $this->publicGet($this->itemUrl($id))->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('position');
        expect($body)->toHaveKey('status');
        expect($body)->toHaveKey('displayMode');
        expect($body)->toHaveKey('_lft');
        expect($body)->toHaveKey('_rgt');
        expect($body)->toHaveKey('createdAt');
        expect($body)->toHaveKey('updatedAt');
    }

    public function test_get_single_category_includes_translation(): void
    {
        $this->seedRequiredData();
        $category = $this->createCategoryWithTranslation([], [
            'name' => 'Translated Category',
            'slug' => 'translated-category',
        ]);

        $body = $this->publicGet($this->itemUrl($category->id))->json();

        expect($body)->toHaveKey('translation');
        expect($body['translation'])->not()->toBeNull();
        expect($body['translation']['name'])->toBe('Translated Category');
        expect($body['translation']['slug'])->toBe('translated-category');
    }

    public function test_get_single_category_includes_children(): void
    {
        $this->seedRequiredData();
        $parent = $this->createCategoryWithTranslation(['parent_id' => null]);
        $this->createCategoryWithTranslation(['parent_id' => $parent->id, 'status' => 1]);

        $body = $this->publicGet($this->itemUrl($parent->id))->json();

        expect($body)->toHaveKey('children');
        expect($body['children'])->toBeArray();
    }

    public function test_get_nonexistent_category_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->itemUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }
}
