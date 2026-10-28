<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Models\Section;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

/**
 * Sections GraphQL API Test Cases
 *
 * Organized by test categories:
 * - Get Sections Basic
 * - Get Sections - Filtered by Type
 * - Get Sections - Complete Details
 * - Single Section by ID
 */
class SectionsTest extends GraphQLTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // BACKWARD COMPATIBILITY: remove when the minimum supported core is 2.4.10.
        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    private function existingSectionId(): int
    {
        $id = Section::query()
            ->whereHas('translations')
            ->orderBy('id')
            ->value('id');

        if (! $id) {
            $this->markTestSkipped('No section with a translation found. seedRequiredData must run first.');
        }

        return (int) $id;
    }

    /**
     * Test: Query sections - Basic
     */
    public function test_theme_customizations_basic(): void
    {
        $query = <<<'GQL'
            query sections($first: Int, $after: String) {
              sections(first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    type
                    name
                    status
                    themeCode
                    sortOrder
                    translation {
                      locale
                      options
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

        $themeNode = $response->json('data.sections.edges.0.node');

        expect($themeNode)->toHaveKeys([
            'id',
            '_id',
            'type',
            'name',
            'status',
            'themeCode',
            'sortOrder',
            'translation',
        ]);

        expect($themeNode['translation'])->toHaveKeys([
            'locale',
            'options',
        ]);

        expect($response->json('data.sections.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.sections.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'hasNextPage',
            'endCursor',
        ]);

        expect($response->json('data.sections.totalCount'))->toBeInt();
    }

    /**
     * Test: Query sections filtered by type
     */
    public function test_theme_customizations_filtered_by_type(): void
    {
        $query = <<<'GQL'
            query sections($type: String) {
              sections(type: $type) {
                edges {
                  node {
                    id
                    _id
                    type
                    name
                    status
                    themeCode
                    sortOrder
                    translation {
                      id
                      _id
                      sectionId
                      locale
                      options
                    }
                    translations {
                      edges {
                        node {
                          id
                          _id
                          sectionId
                          locale
                          options
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

        $response = $this->graphQL($query, ['type' => 'footer_links']);

        $response->assertOk();

        $themeNode = $response->json('data.sections.edges.0.node');

        expect($themeNode)->toHaveKeys([
            'id',
            '_id',
            'type',
            'name',
            'status',
            'themeCode',
            'sortOrder',
            'translation',
            'translations',
        ]);

        expect($themeNode['translation'])->toHaveKeys([
            'id',
            '_id',
            'sectionId',
            'locale',
            'options',
        ]);

        $translationNode = $response->json('data.sections.edges.0.node.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'sectionId',
            'locale',
            'options',
        ]);

        $translationsPageInfo = $response->json('data.sections.edges.0.node.translations.pageInfo');
        expect($translationsPageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.sections.edges.0.node.translations.totalCount'))->toBeInt();

        $pageInfo = $response->json('data.sections.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.sections.totalCount'))->toBeInt();
    }

    /**
     * Test: Query sections with complete details
     */
    public function test_theme_customizations_complete_details(): void
    {
        $query = <<<'GQL'
            query sections($first: Int, $after: String, $last: Int, $before: String, $type: String) {
              sections(first: $first, after: $after, last: $last, before: $before, type: $type) {
                edges {
                  node {
                    id
                    _id
                    themeCode
                    type
                    name
                    sortOrder
                    status
                    channelId
                    createdAt
                    updatedAt
                    translation {
                      id
                      _id
                      sectionId
                      locale
                      options
                    }
                    translations {
                      edges {
                        cursor
                        node {
                          id
                          _id
                          sectionId
                          locale
                          options
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

        $response = $this->graphQL($query, ['first' => 3]);

        $response->assertOk();

        $themeNode = $response->json('data.sections.edges.0.node');

        expect($themeNode)->toHaveKeys([
            'id',
            '_id',
            'themeCode',
            'type',
            'name',
            'sortOrder',
            'status',
            'channelId',
            'createdAt',
            'updatedAt',
            'translation',
            'translations',
        ]);

        expect($themeNode['translation'])->toHaveKeys([
            'id',
            '_id',
            'sectionId',
            'locale',
            'options',
        ]);

        $translationsNode = $response->json('data.sections.edges.0.node.translations.edges.0.node');
        expect($translationsNode)->toHaveKeys([
            'id',
            '_id',
            'sectionId',
            'locale',
            'options',
        ]);

        expect($response->json('data.sections.edges.0.node.translations.edges.0.cursor'))->toBeString();

        $translationsPageInfo = $response->json('data.sections.edges.0.node.translations.pageInfo');
        expect($translationsPageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.sections.edges.0.node.translations.totalCount'))->toBeInt();

        expect($response->json('data.sections.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.sections.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.sections.totalCount'))->toBeInt();
    }

    /**
     * Test: Query single section by ID - Basic
     */
    public function test_get_theme_customization_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getSection($id: ID!) {
              section(id: $id) {
                id
                _id
                type
                name
                status
                themeCode
                translation {
                  locale
                  options
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/theme_customizations/'.$this->existingSectionId()]);

        $response->assertOk();

        $theme = $response->json('data.section');

        expect($theme)->toHaveKeys([
            'id',
            '_id',
            'type',
            'name',
            'status',
            'themeCode',
            'translation',
        ]);

        expect($theme['translation'])->toHaveKeys([
            'locale',
            'options',
        ]);
    }

    /**
     * Test: Query single section by numeric ID
     */
    public function test_get_theme_customization_by_numeric_id(): void
    {
        $query = <<<'GQL'
            query getSection($id: ID!) {
              section(id: $id) {
                id
                _id
                type
                name
                status
                themeCode
                sortOrder
                translation {
                  locale
                  options
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => (string) $this->existingSectionId()]);

        $response->assertOk();

        $theme = $response->json('data.section');

        expect($theme)->toHaveKeys([
            'id',
            '_id',
            'type',
            'name',
            'status',
            'themeCode',
            'sortOrder',
            'translation',
        ]);

        expect($theme['translation'])->toHaveKeys([
            'locale',
            'options',
        ]);
    }

    /**
     * Test: Query single section with complete details
     */
    public function test_get_theme_customization_complete_details(): void
    {
        $query = <<<'GQL'
            query getSection($id: ID!) {
              section(id: $id) {
                id
                _id
                themeCode
                type
                name
                sortOrder
                status
                channelId
                createdAt
                updatedAt
                translation {
                  id
                  _id
                  sectionId
                  locale
                  options
                }
                translations {
                  edges {
                    cursor
                    node {
                      id
                      _id
                      sectionId
                      locale
                      options
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
            }
        GQL;

        $response = $this->graphQL($query, ['id' => (string) $this->existingSectionId()]);

        $response->assertOk();

        $theme = $response->json('data.section');

        expect($theme)->toHaveKeys([
            'id',
            '_id',
            'themeCode',
            'type',
            'name',
            'sortOrder',
            'status',
            'channelId',
            'createdAt',
            'updatedAt',
            'translation',
            'translations',
        ]);

        expect($theme['translation'])->toHaveKeys([
            'id',
            '_id',
            'sectionId',
            'locale',
            'options',
        ]);

        $translationNode = $response->json('data.section.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'sectionId',
            'locale',
            'options',
        ]);

        expect($response->json('data.section.translations.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.section.translations.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.section.translations.totalCount'))->toBeInt();
    }
}
