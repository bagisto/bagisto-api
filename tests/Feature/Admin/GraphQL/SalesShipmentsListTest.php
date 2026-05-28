<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SalesShipmentsListTest extends AdminApiTestCase
{
    public function test_query_returns_cursor_collection(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminShipments(first: 5) {
                edges { node { id } }
                pageInfo { hasNextPage }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminShipments.edges'))->toBeArray();
        expect($response->json('data.adminShipments.totalCount'))->toBeInt();
    }

    public function test_query_requires_authentication(): void
    {
        $query = 'query { adminShipments(first: 1) { edges { node { id } } } }';
        $response = $this->adminGraphQL($query);
        expect($response->json('errors'))->not->toBeNull();
    }
}
