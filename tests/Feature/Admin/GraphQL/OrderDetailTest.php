<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

/**
 * GraphQL coverage for the admin Order detail — adminOrderDetail query.
 *
 * Nested collections (items, invoices, shipments) are GraphQL connections —
 * queried via edges/node.
 */
class OrderDetailTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /** Resolve an existing order id from the listing, or bootstrap one. */
    protected function anOrderId(): int
    {
        $admin = $this->createAdmin();
        $rows = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');

        return empty($rows)
            ? $this->bootstrapAdminOrder('pending', false)->id
            : $rows[0]['id'];
    }

    public function test_order_detail_query_returns_the_order(): void
    {
        $id = $this->anOrderId();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query orderDetail($id: ID!) {
              adminOrderDetail(id: $id) {
                id
                incrementId
                status
                grandTotal
                customer { id email group { name } }
                billingAddress { city country }
                items
                invoices
                shipments
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$id], $admin);

        $response->assertOk();
        $data = $response->json('data.adminOrderDetail');

        expect($data)->not->toBeNull();
        expect($data['id'])->toContain((string) $id);
        expect($data['items'])->toBeArray();
    }

    public function test_order_detail_items_carry_the_product_type(): void
    {
        $id = $this->anOrderId();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query orderDetail($id: ID!) {
              adminOrderDetail(id: $id) {
                items
              }
            }
        GQL;

        $items = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$id], $admin)
            ->json('data.adminOrderDetail.items');

        expect($items)->toBeArray()->not->toBeEmpty();
        expect($items[0])->toHaveKeys(['type', 'sku']);
    }

    public function test_order_detail_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query {
              adminOrderDetail(id: "/api/admin/orders/1") { id }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }
}
