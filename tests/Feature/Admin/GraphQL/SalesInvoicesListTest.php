<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SalesInvoicesListTest extends AdminApiTestCase
{
    public function test_query_returns_cursor_collection(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminInvoices(first: 5) {
                edges { node { id state } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminInvoices.edges'))->toBeArray();
        expect($response->json('data.adminInvoices.totalCount'))->toBeInt();
    }

    public function test_query_requires_authentication(): void
    {
        $query = 'query { adminInvoices(first: 1) { edges { node { id } } } }';
        $response = $this->adminGraphQL($query);
        expect($response->json('errors'))->not->toBeNull();
    }
}
