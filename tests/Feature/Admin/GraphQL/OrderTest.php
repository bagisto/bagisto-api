<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin Orders listing — adminOrders query
 * (native cursor pagination).
 */
class OrderTest extends AdminApiTestCase
{
    public function test_orders_query_returns_cursor_collection(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminOrders(first: 5) {
                edges { node { id incrementId status grandTotal customerEmail } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminOrders.edges'))->toBeArray();
        expect($response->json('data.adminOrders'))->toHaveKey('pageInfo');
        expect($response->json('data.adminOrders.totalCount'))->toBeInt();
    }

    public function test_first_limits_the_result_count(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminOrders(first: 3) {
                edges { node { id } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect(count($response->json('data.adminOrders.edges')))->toBeLessThanOrEqual(3);
    }

    public function test_orders_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query {
              adminOrders(first: 5) {
                edges { node { id } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }
}
