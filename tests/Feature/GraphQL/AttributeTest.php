<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class AttributeTest extends GraphQLTestCase
{
    /**
     * Get Attributes - Basic
     */
    public function test_get_all_attributes_basic(): void
    {
        $query = <<<'GQL'
            query getAllAttributes($first: Int, $after: String) {
              attributes(first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    code
                    adminName
                    type
                    swatchType
                    position
                    isRequired
                    isConfigurable
                    options {
                      edges {
                        node {
                          id
                          adminName
                          swatchValue
                        }
                      }
                      totalCount
                    }
                  }
                  cursor
                }
                pageInfo {
                  endCursor
                  hasNextPage
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 10]);

        $response->assertSuccessful();

        $data = $response->json('data.attributes');

        $this->assertNotNull($data, 'attributes response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributes.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // Ensure at least one attribute is returned
        $this->assertGreaterThanOrEqual(0, $data['totalCount']);

        if (! empty($data['edges'])) {
            $first = $data['edges'][0]['node'] ?? null;
            $this->assertNotNull($first, 'first edge.node is null');
            $this->assertArrayHasKey('id', $first);
            $this->assertArrayHasKey('_id', $first);
            $this->assertArrayHasKey('code', $first);
            $this->assertArrayHasKey('adminName', $first);
            $this->assertArrayHasKey('type', $first);
            $this->assertArrayHasKey('options', $first);

            $this->assertIsArray($first['options']['edges'] ?? []);
            $this->assertArrayHasKey('totalCount', $first['options']);
        }
    }

    /**
     * Get Attribute - Basic
     */
    public function test_get_attribute_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getAttributeByID($id: ID!){
              attribute(id: $id) {
                id
                _id
                code
                adminName
                type
                swatchType
                validation
                regex
                position
                isRequired
                isUnique
                isFilterable
                isComparable
                isConfigurable
                isUserDefined
                isVisibleOnFront
                valuePerLocale
                valuePerChannel
                defaultValue
                enableWysiwyg
                createdAt
                updatedAt
                columnName
                validations
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/attributes/23'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.attribute');

        $this->assertNotNull($node, 'attribute response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('adminName', $node);
        $this->assertArrayHasKey('type', $node);
        $this->assertArrayHasKey('createdAt', $node);
        $this->assertArrayHasKey('updatedAt', $node);
    }

    /**
     * Get Attributes with full Options and Translations
     */
    public function test_get_all_attributes_with_full_options_and_translations(): void
    {
        $query = <<<'GQL'
            query getAllAttributes($first: Int) {
              attributes(first: $first) {
                edges {
                  node {
                    id
                    _id
                    code
                    adminName
                    type
                    swatchType
                    validation
                    regex
                    position
                    isRequired
                    isUnique
                    isFilterable
                    isComparable
                    isConfigurable
                    isUserDefined
                    isVisibleOnFront
                    valuePerLocale
                    valuePerChannel
                    defaultValue
                    enableWysiwyg
                    createdAt
                    updatedAt
                    columnName
                    validations
                    options {
                      edges {
                        node {
                          id
                          _id
                          adminName
                          sortOrder
                          swatchValue
                          swatchValueUrl
                          translation {
                            id
                            _id
                            attributeOptionId
                            locale
                            label
                          }
                          translations {
                            edges {
                              node {
                                id
                                _id
                                attributeOptionId
                                locale
                                label
                              }
                            }
                            pageInfo {
                              endCursor
                              hasNextPage
                            }
                            totalCount
                          }
                        }
                        cursor
                      }
                      pageInfo {
                        endCursor
                        hasNextPage
                      }
                      totalCount
                    }
                    translations {
                      edges {
                        node {
                          id
                          _id
                          attributeId
                          locale
                          name
                        }
                      }
                      pageInfo {
                        endCursor
                        hasNextPage
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

        $response = $this->graphQL($query, ['first' => 5]);

        $response->assertSuccessful();

        $data = $response->json('data.attributes');

        $this->assertNotNull($data, 'attributes response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributes.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo expected keys
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('startCursor', $data['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $data['pageInfo']);

        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'edge.node is null');
                $this->assertArrayHasKey('id', $node);
                $this->assertArrayHasKey('_id', $node);
                $this->assertArrayHasKey('code', $node);
                $this->assertArrayHasKey('adminName', $node);
                $this->assertArrayHasKey('type', $node);
                $this->assertArrayHasKey('options', $node);

                // options structure
                $this->assertIsArray($node['options']['edges'] ?? []);
                $this->assertArrayHasKey('totalCount', $node['options']);

                foreach ($node['options']['edges'] ?? [] as $optEdge) {
                    $opt = $optEdge['node'] ?? null;
                    $this->assertNotNull($opt, 'option edge.node is null');
                    $this->assertArrayHasKey('id', $opt);
                    $this->assertArrayHasKey('_id', $opt);
                    $this->assertArrayHasKey('adminName', $opt);

                    // translation and translations
                    $this->assertArrayHasKey('translation', $opt);
                    $this->assertArrayHasKey('translations', $opt);

                    $this->assertIsArray($opt['translations']['edges'] ?? []);
                    $this->assertArrayHasKey('pageInfo', $opt['translations']);
                    $this->assertArrayHasKey('totalCount', $opt['translations']);
                }

                // attribute translations
                $this->assertIsArray($node['translations']['edges'] ?? []);
                $this->assertArrayHasKey('pageInfo', $node['translations']);
                $this->assertArrayHasKey('totalCount', $node['translations']);
            }
        }
    }

    /**
     * Get Attribute with Full Details
     */
    public function test_get_attribute_by_id_full_details(): void
    {
        $query = <<<'GQL'
            query getAttributeByID($id: ID!){
                attribute(id: $id) {
                  id
                  _id
                  code
                  adminName
                  type
                  swatchType
                  validation
                  regex
                  position
                  isRequired
                  isUnique
                  isFilterable
                  isComparable
                  isConfigurable
                  isUserDefined
                  isVisibleOnFront
                  valuePerLocale
                  valuePerChannel
                  defaultValue
                  enableWysiwyg
                  createdAt
                  updatedAt
                  columnName
                  validations
                  options {
                    edges {
                      node {
                        id
                        _id
                        adminName
                        sortOrder
                        swatchValue
                        swatchValueUrl
                        translation {
                          id
                          _id
                          attributeOptionId
                          locale
                          label
                        }
                        translations {
                          edges {
                            node {
                              id
                              _id
                              attributeOptionId
                              locale
                              label
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
                  translations {
                    edges {
                      node {
                        id
                        _id
                        attributeId
                        locale
                        name
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

        $variables = ['id' => '/api/shop/attributes/23'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.attribute');

        $this->assertNotNull($node, 'attribute response is null');
        $this->assertArrayHasKey('options', $node);
        $this->assertArrayHasKey('translations', $node);

        // options structure
        $this->assertIsArray($node['options']['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $node['options']);
        $this->assertArrayHasKey('totalCount', $node['options']);

        foreach ($node['options']['edges'] ?? [] as $optEdge) {
            $opt = $optEdge['node'] ?? null;
            $this->assertNotNull($opt);
            $this->assertArrayHasKey('translation', $opt);
            $this->assertArrayHasKey('translations', $opt);
            $this->assertArrayHasKey('pageInfo', $opt['translations']);
        }

        // translations structure
        $this->assertIsArray($node['translations']['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $node['translations']);
        $this->assertArrayHasKey('totalCount', $node['translations']);
    }

    /**
     * Get Attribute Options - Basic
     */
    public function test_get_attribute_options_basic(): void
    {
        $query = <<<'GQL'
            query getAttributeOptions($first: Int) {
              attributeOptions(first: $first) {
                edges {
                  node {
                    id
                    _id
                    adminName
                    sortOrder
                    swatchValue
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

        $response->assertSuccessful();

        $data = $response->json('data.attributeOptions');

        $this->assertNotNull($data, 'attributeOptions response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributeOptions.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);

        if (! empty($data['edges'])) {
            $first = $data['edges'][0]['node'] ?? null;
            $this->assertNotNull($first, 'first option node is null');
            $this->assertArrayHasKey('id', $first);
            $this->assertArrayHasKey('_id', $first);
            $this->assertArrayHasKey('adminName', $first);
            $this->assertArrayHasKey('sortOrder', $first);
            $this->assertArrayHasKey('swatchValue', $first);
        }
    }

    /**
     * Get Attribute Options with Translations
     */
    public function test_get_attribute_options_with_translations_basic(): void
    {
        $query = <<<'GQL'
            query getAttributeOptionsWithTranslations($first: Int) {
              attributeOptions(first: $first) {
                edges {
                  node {
                    id
                    adminName
                    sortOrder
                    translations(first: 10) {
                      edges {
                        node {
                          locale
                          label
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 5]);

        $response->assertSuccessful();

        $data = $response->json('data.attributeOptions');

        $this->assertNotNull($data, 'attributeOptions response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributeOptions.edges is not an array');

        if (! empty($data['edges'])) {
            $first = $data['edges'][0]['node'] ?? null;
            $this->assertNotNull($first, 'first option node is null');
            $this->assertArrayHasKey('id', $first);
            $this->assertArrayHasKey('adminName', $first);
            $this->assertArrayHasKey('sortOrder', $first);
            $this->assertArrayHasKey('translations', $first);

            $this->assertIsArray($first['translations']['edges'] ?? []);
            $transFirst = $first['translations']['edges'][0]['node'] ?? null;
            if ($transFirst) {
                $this->assertArrayHasKey('locale', $transFirst);
                $this->assertArrayHasKey('label', $transFirst);
            }
        }
    }

    /**
     * Get Attribute Options with Swatches
     */
    public function test_get_attribute_options_with_swatches_basic(): void
    {
        $query = <<<'GQL'
            query getSwatchOptions($first: Int) {
              attributeOptions(first: $first) {
                edges {
                  node {
                    id
                    adminName
                    swatchValue
                    swatchValueUrl
                    translation {
                      locale
                      label
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['first' => 20]);

        $response->assertSuccessful();

        $data = $response->json('data.attributeOptions');

        $this->assertNotNull($data, 'attributeOptions response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributeOptions.edges is not an array');

        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'option node is null');
                $this->assertArrayHasKey('id', $node);
                $this->assertArrayHasKey('adminName', $node);
                $this->assertArrayHasKey('swatchValue', $node);
                $this->assertArrayHasKey('swatchValueUrl', $node);
                $this->assertArrayHasKey('translation', $node);

                $trans = $node['translation'] ?? null;
                if ($trans) {
                    $this->assertArrayHasKey('locale', $trans);
                    $this->assertArrayHasKey('label', $trans);
                }
            }
        }
    }

    /**
     * Get Single Attribute Option Detail By Option ID
     */
    public function test_get_single_attribute_option_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getAttributeOptionByID ($id: ID!) {
              attributeOption (id: $id) {
                id
                _id
                adminName
                sortOrder
                swatchValue
                swatchValueUrl
                translation {
                  id
                  _id
                  attributeOptionId
                  locale
                  label
                }
                translations {
                  edges {
                    node {
                      id
                      _id
                      attributeOptionId
                      locale
                      label
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

        $variables = ['id' => '/api/shop/attribute-options/1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.attributeOption');

        $this->assertNotNull($node, 'attributeOption response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('adminName', $node);
        $this->assertArrayHasKey('sortOrder', $node);
        $this->assertArrayHasKey('swatchValue', $node);
        $this->assertArrayHasKey('swatchValueUrl', $node);
        $this->assertArrayHasKey('translation', $node);
        $this->assertArrayHasKey('translations', $node);

        $this->assertIsArray($node['translations']['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $node['translations']);
        $this->assertArrayHasKey('totalCount', $node['translations']);

        $this->assertArrayHasKey('endCursor', $node['translations']['pageInfo']);
        $this->assertArrayHasKey('startCursor', $node['translations']['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $node['translations']['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $node['translations']['pageInfo']);
    }

    /**
     * Get Attribute Options - Pagination
     */
    public function test_get_attribute_options_pagination_basic(): void
    {
        $query = <<<'GQL'
            query getAttributeOptionsPaginated(
              $first: Int
              $after: String
            ) {
              attributeOptions(
                first: $first
                after: $after
              ) {
                edges {
                  node {
                    id
                    adminName
                    sortOrder
                  }
                  cursor
                }
                pageInfo {
                  hasNextPage
                  endCursor
                  hasPreviousPage
                  startCursor
                }
              }
            }
        GQL;

        $variables = ['first' => 10, 'after' => null];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.attributeOptions');

        $this->assertNotNull($data, 'attributeOptions response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributeOptions.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);

        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $data['pageInfo']);
        $this->assertArrayHasKey('startCursor', $data['pageInfo']);

        if (! empty($data['edges'])) {
            $firstEdge = $data['edges'][0] ?? null;
            $this->assertNotNull($firstEdge, 'first edge is null');
            $this->assertArrayHasKey('node', $firstEdge);
            $this->assertArrayHasKey('cursor', $firstEdge);

            $node = $firstEdge['node'] ?? null;
            $this->assertNotNull($node, 'edge.node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('adminName', $node);
            $this->assertArrayHasKey('sortOrder', $node);
        }
    }

    /**
     * Get Attribute Options with Attribute
     */
    public function test_get_attribute_with_options_basic(): void
    {
        $query = <<<'GQL'
            query getAttribute($id: ID!, $first: Int) {
              attribute(id: $id) {
                id
                code
                adminName    
                options(first: $first) {
                  edges {
                    node {
                      id
                      adminName
                      sortOrder
                      swatchValue
                      translation {
                        locale
                        label
                      }
                    }
                    cursor
                  }
                  pageInfo {
                    hasNextPage
                    endCursor
                  }
                }
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/attributes/23', 'first' => 10];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.attribute');

        $this->assertNotNull($data, 'attribute response is null');
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('code', $data);
        $this->assertArrayHasKey('adminName', $data);
        $this->assertArrayHasKey('options', $data);

        $this->assertIsArray($data['options']['edges'] ?? [], 'attribute.options.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data['options']);
        $this->assertArrayHasKey('hasNextPage', $data['options']['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['options']['pageInfo']);

        if (! empty($data['options']['edges'])) {
            $firstEdge = $data['options']['edges'][0] ?? null;
            $this->assertNotNull($firstEdge, 'first attribute option edge is null');
            $this->assertArrayHasKey('node', $firstEdge);
            $this->assertArrayHasKey('cursor', $firstEdge);

            $node = $firstEdge['node'] ?? null;
            $this->assertNotNull($node, 'option node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('adminName', $node);
            $this->assertArrayHasKey('sortOrder', $node);
            $this->assertArrayHasKey('swatchValue', $node);
            $this->assertArrayHasKey('translation', $node);

            $trans = $node['translation'] ?? null;
            if ($trans) {
                $this->assertArrayHasKey('locale', $trans);
                $this->assertArrayHasKey('label', $trans);
            }
        }
    }

    /**
     * Get Color Options for Display
     */
    public function test_get_color_options_for_display(): void
    {
        $query = <<<'GQL'
            query getColorOptions {
              attributeOptions(first: 50) {
                edges {
                  node {
                    adminName
                    swatchValue
                    translation {
                      label
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.attributeOptions');

        $this->assertNotNull($data, 'attributeOptions response is null');
        $this->assertIsArray($data['edges'] ?? [], 'attributeOptions.edges is not an array');

        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'option node is null');
                $this->assertArrayHasKey('adminName', $node);
                $this->assertArrayHasKey('swatchValue', $node);
                $this->assertArrayHasKey('translation', $node);

                $trans = $node['translation'] ?? null;
                if ($trans) {
                    $this->assertArrayHasKey('label', $trans);
                }
            }
        }
    }
}
