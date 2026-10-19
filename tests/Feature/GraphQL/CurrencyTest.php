<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class CurrencyTest extends GraphQLTestCase
{
    /**
     * Query all currencies — basic fields.
     */
    public function test_get_all_currencies(): void
    {
        $query = <<<'GQL'
            query allCurrency {
              currencies {
                edges {
                  node {
                    id
                    _id
                    code
                    name
                    symbol
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.currencies');
        $this->assertNotNull($data, 'currencies response is null');

        $edges = $data['edges'] ?? [];
        $this->assertNotEmpty($edges, 'There should be at least one currency');

        foreach ($edges as $edge) {
            $node = $edge['node'] ?? null;
            $this->assertNotNull($node, 'currency node is null');
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('_id', $node);
            $this->assertArrayHasKey('code', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('symbol', $node);

            $this->assertNotEmpty($node['code']);
            $this->assertNotEmpty($node['name']);
        }
    }

    /**
     * Query single currency by ID — full detail fields.
     */
    public function test_get_currency_by_id(): void
    {
        $iri = $this->getFirstCurrencyIri();

        $query = <<<'GQL'
            query getCurrencyByID($id: ID!) {
              currency(id: $id) {
                id
                _id
                code
                name
                symbol
                decimal
                groupSeparator
                decimalSeparator
                currencyPosition
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => $iri]);

        $response->assertSuccessful();

        $node = $response->json('data.currency');

        $this->assertNotNull($node, 'currency response is null');
        $this->assertSame($iri, $node['id']);
        $this->assertArrayHasKey('_id', $node);
        $this->assertArrayHasKey('code', $node);
        $this->assertArrayHasKey('name', $node);
        $this->assertArrayHasKey('symbol', $node);
        $this->assertArrayHasKey('decimal', $node);
        $this->assertArrayHasKey('groupSeparator', $node);
        $this->assertArrayHasKey('decimalSeparator', $node);
        $this->assertArrayHasKey('currencyPosition', $node);

        $this->assertNotEmpty($node['code']);
        $this->assertNotEmpty($node['name']);
    }

    /**
     * Invalid currency ID returns null.
     */
    public function test_invalid_currency_id_returns_null(): void
    {
        $query = <<<'GQL'
            query getCurrencyByID($id: ID!) {
              currency(id: $id) {
                id
                _id
                code
                name
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/currencies/99999']);

        $response->assertSuccessful();
        $this->assertNull($response->json('data.currency'));
    }

    /**
     * Ensure at least one currency exists and return its IRI.
     */
    private function getFirstCurrencyIri(): string
    {
        $id = DB::table('currencies')->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped('No currencies found in the database. Run Bagisto seeders.');
        }

        return '/api/shop/currencies/'.$id;
    }
}
