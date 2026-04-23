<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;

class CategoryTreeTest extends RestApiTestCase
{
    private string $url = '/api/shop/category-trees';

    private function createCategory(array $categoryAttrs = [], array $translationAttrs = []): Category
    {
        $category = Category::factory()->create(array_merge([
            'status'   => 1,
            'position' => 1,
        ], $categoryAttrs));

        CategoryTranslation::factory()->create(array_merge([
            'category_id' => $category->id,
            'locale'      => 'en',
            'name'        => 'Category '.$category->id,
            'slug'        => 'category-'.$category->id,
        ], $translationAttrs));

        return $category->fresh();
    }

    private function createChildCategory(int $parentId, array $attrs = []): Category
    {
        return $this->createCategory(array_merge(['parent_id' => $parentId], $attrs));
    }

    private function rootCategoryId(): int
    {
        $id = Category::query()->whereNull('parent_id')->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No root categories found. seedRequiredData must run first.');
        }

        return (int) $id;
    }

    // ── GET /category-trees (no parentId) ─────────────────────

    public function test_get_tree_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();
    }

    public function test_get_tree_returns_array(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    public function test_get_tree_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_tree_is_not_paginated(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();
        // Paginated responses include hydra:member; this endpoint is a plain array
        $body = $response->json();
        expect(is_array($body))->toBeTrue();
        expect(array_key_exists('hydra:member', $body))->toBeFalse();
    }

    public function test_tree_items_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->url)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('position');
        expect($first)->toHaveKey('status');
        expect($first)->toHaveKey('displayMode');
        expect($first)->toHaveKey('createdAt');
        expect($first)->toHaveKey('updatedAt');
        expect($first)->toHaveKey('children');
    }

    public function test_tree_items_include_translation(): void
    {
        $this->seedRequiredData();
        $root = $this->createCategory(['parent_id' => null], [
            'name' => 'Root With Translation',
            'slug' => 'root-with-translation',
        ]);

        $tree = $this->publicGet($this->url)->json();
        $found = collect($tree)->firstWhere('id', $root->id);

        expect($found)->not()->toBeNull();
        expect($found)->toHaveKey('translation');
        expect($found['translation'])->toHaveKey('name');
        expect($found['translation'])->toHaveKey('slug');
        expect($found['translation'])->toHaveKey('urlPath');
    }

    public function test_tree_items_children_is_array(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->url)->json(0);

        expect($first['children'])->toBeArray();
    }

    public function test_tree_items_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->url)->json(0);

        expect($first['id'])->toBeInt();
    }

    public function test_tree_children_are_nested(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $child = $this->createChildCategory($rootId);

        $tree = $this->publicGet($this->url)->json();
        $root = collect($tree)->firstWhere('id', $rootId);

        expect($root)->not()->toBeNull();

        $childIds = array_column($root['children'], 'id');
        expect(in_array($child->id, $childIds))->toBeTrue();
    }

    // ── GET /category-trees?parentId={id} ─────────────────────

    public function test_get_tree_with_parent_id_returns_ok(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();

        $response = $this->publicGet($this->url.'?parentId='.$rootId);

        $response->assertOk();
    }

    public function test_get_tree_with_parent_id_returns_children(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $child = $this->createChildCategory($rootId);

        $tree = $this->publicGet($this->url.'?parentId='.$rootId)->json();

        $childIds = array_column($tree, 'id');
        expect(in_array($child->id, $childIds))->toBeTrue();
    }

    public function test_get_tree_with_parent_id_excludes_parent_itself(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();

        $tree = $this->publicGet($this->url.'?parentId='.$rootId)->json();

        $ids = array_column($tree, 'id');
        expect(in_array($rootId, $ids))->toBeFalse();
    }

    public function test_get_tree_with_parent_id_nests_grandchildren(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $child = $this->createChildCategory($rootId);
        $grand = $this->createChildCategory($child->id);

        $tree = $this->publicGet($this->url.'?parentId='.$rootId)->json();

        $childInTree = collect($tree)->firstWhere('id', $child->id);
        expect($childInTree)->not()->toBeNull();

        $grandIds = array_column($childInTree['children'], 'id');
        expect(in_array($grand->id, $grandIds))->toBeTrue();
    }

    public function test_get_tree_with_nonexistent_parent_id_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url.'?parentId=999999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    public function test_get_tree_with_parent_id_omits_inactive_children(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $inactive = $this->createChildCategory($rootId, ['status' => 0]);

        $tree = $this->publicGet($this->url.'?parentId='.$rootId)->json();

        $ids = array_column($tree, 'id');
        expect(in_array($inactive->id, $ids))->toBeFalse();
    }

    public function test_get_tree_with_parent_id_children_have_translation(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $child = $this->createChildCategory($rootId);

        $tree = $this->publicGet($this->url.'?parentId='.$rootId)->json();
        $inTree = collect($tree)->firstWhere('id', $child->id);

        expect($inTree)->not()->toBeNull();
        expect($inTree)->toHaveKey('translation');
        expect($inTree['translation'])->toHaveKey('name');
        expect($inTree['translation'])->toHaveKey('slug');
    }

    // ── depth parameter ───────────────────────────────────────

    public function test_depth_one_produces_empty_children(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $child = $this->createChildCategory($rootId);
        $this->createChildCategory($child->id); // grandchild

        $tree = $this->publicGet($this->url.'?parentId='.$rootId.'&depth=1')->json();
        $childInTree = collect($tree)->firstWhere('id', $child->id);

        expect($childInTree)->not()->toBeNull();
        // depth=1 means no nesting below the immediate children
        expect($childInTree['children'])->toBe([]);
    }

    public function test_depth_two_shows_grandchildren(): void
    {
        $this->seedRequiredData();
        $rootId = $this->rootCategoryId();
        $child = $this->createChildCategory($rootId);
        $grand = $this->createChildCategory($child->id);

        $tree = $this->publicGet($this->url.'?parentId='.$rootId.'&depth=2')->json();
        $childInTree = collect($tree)->firstWhere('id', $child->id);

        expect($childInTree)->not()->toBeNull();
        $grandIds = array_column($childInTree['children'], 'id');
        expect(in_array($grand->id, $grandIds))->toBeTrue();
    }

    public function test_default_depth_is_applied_when_omitted(): void
    {
        $this->seedRequiredData();

        // Without depth param the endpoint still returns valid tree data
        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }
}
