<?php

namespace Tests\Feature\BagistoApi\GraphQL;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Webkul\Core\Models\Locale;

class LocaleTest extends TestCase
{
    use DatabaseTransactions;

    private string $graphqlUrl = '/api/graphql';

    /**
     * Test: Query all locales collection
     */
    public function test_get_locales_collection(): void
    {
        $query = <<<'GQL'
            query getLocales {
              locales {
                edges {
                  cursor
                  node {
                    id
                    _id
                    code
                    name
                    direction
                    logoPath
                    logoUrl
                    createdAt
                    updatedAt
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

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'locales' => [
                    'edges' => [
                        '*' => [
                            'cursor',
                            'node' => [
                                'id',
                                '_id',
                                'code',
                                'name',
                                'direction',
                                'logoPath',
                                'logoUrl',
                                'createdAt',
                                'updatedAt',
                            ],
                        ],
                    ],
                    'pageInfo' => [
                        'endCursor',
                        'startCursor',
                        'hasNextPage',
                        'hasPreviousPage',
                    ],
                    'totalCount',
                ],
            ],
        ]);

        $data = $response->json('data.locales');
        expect($data['totalCount'])->toBe(Locale::count());
        expect($data['edges'])->not()->toBeEmpty();
    }

    /**
     * Test: Query single locale by ID
     */
    public function test_get_locale_by_id(): void
    {
        $locale = Locale::first();
        $localeId = "/api/shop/locales/{$locale->id}";

        $query = <<<GQL
            query getLocale {
              locale(id: "{$localeId}") {
                id
                _id
                code
                name
                direction
                logoPath
                logoUrl
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $data = $response->json('data.locale');

        expect($data['_id'])->toBe($locale->id);
        expect($data['code'])->toBe($locale->code);
        expect($data['name'])->toBe($locale->name);
        expect($data['direction'])->toBe($locale->direction);
    }

    /**
     * Test: Timestamps are returned in ISO8601 format
     */
    public function test_locale_timestamps_are_iso8601_format(): void
    {
        $query = <<<'GQL'
            query getLocales {
              locales(first: 1) {
                edges {
                  node {
                    code
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $locale = $response->json('data.locales.edges.0.node');

        // Verify ISO8601 format (should contain 'T' and 'Z' or timezone)
        expect($locale['createdAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
        expect($locale['updatedAt'])->toMatch('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');
    }

    /**
     * Test: Query locales with pagination (first)
     */
    public function test_locales_pagination_first(): void
    {
        $query = <<<'GQL'
            query getLocales {
              locales(first: 1) {
                edges {
                  node {
                    id
                    code
                  }
                }
                pageInfo {
                  hasNextPage
                }
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $data = $response->json('data.locales');

        expect($data['edges'])->toHaveCount(1);
    }

    /**
     * Test: Query locales with pagination (after cursor)
     */
    public function test_locales_pagination_after_cursor(): void
    {
        // Get first page
        $firstQuery = <<<'GQL'
            query getLocales {
              locales(first: 1) {
                edges {
                  cursor
                  node {
                    code
                  }
                }
              }
            }
        GQL;

        $firstResponse = $this->postJson($this->graphqlUrl, ['query' => $firstQuery]);
        $firstCursor = $firstResponse->json('data.locales.edges.0.cursor');

        // Get second page using cursor
        $secondQuery = <<<GQL
            query getLocales {
              locales(first: 1, after: "{$firstCursor}") {
                edges {
                  node {
                    code
                  }
                }
              }
            }
        GQL;

        $secondResponse = $this->postJson($this->graphqlUrl, ['query' => $secondQuery]);

        $secondResponse->assertOk();
    }

    /**
     * Test: Query locales with introspection (schema exploration)
     */
    public function test_locale_introspection_query(): void
    {
        $query = <<<'GQL'
            {
              __type(name: "Locale") {
                name
                kind
                fields {
                  name
                  type {
                    name
                    kind
                  }
                }
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $type = $response->json('data.__type');

        expect($type['name'])->toBe('Locale');
        expect($type['kind'])->toBe('OBJECT');
        
        $fieldNames = collect($type['fields'])->pluck('name')->toArray();
        expect($fieldNames)->toContain('id', 'code', 'name', 'direction', 'createdAt', 'updatedAt');
    }

    /**
     * Test: Query returns appropriate error for invalid ID
     */
    public function test_invalid_locale_id_returns_error(): void
    {
        $query = <<<'GQL'
            query getLocale {
              locale(id: "/api/shop/locales/99999") {
                id
                code
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        expect($response->json('data.locale'))->toBeNull();
    }

    /**
     * Test: Logo URL is properly formatted
     */
    public function test_locale_logo_url_format(): void
    {
        $locale = Locale::first();

        $query = <<<GQL
            query getLocale {
              locale(id: "/api/shop/locales/{$locale->id}") {
                logoUrl
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $logoUrl = $response->json('data.locale.logoUrl');

        // Should be either null or a valid URL
        if ($logoUrl) {
            expect($logoUrl)->toMatch('/http(s)?:\/\/.+/');
        }
    }

    /**
     * Test: Multiple fields can be queried together
     */
    public function test_query_multiple_locale_fields(): void
    {
        $query = <<<'GQL'
            query getLocales {
              locales(first: 5) {
                edges {
                  node {
                    id
                    _id
                    code
                    name
                    direction
                    logoPath
                    logoUrl
                    createdAt
                    updatedAt
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $data = $response->json('data.locales');

        expect($data['totalCount'])->toBeGreaterThan(0);
        
        $node = $data['edges'][0]['node'];
        expect($node)->toHaveKeys(['id', '_id', 'code', 'name', 'direction', 'logoPath', 'logoUrl', 'createdAt', 'updatedAt']);
    }

    /**
     * Test: Direction field validation (should be 'ltr' or 'rtl')
     */
    public function test_locale_direction_values(): void
    {
        $query = <<<'GQL'
            query getLocales {
              locales {
                edges {
                  node {
                    direction
                  }
                }
              }
            }
        GQL;

        $response = $this->postJson($this->graphqlUrl, ['query' => $query]);

        $response->assertOk();
        $edges = $response->json('data.locales.edges');

        foreach ($edges as $edge) {
            expect($edge['node']['direction'])->toBeIn(['ltr', 'rtl']);
        }
    }
}
