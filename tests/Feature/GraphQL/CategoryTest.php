<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;

class CategoryTest extends GraphQLTestCase
{
    /**
     * Test: Query treeCategories with parentId.
     */
    public function test_tree_categories(): void
    {
        $query = <<<'GQL'
            query treeCategories {
              treeCategories(parentId: 1) {
                id
                _id
                position
                status
                translation {
                  name
                  slug
                  urlPath
                }
                children(first: 100) {
                  edges {
                    node {
                      id
                      _id
                      position
                      status
                      translation {
                        name
                        slug
                        urlPath
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();

        $category = $response->json('data.treeCategories.0');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
            'children',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);

        $childNode = $response->json(
            'data.treeCategories.0.children.edges.0.node'
        );

        expect($childNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
        ]);

        expect($childNode['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);
    }

    /**
     * Test: Query treeCategories with complete details.
     */
    public function test_tree_categories_complete_details(): void
    {
        $query = <<<'GQL'
            query treeCategories {
              treeCategories(parentId: 1) {
                id
                _id
                position
                status
                logoPath
                displayMode
                _lft
                _rgt
                additional
                bannerPath
                createdAt
                updatedAt
                url
                logoUrl
                bannerUrl
                translation {
                  name
                  slug
                  urlPath
                }
                children(first: 100) {
                  edges {
                    node {
                      id
                      _id
                      position
                      status
                      translation {
                        name
                        slug
                        urlPath
                      }
                    }
                  }
                }
                translations(first: 1) {
                  edges {
                    node {
                      id
                      _id
                      categoryId
                      name
                      slug
                      urlPath
                      description
                      metaTitle
                      metaDescription
                      metaKeywords
                      localeId
                      locale
                    }
                    cursor
                  }
                  pageInfo {
                    endCursor
                    startCursor
                    hasNextPage
                    hasPreviousPage
                  }
                  totalCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertOk();

        $category = $response->json('data.treeCategories.0');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'logoPath',
            'displayMode',
            '_lft',
            '_rgt',
            'additional',
            'bannerPath',
            'createdAt',
            'updatedAt',
            'url',
            'logoUrl',
            'bannerUrl',
            'translation',
            'children',
            'translations',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);

        $childNode = $response->json('data.treeCategories.0.children.edges.0.node');
        expect($childNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
        ]);

        expect($childNode['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);

        $translationNode = $response->json('data.treeCategories.0.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'categoryId',
            'name',
            'slug',
            'urlPath',
            'description',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'localeId',
            'locale',
        ]);

        $pageInfo = $response->json('data.treeCategories.0.translations.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.treeCategories.0.translations.totalCount'))->toBeInt();
    }

    /**
     * Test: Query categories with complete details.
     */
    public function test_get_categories_complete_details(): void
    {
        $query = <<<'GQL'
            query getCategories($first: Int, $after: String) {
              categories(first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    position
                    status
                    logoPath
                    displayMode
                    _lft
                    _rgt
                    additional
                    bannerPath
                    createdAt
                    updatedAt
                    url
                    logoUrl
                    bannerUrl
                    translation {
                      name
                      slug
                      urlPath
                    }
                    children(first: 100) {
                      edges {
                        node {
                          id
                          _id
                          position
                          status
                          translation {
                            name
                            slug
                            urlPath
                          }
                        }
                      }
                    }
                    translations(first: 1) {
                      edges {
                        node {
                          id
                          _id
                          categoryId
                          name
                          slug
                          urlPath
                          description
                          metaTitle
                          metaDescription
                          metaKeywords
                          localeId
                          locale
                        }
                        cursor
                      }
                      pageInfo {
                        endCursor
                        startCursor
                        hasNextPage
                        hasPreviousPage
                      }
                      totalCount
                    }
                  }
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 10]);

        $response->assertOk();

        $categoryNode = $response->json('data.categories.edges.0.node');

        expect($categoryNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'logoPath',
            'displayMode',
            '_lft',
            '_rgt',
            'additional',
            'bannerPath',
            'createdAt',
            'updatedAt',
            'url',
            'logoUrl',
            'bannerUrl',
            'translation',
            'children',
            'translations',
        ]);

        expect($categoryNode['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);

        $childNode = $response->json('data.categories.edges.0.node.children.edges.0.node');
        expect($childNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
        ]);

        $translationNode = $response->json('data.categories.edges.0.node.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'categoryId',
            'name',
            'slug',
            'urlPath',
            'description',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'localeId',
            'locale',
        ]);

        $pageInfo = $response->json('data.categories.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.categories.totalCount'))->toBeInt();
    }

    /**
     * Test: Query categories with basic details.
     */
    public function test_get_categories_basic_details(): void
    {
        $query = <<<'GQL'
            query getCategories($first: Int, $after: String) {
              categories(first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    position
                    status
                    translation {
                      name
                      slug
                      urlPath
                    }
                  }
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 10]);

        $response->assertOk();

        $categoryNode = $response->json('data.categories.edges.0.node');

        expect($categoryNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
        ]);

        expect($categoryNode['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);

        $pageInfo = $response->json('data.categories.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'hasNextPage',
            'endCursor',
        ]);
    }

    /**
     * An undecodable cursor is reported to the client as an invalid cursor.
     */
    public function test_get_categories_with_an_invalid_cursor_reports_it(): void
    {
        $query = <<<'GQL'
            query getCategories($first: Int, $after: String) {
              categories(first: $first, after: $after) {
                edges { cursor node { _id } }
                pageInfo { endCursor hasNextPage }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 5, 'after' => 'invalid-cursor']);

        $response->assertOk();
        expect($response->json('errors.0.message'))->toBe('Cursor invalid-cursor is invalid');
        expect($response->json('data.categories'))->toBeNull();
    }

    /**
     * Test: Query categories with cursor pagination.
     */
    public function test_get_categories_cursor_pagination(): void
    {
        $query = <<<'GQL'
            query getCategories($first: Int, $after: String, $last: Int, $before: String) {
              categories(first: $first, after: $after, last: $last, before: $before) {
                edges {
                  node {
                    id
                    _id
                    position
                    translation {
                      name
                      slug
                    }
                    status
                    children {
                      totalCount
                    }
                  }
                  cursor
                }
                pageInfo {
                  endCursor
                  startCursor
                  hasNextPage
                  hasPreviousPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, [
            'first' => 10,
            'after' => null,
        ]);

        $response->assertOk();

        $categoryNode = $response->json('data.categories.edges.0.node');

        expect($categoryNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'translation',
            'status',
            'children',
        ]);

        expect($categoryNode['translation'])->toHaveKeys([
            'name',
            'slug',
        ]);

        expect($categoryNode['children'])->toHaveKeys(['totalCount']);

        expect($response->json('data.categories.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.categories.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.categories.totalCount'))->toBeInt();
    }

    /**
     * Test: Query categories with child categories.
     */
    public function test_get_categories_with_child_categories(): void
    {
        $query = <<<'GQL'
            query getCategories($first: Int) {
              categories(first: $first) {
                edges {
                  node {
                    id
                    _id
                    position
                    translation {
                      name
                      slug
                    }
                    children(first: 50) {
                      edges {
                        node {
                          id
                          _id
                          position
                          translation {
                            name
                            slug
                          }
                        }
                      }
                      totalCount
                    }
                  }
                  cursor
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 5]);

        $response->assertOk();

        $categoryNode = $response->json('data.categories.edges.0.node');

        expect($categoryNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'translation',
            'children',
        ]);

        expect($categoryNode['translation'])->toHaveKeys([
            'name',
            'slug',
        ]);

        $childNode = $response->json('data.categories.edges.0.node.children.edges.0.node');
        expect($childNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'translation',
        ]);

        expect($childNode['translation'])->toHaveKeys([
            'name',
            'slug',
        ]);

        expect($response->json('data.categories.edges.0.node.children.totalCount'))->toBeInt();
        expect($response->json('data.categories.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.categories.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'hasNextPage',
            'endCursor',
        ]);

        expect($response->json('data.categories.totalCount'))->toBeInt();
    }

    /**
     * Test: Query single category by ID - Basic.
     */
    public function test_get_category_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                position
                status
                translation {
                  name
                  slug
                  urlPath
                  description
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/categories/1']);

        $response->assertOk();

        $category = $response->json('data.category');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
            'description',
        ]);
    }

    /**
     * Test: Query single category by numeric ID.
     */
    public function test_get_category_by_numeric_id(): void
    {
        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                position
                status
                translation {
                  name
                  slug
                  urlPath
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '1']);

        $response->assertOk();

        $category = $response->json('data.category');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'position',
            'status',
            'translation',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
        ]);
    }

    /**
     * Test: Query single category with complete details.
     */
    public function test_get_category_complete_details(): void
    {
        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                position
                logoPath
                logoUrl
                status
                displayMode
                _lft
                _rgt
                additional
                bannerPath
                bannerUrl
                translation {
                  id
                  _id
                  categoryId
                  name
                  slug
                  urlPath
                  description
                  metaTitle
                  metaDescription
                  metaKeywords
                  localeId
                  locale
                }
                translations {
                  edges {
                    node {
                      id
                      _id
                      categoryId
                      name
                      slug
                      urlPath
                      description
                      metaTitle
                      metaDescription
                      metaKeywords
                      localeId
                      locale
                    }
                    cursor
                  }
                  pageInfo {
                    endCursor
                    startCursor
                    hasNextPage
                    hasPreviousPage
                  }
                  totalCount
                }
                createdAt
                updatedAt
                url
                children {
                  edges {
                    node {
                      id
                      _id
                      position
                      logoUrl
                      status
                      translation {
                        name
                        slug
                      }
                    }
                  }
                  pageInfo {
                    hasNextPage
                    endCursor
                    startCursor
                    hasPreviousPage
                  }
                  totalCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/categories/1']);

        $response->assertOk();

        $category = $response->json('data.category');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'position',
            'logoPath',
            'logoUrl',
            'status',
            'displayMode',
            '_lft',
            '_rgt',
            'additional',
            'bannerPath',
            'bannerUrl',
            'translation',
            'translations',
            'createdAt',
            'updatedAt',
            'url',
            'children',
        ]);

        expect($category['translation'])->toHaveKeys([
            'id',
            '_id',
            'categoryId',
            'name',
            'slug',
            'urlPath',
            'description',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'localeId',
            'locale',
        ]);

        $translationNode = $response->json('data.category.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'categoryId',
            'name',
            'slug',
            'urlPath',
            'description',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'localeId',
            'locale',
        ]);

        $translationsPageInfo = $response->json('data.category.translations.pageInfo');
        expect($translationsPageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.category.translations.totalCount'))->toBeInt();

        $childNode = $response->json('data.category.children.edges.0.node');
        expect($childNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'logoUrl',
            'status',
            'translation',
        ]);

        expect($childNode['translation'])->toHaveKeys([
            'name',
            'slug',
        ]);

        $childrenPageInfo = $response->json('data.category.children.pageInfo');
        expect($childrenPageInfo)->toHaveKeys([
            'hasNextPage',
            'endCursor',
            'startCursor',
            'hasPreviousPage',
        ]);

        expect($response->json('data.category.children.totalCount'))->toBeInt();
    }

    /**
     * Test: Query single category with SEO data.
     */
    public function test_get_category_with_seo_data(): void
    {
        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                url
                translation {
                  name
                  slug
                  urlPath
                  description
                  metaTitle
                  metaDescription
                  metaKeywords
                }
                translations {
                  edges {
                    node {
                      name
                      slug
                      metaTitle
                      metaDescription
                      metaKeywords
                      locale
                    }
                  }
                  totalCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/categories/1']);

        $response->assertOk();

        $category = $response->json('data.category');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'url',
            'translation',
            'translations',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
            'urlPath',
            'description',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
        ]);

        $seoNode = $response->json('data.category.translations.edges.0.node');
        expect($seoNode)->toHaveKeys([
            'name',
            'slug',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'locale',
        ]);

        expect($response->json('data.category.translations.totalCount'))->toBeInt();
    }

    /**
     * Test: Query single category with display settings.
     */
    public function test_get_category_with_display_settings(): void
    {
        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                position
                logoPath
                logoUrl
                bannerPath
                bannerUrl
                displayMode
                status
                _lft
                _rgt
                translation {
                  name
                  slug
                  description
                }
                children {
                  totalCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/categories/1']);

        $response->assertOk();

        $category = $response->json('data.category');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'position',
            'logoPath',
            'logoUrl',
            'bannerPath',
            'bannerUrl',
            'displayMode',
            'status',
            '_lft',
            '_rgt',
            'translation',
            'children',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
            'description',
        ]);

        expect($category['children'])->toHaveKeys(['totalCount']);
    }

    /**
     * Test: Query single category with all children.
     */
    public function test_get_category_with_all_children(): void
    {
        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                translation {
                  name
                  slug
                }
                url
                children {
                  edges {
                    node {
                      id
                      _id
                      position
                      translation {
                        name
                        slug
                      }
                      logoUrl
                      status
                      children {
                        totalCount
                      }
                    }
                  }
                  pageInfo {
                    hasNextPage
                    endCursor
                  }
                  totalCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/categories/1']);

        $response->assertOk();

        $category = $response->json('data.category');

        expect($category)->toHaveKeys([
            'id',
            '_id',
            'translation',
            'url',
            'children',
        ]);

        expect($category['translation'])->toHaveKeys([
            'name',
            'slug',
        ]);

        $childNode = $response->json('data.category.children.edges.0.node');
        expect($childNode)->toHaveKeys([
            'id',
            '_id',
            'position',
            'translation',
            'logoUrl',
            'status',
            'children',
        ]);

        expect($childNode['translation'])->toHaveKeys([
            'name',
            'slug',
        ]);

        expect($childNode['children'])->toHaveKeys(['totalCount']);

        $pageInfo = $response->json('data.category.children.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'hasNextPage',
            'endCursor',
        ]);

        expect($response->json('data.category.children.totalCount'))->toBeInt();
    }

    /**
     * Test filterable attributes returns pivot data.
     */
    public function test_filterable_attributes_returns_pivot_data(): void
    {
        $this->seedRequiredData();

        $category = Category::query()->first();
        $attributeIds = Attribute::query()
            ->whereIn('code', ['price', 'color', 'size', 'brand'])
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (
            count($attributeIds) < 1
            || ! $category
        ) {
            $this->markTestSkipped('Required category/attributes not seeded.');
        }

        \DB::table('category_filterable_attributes')->where('category_id', $category->id)->delete();
        foreach ($attributeIds as $attrId) {
            \DB::table('category_filterable_attributes')->insert([
                'category_id' => $category->id,
                'attribute_id' => $attrId,
            ]);
        }

        $query = <<<'GQL'
            query getCategoryByID($id: ID!) {
              category(id: $id) {
                id
                _id
                filterableAttributes {
                  edges {
                    node {
                      id
                      _id
                      code
                      adminName
                    }
                  }
                  totalCount
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/categories/'.$category->id]);
        $response->assertOk();

        $edges = $response->json('data.category.filterableAttributes.edges') ?? [];
        $returnedIds = array_map(fn ($e) => (int) $e['node']['_id'], $edges);
        sort($returnedIds);

        $expected = $attributeIds;
        sort($expected);

        expect($returnedIds)->toBe($expected);
        expect($response->json('data.category.filterableAttributes.totalCount'))->toBe(count($expected));
    }

    /**
     * Test: disabled categories are not returned by the collection or the item query.
     */
    public function test_disabled_category_is_not_returned(): void
    {
        $category = Category::factory()->create([
            'parent_id' => 1,
            'position' => 99,
            'status' => 0,
        ]);

        CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Disabled Category',
            'slug' => 'disabled-category',
        ]);

        $collection = $this->graphQL('query { categories(first: 50) { edges { node { _id status } } } }');

        $collection->assertOk();

        $ids = array_column(array_column($collection->json('data.categories.edges') ?? [], 'node'), '_id');

        expect($ids)->not->toContain($category->id);

        $item = $this->graphQL(
            'query getCategory($id: ID!) { category(id: $id) { _id } }',
            ['id' => '/api/shop/categories/'.$category->id]
        );

        expect($item->json('data.category'))->toBeNull();
    }

    /**
     * Test: a non-positive page size is clamped instead of removing the limit.
     */
    public function test_negative_first_is_clamped(): void
    {
        $response = $this->graphQL('query { categories(first: -1) { edges { node { _id } } } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(count($response->json('data.categories.edges')))->toBeLessThanOrEqual(1);
    }

    /**
     * The categoryTrees query returns the current channel's root category.
     */
    public function test_category_trees_returns_the_channel_root_category(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              categoryTrees {
                id
              }
            }
        GQL);

        $response->assertOk();

        $ids = collect($response->json('data.categoryTrees'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        expect($response->json('errors'))->toBeNull()
            ->and($ids)->toBe([(int) core()->getCurrentChannel()->root_category_id]);
    }

    /**
     * The categoryAttributeFilters query lists every filterable attribute when no category is given.
     */
    public function test_category_attribute_filters_lists_filterable_attributes(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              categoryAttributeFilters(first: 100) {
                totalCount
                edges {
                  node {
                    _id
                    code
                    isFilterable
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $expectedIds = Attribute::query()->where('is_filterable', 1)->orderBy('id')->pluck('id')->all();
        $returnedIds = collect($response->json('data.categoryAttributeFilters.edges'))->pluck('node._id')->all();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.categoryAttributeFilters.totalCount'))->toBe(count($expectedIds))
            ->and($returnedIds)->toBe($expectedIds)
            ->and(collect($response->json('data.categoryAttributeFilters.edges'))->pluck('node.isFilterable')->unique()->all())->toBe([true]);
    }

    /**
     * The categoryAttributeFilters query narrows the list to a category's own filterable attributes by its slug.
     */
    public function test_category_attribute_filters_by_category_slug(): void
    {
        $category = Category::factory()->create([
            'parent_id' => 1,
            'status' => 1,
        ]);

        CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Filter Slug Category',
            'slug' => 'filter-slug-category-'.$category->id,
        ]);

        $attribute = Attribute::query()->where('code', 'color')->first() ?? Attribute::query()->first();

        DB::table('category_filterable_attributes')->insert([
            'category_id' => $category->id,
            'attribute_id' => $attribute->id,
        ]);

        $response = $this->graphQL(<<<'GQL'
            query filters($slug: String) {
              categoryAttributeFilters(categorySlug: $slug) {
                totalCount
                edges {
                  node {
                    _id
                    code
                  }
                }
              }
            }
        GQL, ['slug' => 'filter-slug-category-'.$category->id]);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.categoryAttributeFilters.totalCount'))->toBe(1)
            ->and($response->json('data.categoryAttributeFilters.edges.0.node._id'))->toBe($attribute->id)
            ->and($response->json('data.categoryAttributeFilters.edges.0.node.code'))->toBe($attribute->code);
    }

    /**
     * The categoryAttributeFilters query rejects a category slug of the wrong type.
     */
    public function test_category_attribute_filters_rejects_a_non_string_slug(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              categoryAttributeFilters(categorySlug: 123) {
                totalCount
              }
            }
        GQL);

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.categoryAttributeFilters'))->toBeNull();
    }

    /**
     * Set up the test - seed required category data.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRequiredData();

        $parentCategory = Category::find(1);

        if ($parentCategory) {
            $parentCategory->children()->delete();

            $childCategory = Category::factory()->create([
                'parent_id' => $parentCategory->id,
                'position' => 1,
                'status' => 1,
            ]);

            CategoryTranslation::factory()->create([
                'category_id' => $childCategory->id,
                'locale' => 'en',
                'name' => 'Test Child Category',
                'slug' => 'test-child-category',
            ]);

            $grandchildCategory = Category::factory()->create([
                'parent_id' => $childCategory->id,
                'position' => 1,
                'status' => 1,
            ]);

            CategoryTranslation::factory()->create([
                'category_id' => $grandchildCategory->id,
                'locale' => 'en',
                'name' => 'Test Grandchild Category',
                'slug' => 'test-grandchild-category',
            ]);

            if ($parentCategory->translations()->count() === 0) {
                CategoryTranslation::factory()->create([
                    'category_id' => $parentCategory->id,
                    'locale' => 'en',
                    'name' => 'Root Category',
                    'slug' => 'root-category',
                ]);
            }
        }
    }

    /**
     * Last without before returns the final categories of the collection, with matching cursors.
     */
    public function test_categories_last_returns_the_final_rows(): void
    {
        foreach ([97, 98] as $position) {
            Category::factory()->create(['parent_id' => 1, 'position' => $position, 'status' => 1]);
        }

        $ids = Category::query()->where('status', 1)->orderBy('id')->pluck('id')->all();

        $response = $this->graphQL('query { categories(last: 2) { edges { node { _id } cursor } totalCount } }');

        $response->assertOk();

        $edges = $response->json('data.categories.edges');

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.categories.totalCount'))->toBe(count($ids))
            ->and(array_map(fn ($edge) => (int) $edge['node']['_id'], $edges))->toBe(array_slice($ids, -2))
            ->and(array_column($edges, 'cursor'))->toBe([base64_encode((string) (count($ids) - 2)), base64_encode((string) (count($ids) - 1))]);
    }

    /**
     * A zero page size returns an empty page rather than a row.
     */
    public function test_categories_zero_first_returns_an_empty_page(): void
    {
        $response = $this->graphQL('query { categories(first: 0) { edges { node { _id } } totalCount } }');

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.categories.edges'))->toBe([]);
    }
}
