<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CountryTest extends GraphQLTestCase
{
    /**
     * Get Country By ID - Basic
     */
    public function test_get_country_by_id_basic(): void
    {
        $query = <<<'GQL'
            query getSingleCountry($id: ID!) {
              country(id: $id) {
                id
                _id
                code
                name
              }
            }
        GQL;

        $variables = ['id' => '106'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.country');

        $this->assertNotNull($node, 'country response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('name', $node);
    }

    /**
     * Get Country with States
     */
    public function test_get_country_with_states(): void
    {
        $query = <<<'GQL'
            query getSingleCountry($id: ID!) {
              country(id: $id) {
                id
                _id
                code
                name
                states {
                  edges {
                    node {
                      id
                      _id
                      code
                      defaultName
                      countryId
                      countryCode
                      translations {
                        edges {
                          node {
                            id
                            locale
                            defaultName
                          }
                        }
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

        $variables = ['id' => '1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.country');

        $this->assertNotNull($node, 'country response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('name', $node);

        // Verify states
        $this->assertArrayHasKey('states', $node);
        $states = $node['states'];
        $this->assertIsArray($states['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $states);
        $this->assertArrayHasKey('totalCount', $states);

        // pageInfo assertions
        $this->assertArrayHasKey('hasNextPage', $states['pageInfo']);
        $this->assertArrayHasKey('endCursor', $states['pageInfo']);

        // Verify state edges
        foreach ($states['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $state = $edge['node'] ?? null;
            $this->assertNotNull($state, 'state node is null');
            $this->assertArrayHasKey('id', $state);
            $this->assertArrayHasKey('_id', $state);
            $this->assertArrayHasKey('code', $state);
            $this->assertArrayHasKey('defaultName', $state);
            $this->assertArrayHasKey('countryId', $state);
            $this->assertArrayHasKey('countryCode', $state);

            // Verify translations
            $this->assertArrayHasKey('translations', $state);
            $translations = $state['translations'];
            $this->assertIsArray($translations['edges'] ?? []);

            foreach ($translations['edges'] ?? [] as $tEdge) {
                $t = $tEdge['node'] ?? null;
                $this->assertNotNull($t, 'translation node is null');
                $this->assertArrayHasKey('id', $t);
                $this->assertArrayHasKey('locale', $t);
                $this->assertArrayHasKey('defaultName', $t);
            }
        }
    }

    /**
     * Get Country with Translations
     */
    public function test_get_country_with_translations(): void
    {
        $query = <<<'GQL'
            query getSingleCountry($id: ID!) {
              country(id: $id) {
                id
                _id
                code
                name
                translations {
                  edges {
                    node {
                      id
                      _id
                      locale
                      name
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
            }
        GQL;

        $variables = ['id' => '1'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.country');

        $this->assertNotNull($node, 'country response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('name', $node);

        // Verify translations
        $this->assertArrayHasKey('translations', $node);
        $translations = $node['translations'];
        $this->assertIsArray($translations['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $translations);
        $this->assertArrayHasKey('totalCount', $translations);

        // pageInfo assertions
        $this->assertArrayHasKey('hasNextPage', $translations['pageInfo']);
        $this->assertArrayHasKey('endCursor', $translations['pageInfo']);

        // Verify translation edges
        foreach ($translations['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertArrayHasKey('cursor', $edge);

            $t = $edge['node'] ?? null;
            $this->assertNotNull($t, 'translation node is null');
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('_id', $t);
            $this->assertArrayHasKey('locale', $t);
            $this->assertArrayHasKey('name', $t);
        }
    }

    /**
     * Get Country - Complete Details
     */
    public function test_get_country_complete_details(): void
    {
        $query = <<<'GQL'
            query getSingleCountry($id: ID!) {
              country(id: $id) {
                id
                _id
                code
                name
                states {
                  edges {
                    node {
                      id
                      _id
                      code
                      defaultName
                      countryId
                      countryCode
                      translations {
                        edges {
                          node {
                            id
                            locale
                            defaultName
                          }
                        }
                      }
                    }
                  }
                  pageInfo {
                    hasNextPage
                    endCursor
                  }
                  totalCount
                }
                translations {
                  edges {
                    node {
                      id
                      _id
                      locale
                      name
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

        $variables = ['id' => '106'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.country');

        $this->assertNotNull($node, 'country response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('name', $node);

        // Verify states
        $this->assertArrayHasKey('states', $node);
        $states = $node['states'];
        $this->assertIsArray($states['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $states);
        $this->assertArrayHasKey('totalCount', $states);

        // pageInfo assertions for states
        $this->assertArrayHasKey('hasNextPage', $states['pageInfo']);
        $this->assertArrayHasKey('endCursor', $states['pageInfo']);

        // Verify state edges
        foreach ($states['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $state = $edge['node'] ?? null;
            $this->assertNotNull($state, 'state node is null');
            $this->assertArrayHasKey('id', $state);
            $this->assertArrayHasKey('_id', $state);
            $this->assertArrayHasKey('code', $state);
            $this->assertArrayHasKey('defaultName', $state);
            $this->assertArrayHasKey('countryId', $state);
            $this->assertArrayHasKey('countryCode', $state);

            // Verify state translations
            $this->assertArrayHasKey('translations', $state);
            $stateTranslations = $state['translations'];
            $this->assertIsArray($stateTranslations['edges'] ?? []);

            foreach ($stateTranslations['edges'] ?? [] as $tEdge) {
                $t = $tEdge['node'] ?? null;
                $this->assertNotNull($t, 'state translation node is null');
                $this->assertArrayHasKey('id', $t);
                $this->assertArrayHasKey('locale', $t);
                $this->assertArrayHasKey('defaultName', $t);
            }
        }

        // Verify country translations
        $this->assertArrayHasKey('translations', $node);
        $countryTranslations = $node['translations'];
        $this->assertIsArray($countryTranslations['edges'] ?? []);
        $this->assertArrayHasKey('pageInfo', $countryTranslations);
        $this->assertArrayHasKey('totalCount', $countryTranslations);

        // pageInfo assertions for translations
        $this->assertArrayHasKey('hasNextPage', $countryTranslations['pageInfo']);
        $this->assertArrayHasKey('endCursor', $countryTranslations['pageInfo']);

        // Verify country translation edges
        foreach ($countryTranslations['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $t = $edge['node'] ?? null;
            $this->assertNotNull($t, 'country translation node is null');
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('_id', $t);
            $this->assertArrayHasKey('locale', $t);
            $this->assertArrayHasKey('name', $t);
        }
    }

    /**
     * Get Countries - Basic
     */
    public function test_get_countries_basic(): void
    {
        $query = <<<'GQL'
            query getCountries {
              countries {
                edges {
                  node {
                    id
                    _id
                    code
                    name
                  }
                }
                pageInfo {
                  hasNextPage
                  endCursor
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.countries');

        $this->assertNotNull($data, 'countries response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countries.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo assertions
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);

        // Verify country edges
        if (! empty($data['edges'])) {
            foreach ($data['edges'] as $edge) {
                $this->assertArrayHasKey('node', $edge);

                $node = $edge['node'] ?? null;
                $this->assertNotNull($node, 'country node is null');
                $this->assertArrayHasKey('id', $node);
                $this->assertArrayHasKey('_id', $node);
                $this->assertArrayHasKey('code', $node);
                $this->assertArrayHasKey('name', $node);
            }
        }
    }

    /**
     * Get Countries with States
     */
    public function test_get_countries_with_states(): void
    {
        $query = <<<'GQL'
            query countries {
              countries {
                edges {
                  node {
                    id
                    _id
                    code
                    name
                    states {
                      edges {
                        node {
                          id
                          _id
                          code
                          defaultName
                          countryId
                          countryCode
                          translation {
                            id
                            locale
                            defaultName
                          }
                        }
                      }
                      pageInfo {
                        hasNextPage
                        endCursor
                      }
                      totalCount
                    }
                    translations {
                      edges {
                        node {
                          id
                          locale
                          name
                        }
                      }
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
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.countries');

        $this->assertNotNull($data, 'countries response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countries.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo assertions
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);

        // Verify country edges
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $node = $edge['node'] ?? null;
            $this->assertNotNull($node, 'country node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('_id', $node);
            $this->assertArrayHasKey('code', $node);
            $this->assertArrayHasKey('name', $node);

            // Verify states
            $this->assertArrayHasKey('states', $node);
            $states = $node['states'];
            $this->assertIsArray($states['edges'] ?? []);
            $this->assertArrayHasKey('pageInfo', $states);
            $this->assertArrayHasKey('totalCount', $states);

            // pageInfo assertions for states
            $this->assertArrayHasKey('hasNextPage', $states['pageInfo']);
            $this->assertArrayHasKey('endCursor', $states['pageInfo']);

            // Verify state edges
            foreach ($states['edges'] ?? [] as $stateEdge) {
                $this->assertArrayHasKey('node', $stateEdge);

                $state = $stateEdge['node'] ?? null;
                $this->assertNotNull($state, 'state node is null');
                $this->assertArrayHasKey('id', $state);
                $this->assertArrayHasKey('_id', $state);
                $this->assertArrayHasKey('code', $state);
                $this->assertArrayHasKey('defaultName', $state);
                $this->assertArrayHasKey('countryId', $state);
                $this->assertArrayHasKey('countryCode', $state);

                // Verify state translation (singular)
                $this->assertArrayHasKey('translation', $state);
                $translation = $state['translation'];
                if ($translation) {
                    $this->assertArrayHasKey('id', $translation);
                    $this->assertArrayHasKey('locale', $translation);
                    $this->assertArrayHasKey('defaultName', $translation);
                }
            }

            // Verify country translations
            $this->assertArrayHasKey('translations', $node);
            $translations = $node['translations'];
            $this->assertIsArray($translations['edges'] ?? []);
            $this->assertArrayHasKey('totalCount', $translations);

            // Verify translation edges
            foreach ($translations['edges'] ?? [] as $tEdge) {
                $t = $tEdge['node'] ?? null;
                $this->assertNotNull($t, 'translation node is null');
                $this->assertArrayHasKey('id', $t);
                $this->assertArrayHasKey('locale', $t);
                $this->assertArrayHasKey('name', $t);
            }
        }
    }

    /**
     * Get Countries with Pagination
     */
    public function test_get_countries_with_pagination(): void
    {
        $query = <<<'GQL'
            query countries($first: Int, $after: String) {
              countries(first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    code
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
        GQL;

        $variables = ['first' => 10, 'after' => null];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.countries');

        $this->assertNotNull($data, 'countries response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countries.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo assertions
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('startCursor', $data['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $data['pageInfo']);

        // Verify country edges with cursor
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertArrayHasKey('cursor', $edge);

            $node = $edge['node'] ?? null;
            $this->assertNotNull($node, 'country node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('_id', $node);
            $this->assertArrayHasKey('code', $node);
            $this->assertArrayHasKey('name', $node);
        }
    }

    /**
     * Get Countries with All Translations
     */
    public function test_get_countries_with_all_translations(): void
    {
        $query = <<<'GQL'
            query countries {
              countries {
                edges {
                  node {
                    id
                    _id
                    code
                    translations {
                      edges {
                        node {
                          id
                          locale
                          name
                        }
                      }
                      totalCount
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.countries');

        $this->assertNotNull($data, 'countries response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countries.edges is not an array');
        $this->assertArrayHasKey('totalCount', $data);

        // Verify country edges
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $node = $edge['node'] ?? null;
            $this->assertNotNull($node, 'country node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('_id', $node);
            $this->assertArrayHasKey('code', $node);

            // Verify translations
            $this->assertArrayHasKey('translations', $node);
            $translations = $node['translations'];
            $this->assertIsArray($translations['edges'] ?? []);
            $this->assertArrayHasKey('totalCount', $translations);

            // Verify translation edges
            foreach ($translations['edges'] ?? [] as $tEdge) {
                $t = $tEdge['node'] ?? null;
                $this->assertNotNull($t, 'translation node is null');
                $this->assertArrayHasKey('id', $t);
                $this->assertArrayHasKey('locale', $t);
                $this->assertArrayHasKey('name', $t);
            }
        }
    }

    /**
     * Get Countries for address form
     */
    public function test_get_countries_for_address_form(): void
    {
        $query = <<<'GQL'
            query countries {
              countries {
                edges {
                  node {
                    id
                    _id
                    code
                    name
                    states {
                      edges {
                        node {
                          id
                          _id
                          code
                          defaultName
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

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.countries');

        $this->assertNotNull($data, 'countries response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countries.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo assertions
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);

        // Verify country edges with cursor
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertArrayHasKey('cursor', $edge);

            $node = $edge['node'] ?? null;
            $this->assertNotNull($node, 'country node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('_id', $node);
            $this->assertArrayHasKey('code', $node);
            $this->assertArrayHasKey('name', $node);

            // Verify states
            $this->assertArrayHasKey('states', $node);
            $states = $node['states'];
            $this->assertIsArray($states['edges'] ?? []);
            $this->assertArrayHasKey('totalCount', $states);

            // Verify state edges
            foreach ($states['edges'] ?? [] as $stateEdge) {
                $this->assertArrayHasKey('node', $stateEdge);

                $state = $stateEdge['node'] ?? null;
                $this->assertNotNull($state, 'state node is null');
                $this->assertArrayHasKey('id', $state);
                $this->assertArrayHasKey('_id', $state);
                $this->assertArrayHasKey('code', $state);
                $this->assertArrayHasKey('defaultName', $state);
            }
        }
    }

    /**
     * Get Country States - Basic
     */
    public function test_get_country_states_basic(): void
    {
        $query = <<<'GQL'
            query getCountryStates($countryId: Int!) {
              countryStates(countryId: $countryId) {
                edges {
                  node {
                    id
                    _id
                    code
                    defaultName
                    countryId
                    countryCode
                  }
                }
                totalCount
              }
            }
        GQL;

        $variables = ['countryId' => 16];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.countryStates');

        $this->assertNotNull($data, 'countryStates response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countryStates.edges is not an array');
        $this->assertArrayHasKey('totalCount', $data);

        // Verify state edges
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $state = $edge['node'] ?? null;
            $this->assertNotNull($state, 'state node is null');
            $this->assertArrayHasKey('id', $state);
            $this->assertArrayHasKey('_id', $state);
            $this->assertArrayHasKey('code', $state);
            $this->assertArrayHasKey('defaultName', $state);
            $this->assertArrayHasKey('countryId', $state);
            $this->assertArrayHasKey('countryCode', $state);
        }
    }

    /**
     * Get Country States with Translations
     */
    public function test_get_country_states_with_translations(): void
    {
        $query = <<<'GQL'
            query getCountryStates($countryId: Int!) {
              countryStates(countryId: $countryId) {
                edges {
                  node {
                    id
                    _id
                    code
                    defaultName
                    countryId
                    countryCode
                    translations {
                      edges {
                        node {
                          id
                          locale
                          defaultName
                        }
                      }
                      totalCount
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $variables = ['countryId' => 16];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.countryStates');

        $this->assertNotNull($data, 'countryStates response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countryStates.edges is not an array');
        $this->assertArrayHasKey('totalCount', $data);

        // Verify state edges
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $state = $edge['node'] ?? null;
            $this->assertNotNull($state, 'state node is null');
            $this->assertArrayHasKey('id', $state);
            $this->assertArrayHasKey('_id', $state);
            $this->assertArrayHasKey('code', $state);
            $this->assertArrayHasKey('defaultName', $state);
            $this->assertArrayHasKey('countryId', $state);
            $this->assertArrayHasKey('countryCode', $state);

            // Verify translations
            $this->assertArrayHasKey('translations', $state);
            $translations = $state['translations'];
            $this->assertIsArray($translations['edges'] ?? []);
            $this->assertArrayHasKey('totalCount', $translations);

            // Verify translation edges
            foreach ($translations['edges'] ?? [] as $tEdge) {
                $t = $tEdge['node'] ?? null;
                $this->assertNotNull($t, 'translation node is null');
                $this->assertArrayHasKey('id', $t);
                $this->assertArrayHasKey('locale', $t);
                $this->assertArrayHasKey('defaultName', $t);
            }
        }
    }

    /**
     * Get Country States with Pagination
     */
    public function test_get_country_states_with_pagination(): void
    {
        $query = <<<'GQL'
            query getCountryStates($countryId: Int!, $first: Int, $after: String) {
              countryStates(countryId: $countryId, first: $first, after: $after) {
                edges {
                  node {
                    id
                    _id
                    code
                    defaultName
                    countryId
                    countryCode
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

        $variables = ['countryId' => 16, 'first' => 10, 'after' => null];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.countryStates');

        $this->assertNotNull($data, 'countryStates response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countryStates.edges is not an array');
        $this->assertArrayHasKey('pageInfo', $data);
        $this->assertArrayHasKey('totalCount', $data);

        // pageInfo assertions
        $this->assertArrayHasKey('endCursor', $data['pageInfo']);
        $this->assertArrayHasKey('startCursor', $data['pageInfo']);
        $this->assertArrayHasKey('hasNextPage', $data['pageInfo']);
        $this->assertArrayHasKey('hasPreviousPage', $data['pageInfo']);

        // Verify state edges with cursor
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertArrayHasKey('cursor', $edge);

            $state = $edge['node'] ?? null;
            $this->assertNotNull($state, 'state node is null');
            $this->assertArrayHasKey('id', $state);
            $this->assertArrayHasKey('_id', $state);
            $this->assertArrayHasKey('code', $state);
            $this->assertArrayHasKey('defaultName', $state);
            $this->assertArrayHasKey('countryId', $state);
            $this->assertArrayHasKey('countryCode', $state);
        }
    }

    /**
     * Get Country States for dropdown form
     */
    public function test_get_country_states_for_dropdown_form(): void
    {
        $query = <<<'GQL'
            query getCountryStates($countryId: Int!) {
              countryStates(countryId: $countryId) {
                edges {
                  node {
                    id
                    _id
                    code
                    defaultName
                  }
                }
                totalCount
              }
            }
        GQL;

        $variables = ['countryId' => 106];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $data = $response->json('data.countryStates');

        $this->assertNotNull($data, 'countryStates response is null');
        $this->assertIsArray($data['edges'] ?? [], 'countryStates.edges is not an array');
        $this->assertArrayHasKey('totalCount', $data);

        // Verify state edges
        foreach ($data['edges'] ?? [] as $edge) {
            $this->assertArrayHasKey('node', $edge);

            $state = $edge['node'] ?? null;
            $this->assertNotNull($state, 'state node is null');
            $this->assertArrayHasKey('id', $state);
            $this->assertArrayHasKey('_id', $state);
            $this->assertArrayHasKey('code', $state);
            $this->assertArrayHasKey('defaultName', $state);
        }
    }

    /**
     * Get Country State - Basic
     */
    public function test_get_country_state_basic(): void
    {
        $query = <<<'GQL'
            query getCountryState($id: ID!) {
              countryState(id: $id) {
                id
                _id
                code
                defaultName
                countryId
                countryCode
              }
            }
        GQL;

        $variables = ['id' => '16'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.countryState');

        $this->assertNotNull($node, 'countryState response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('defaultName', $node);
        $this->assertArrayHasKey('countryId', $node);
        $this->assertArrayHasKey('countryCode', $node);
    }

    /**
     * Get Country State with Translations
     */
    public function test_get_country_state_with_translations(): void
    {
        $query = <<<'GQL'
            query getCountryState($id: ID!) {
              countryState(id: $id) {
                id
                _id
                code
                defaultName
                countryId
                countryCode
                translations {
                  edges {
                    node {
                      id
                      locale
                      defaultName
                    }
                  }
                  totalCount
                }
              }
            }
        GQL;

        $variables = ['id' => '16'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.countryState');

        $this->assertNotNull($node, 'countryState response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('defaultName', $node);
        $this->assertArrayHasKey('countryId', $node);
        $this->assertArrayHasKey('countryCode', $node);

        // Verify translations
        $this->assertArrayHasKey('translations', $node);
        $translations = $node['translations'];
        $this->assertIsArray($translations['edges'] ?? []);
        $this->assertArrayHasKey('totalCount', $translations);

        // Verify translation edges
        foreach ($translations['edges'] ?? [] as $tEdge) {
            $t = $tEdge['node'] ?? null;
            $this->assertNotNull($t, 'translation node is null');
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('locale', $t);
            $this->assertArrayHasKey('defaultName', $t);
        }
    }

    /**
     * Get Country State for address validation
     */
    public function test_get_country_state_for_address_validation(): void
    {
        $query = <<<'GQL'
            query getCountryState($id: ID!) {
              countryState(id: $id) {
                id
                _id
                code
                defaultName
                countryId
                countryCode
              }
            }
        GQL;

        $variables = ['id' => '/api/shop/country-states/16'];

        $response = $this->graphQL($query, $variables);

        $response->assertSuccessful();

        $node = $response->json('data.countryState');

        $this->assertNotNull($node, 'countryState response is null');
        $this->assertArrayHasKey('id', $node);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('defaultName', $node);
        $this->assertArrayHasKey('countryId', $node);
        $this->assertArrayHasKey('countryCode', $node);
    }
}
