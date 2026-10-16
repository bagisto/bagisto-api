<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

/**
 * GraphQL coverage for the admin Orders listing — adminOrders query
 * (native cursor pagination).
 */
class OrderTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

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

    public function test_listing_resolves_non_null_tax_and_flag_scalars(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminOrders(first: 3) {
                edges {
                  node {
                    _id
                    isGift
                    shippingTaxAmount
                    baseShippingTaxAmount
                    shippingTaxRefunded
                    baseShippingTaxRefunded
                    subTotalInclTax
                    baseSubTotalInclTax
                    shippingAmountInclTax
                    baseShippingAmountInclTax
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
    }

    public function test_items_resolve_as_a_connection(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminOrders(first: 3) {
                edges {
                  node {
                    _id
                    incrementId
                    items {
                      edges {
                        node {
                          _id
                          sku
                          name
                          qtyOrdered
                          productImage
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminOrders.edges');
        expect($edges)->toBeArray();

        foreach ($edges as $edge) {
            expect($edge['node'])->toHaveKey('items');
            expect($edge['node']['items'])->toHaveKey('edges');

            foreach ($edge['node']['items']['edges'] as $itemEdge) {
                expect($itemEdge['node'])->toHaveKeys(['_id', 'sku', 'name', 'qtyOrdered', 'productImage']);
            }
        }
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

    public function test_orders_query_accepts_quick_search_and_customer_id(): void
    {
        $admin = $this->createAdmin();
        $order = $this->bootstrapAdminOrder('pending', false);

        $query = <<<'GQL'
            query Orders($query: String, $customerId: Int) {
              adminOrders(first: 50, query: $query, customerId: $customerId) {
                edges { node { _id incrementId customerId customerEmail } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [
            'query' => $order->increment_id,
            'customerId' => (int) $order->customer_id,
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $ids = collect($response->json('data.adminOrders.edges'))->pluck('node._id')->all();
        expect($ids)->toContain($order->id);

        foreach ($response->json('data.adminOrders.edges') as $edge) {
            expect((int) $edge['node']['customerId'])->toBe((int) $order->customer_id);
        }
    }

    public function test_orders_query_by_unknown_customer_id_is_empty(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { adminOrders(first: 5, customerId: 99999999) { edges { node { _id } } totalCount } }';

        $response = $this->adminGraphQL($query, [], $admin);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminOrders.totalCount'))->toBe(0);
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
