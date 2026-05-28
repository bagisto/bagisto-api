<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SalesTransactionsTest extends AdminApiTestCase
{
    public function test_list_query(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminTransactions(first: 5) {
                edges { node { id status } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminTransactions.edges'))->toBeArray();
    }

    public function test_detail_query(): void
    {
        $admin = $this->createAdmin();

        $first = $this->adminGraphQL('query { adminTransactions(first: 1) { edges { node { _id } } } }', [], $admin)
            ->json('data.adminTransactions.edges');

        if (empty($first)) {
            $this->markTestSkipped('No transactions in DB.');
        }

        $id = $first[0]['node']['_id'];
        $query = 'query Q($id: ID!) { adminTransaction(id: $id) { id status } }';
        $response = $this->adminGraphQL($query, ['id' => "/api/admin/transactions/{$id}"], $admin);

        $response->assertOk();
        expect($response->json('data.adminTransaction.id'))->not->toBeNull();
    }

    public function test_requires_authentication(): void
    {
        $response = $this->adminGraphQL('query { adminTransactions(first: 1) { edges { node { id } } } }');
        expect($response->json('errors'))->not->toBeNull();
    }
}
