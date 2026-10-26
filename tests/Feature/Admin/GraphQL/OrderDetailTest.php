<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\OrderComment;

class OrderDetailTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test order detail query returns the order.
     */
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
                statusLabel
                channelName
                grandTotal
                formattedGrandTotal
                totalDue
                customer {
                  name
                  email
                }
                items {
                  edges {
                    node {
                      sku
                      qtyOrdered
                    }
                  }
                }
                invoices {
                  edges {
                    node {
                      _id
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$id], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.adminOrderDetail');

        expect($data)->not->toBeNull();
        expect($data['id'])->toContain((string) $id);
        expect($data['items']['edges'])->toBeArray();

        expect($data['incrementId'])->not->toBeNull();
        expect($data['statusLabel'])->not->toBeNull();
        expect($data['channelName'])->not->toBeNull();
        expect($data['grandTotal'])->not->toBeNull();
        expect($data['formattedGrandTotal'])->not->toBeNull();
        expect($data['totalDue'])->not->toBeNull();
    }

    /**
     * Test order detail resolves customer typed object and addresses connection.
     */
    public function test_order_detail_resolves_customer_typed_object_and_addresses_connection(): void
    {
        $order = $this->bootstrapInvoiceableOrder('processing');

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query orderDetail($id: ID!) {
              adminOrderDetail(id: $id) {
                customer {
                  email
                  group {
                    code
                  }
                }
                addresses {
                  edges {
                    node {
                      addressType
                      city
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$order->id], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.adminOrderDetail');

        if ($data['customer'] !== null) {
            expect($data['customer']['email'])->not->toBeNull();
        }

        expect($data['addresses']['edges'])->toBeArray();
        foreach ($data['addresses']['edges'] as $edge) {
            expect($edge['node']['addressType'])->not->toBeNull();
        }
    }

    /**
     * Test order detail items connection carries the product type.
     */
    public function test_order_detail_items_connection_carries_the_product_type(): void
    {
        $id = $this->anOrderId();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query orderDetail($id: ID!) {
              adminOrderDetail(id: $id) {
                items {
                  edges {
                    node {
                      type
                      sku
                    }
                  }
                }
              }
            }
        GQL;

        $edges = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$id], $admin)
            ->json('data.adminOrderDetail.items.edges');

        expect($edges)->toBeArray()->not->toBeEmpty();
        expect($edges[0]['node'])->toHaveKeys(['type', 'sku']);
        expect($edges[0]['node']['type'])->not->toContain('\\');
    }

    /**
     * Test order detail items resolve their JSON child and downloadable links.
     */
    public function test_order_detail_items_resolve_child_and_downloadable_links(): void
    {
        $id = $this->anOrderId();

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query orderDetail($id: ID!) {
              adminOrderDetail(id: $id) {
                items {
                  edges {
                    node {
                      sku
                      child
                      downloadableLinks
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$id], $admin);

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.adminOrderDetail.items.edges.0.node');

        expect($node)->toHaveKeys(['child', 'downloadableLinks']);
        expect($node['downloadableLinks'])->toBeArray();
    }

    /**
     * Test order detail embeds comments and refunds connections.
     */
    public function test_order_detail_embeds_comments_and_refunds_connections(): void
    {
        $order = $this->bootstrapAdminOrder('pending', false);

        OrderComment::create([
            'order_id' => $order->id,
            'comment' => 'GQL QA comment',
            'customer_notified' => 0,
        ]);

        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query orderDetail($id: ID!) {
              adminOrderDetail(id: $id) {
                refunds {
                  edges {
                    node {
                      _id
                    }
                  }
                }
                comments {
                  edges {
                    node {
                      comment
                    }
                  }
                }
              }
            }
        GQL;

        $data = $this->adminGraphQL($query, ['id' => '/api/admin/orders/'.$order->id], $admin)
            ->json('data.adminOrderDetail');

        expect($data)->not->toBeNull();
        expect($data['refunds']['edges'])->toBeArray();
        expect($data['comments']['edges'])->toBeArray();
        expect(collect($data['comments']['edges'])->pluck('node.comment'))->toContain('GQL QA comment');
    }

    /**
     * Test order detail query requires authentication.
     */
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

    /**
     * Resolve an existing order id from the listing, or bootstrap one.
     */
    protected function anOrderId(): int
    {
        $admin = $this->createAdmin();
        $rows = $this->adminGet($admin, '/api/admin/orders?per_page=1')->json('data');

        return empty($rows)
            ? $this->bootstrapAdminOrder('pending', false)->id
            : $rows[0]['id'];
    }
}
