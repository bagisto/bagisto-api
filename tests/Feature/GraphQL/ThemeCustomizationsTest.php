<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

/**
 * Theme Customizations GraphQL API Test Cases
 *
 * Organized by test categories:
 * - Get Theme Customizations Basic
 * - Get Theme Customizations - Filtered by Type
 * - Get Theme Customizations - Complete Details
 * - Single Theme Customization by ID
 */
class ThemeCustomizationsTest extends GraphQLTestCase
{
    /**
     * Test: Query theme customizations - Basic
     */
    public function test_theme_customizations_basic(): void
    {
        $query = <<<'GQL'
            query themeCustomizations($first: Int, $after: String) {
              themeCustomizations(first: $first, after: $after) {
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

        $themeNode = $response->json('data.themeCustomizations.edges.0.node');

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

        expect($response->json('data.themeCustomizations.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.themeCustomizations.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'hasNextPage',
            'endCursor',
        ]);

        expect($response->json('data.themeCustomizations.totalCount'))->toBeInt();
    }

    /**
     * Test: Query theme customizations filtered by type
     */
    public function test_theme_customizations_filtered_by_type(): void
    {
        $query = <<<'GQL'
            query themeCustomizations($type: String) {
              themeCustomizations(type: $type) {
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
                      themeCustomizationId
                      locale
                      options
                    }
                    translations {
                      edges {
                        node {
                          id
                          _id
                          themeCustomizationId
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

        $themeNode = $response->json('data.themeCustomizations.edges.0.node');

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
            'themeCustomizationId',
            'locale',
            'options',
        ]);

        $translationNode = $response->json('data.themeCustomizations.edges.0.node.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'themeCustomizationId',
            'locale',
            'options',
        ]);

        $translationsPageInfo = $response->json('data.themeCustomizations.edges.0.node.translations.pageInfo');
        expect($translationsPageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.themeCustomizations.edges.0.node.translations.totalCount'))->toBeInt();

        $pageInfo = $response->json('data.themeCustomizations.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.themeCustomizations.totalCount'))->toBeInt();
    }

    /**
     * Test: Query theme customizations with complete details
     */
    public function test_theme_customizations_complete_details(): void
    {
        $query = <<<'GQL'
            query themeCustomizations($first: Int, $after: String, $last: Int, $before: String, $type: String) {
              themeCustomizations(first: $first, after: $after, last: $last, before: $before, type: $type) {
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
                      themeCustomizationId
                      locale
                      options
                    }
                    translations {
                      edges {
                        cursor
                        node {
                          id
                          _id
                          themeCustomizationId
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

        $themeNode = $response->json('data.themeCustomizations.edges.0.node');

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
            'themeCustomizationId',
            'locale',
            'options',
        ]);

        $translationsNode = $response->json('data.themeCustomizations.edges.0.node.translations.edges.0.node');
        expect($translationsNode)->toHaveKeys([
            'id',
            '_id',
            'themeCustomizationId',
            'locale',
            'options',
        ]);

        expect($response->json('data.themeCustomizations.edges.0.node.translations.edges.0.cursor'))->toBeString();

        $translationsPageInfo = $response->json('data.themeCustomizations.edges.0.node.translations.pageInfo');
        expect($translationsPageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.themeCustomizations.edges.0.node.translations.totalCount'))->toBeInt();

        expect($response->json('data.themeCustomizations.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.themeCustomizations.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.themeCustomizations.totalCount'))->toBeInt();
    }

    /**
     * Test: Query single theme customization by ID - Basic
     */
    public function test_get_theme_customization_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getThemeCustomisation($id: ID!) {
              themeCustomization(id: $id) {
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

        $response = $this->graphQL($query, ['id' => '/api/theme_customizations/1']);

        $response->assertOk();

        $theme = $response->json('data.themeCustomization');

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
     * Test: Query single theme customization by numeric ID
     */
    public function test_get_theme_customization_by_numeric_id(): void
    {
        $query = <<<'GQL'
            query getThemeCustomisation($id: ID!) {
              themeCustomization(id: $id) {
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

        $response = $this->graphQL($query, ['id' => '2']);

        $response->assertOk();

        $theme = $response->json('data.themeCustomization');

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
     * Test: Query single theme customization with complete details
     */
    public function test_get_theme_customization_complete_details(): void
    {
        $query = <<<'GQL'
            query getThemeCustomisation($id: ID!) {
              themeCustomization(id: $id) {
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
                  themeCustomizationId
                  locale
                  options
                }
                translations {
                  edges {
                    cursor
                    node {
                      id
                      _id
                      themeCustomizationId
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

        $response = $this->graphQL($query, ['id' => '1']);

        $response->assertOk();

        $theme = $response->json('data.themeCustomization');

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
            'themeCustomizationId',
            'locale',
            'options',
        ]);

        $translationNode = $response->json('data.themeCustomization.translations.edges.0.node');
        expect($translationNode)->toHaveKeys([
            'id',
            '_id',
            'themeCustomizationId',
            'locale',
            'options',
        ]);

        expect($response->json('data.themeCustomization.translations.edges.0.cursor'))->toBeString();

        $pageInfo = $response->json('data.themeCustomization.translations.pageInfo');
        expect($pageInfo)->toHaveKeys([
            'endCursor',
            'startCursor',
            'hasNextPage',
            'hasPreviousPage',
        ]);

        expect($response->json('data.themeCustomization.translations.totalCount'))->toBeInt();
    }
}
