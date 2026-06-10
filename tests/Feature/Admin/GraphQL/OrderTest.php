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

    /**
     * The adminOrders query must expose the listing filter args (declared via
     * extraArgs). Without them the query 500s with "Unknown argument". This
     * verifies they are accepted and the status filter actually narrows.
     */
    public function test_orders_query_accepts_filter_args(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminOrders(first: 5, status: "processing", date_range: "this_year", grand_total_from: 0) {
                edges { node { id status } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        foreach ($response->json('data.adminOrders.edges') as $edge) {
            expect($edge['node']['status'])->toBe('processing');
        }
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
