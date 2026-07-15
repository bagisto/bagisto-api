<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Sales\Models\OrderComment;

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

        // Nested data is field-selectable: collections are connections
        // (items { edges { node } }), customer is a typed object.
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

        // customer is a typed object with a typed group; null only for guest orders.
        if ($data['customer'] !== null) {
            expect($data['customer']['email'])->not->toBeNull();
        }

        // addresses is a connection; each node carries addressType.
        expect($data['addresses']['edges'])->toBeArray();
        foreach ($data['addresses']['edges'] as $edge) {
            expect($edge['node']['addressType'])->not->toBeNull();
        }
    }

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
        // Catalog type, not the morph class.
        expect($edges[0]['node']['type'])->not->toContain('\\');
    }

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
