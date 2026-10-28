<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;

class SalesInvoicesListTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

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

    public function test_query_filters_by_customer_id(): void
    {
        $admin = $this->createAdmin();
        $order = $this->bootstrapOrderWithInvoice();
        $invoiceId = $order->invoices->first()->id;

        $query = 'query Invoices($customerId: Int) { adminInvoices(first: 50, customerId: $customerId) { edges { node { _id } } totalCount } }';

        $response = $this->adminGraphQL($query, ['customerId' => (int) $order->customer_id], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(collect($response->json('data.adminInvoices.edges'))->pluck('node._id')->all())->toContain($invoiceId);

        $empty = $this->adminGraphQL($query, ['customerId' => 99999999], $admin);

        expect($empty->json('errors'))->toBeNull();
        expect($empty->json('data.adminInvoices.totalCount'))->toBe(0);
    }

    public function test_query_requires_authentication(): void
    {
        $query = 'query { adminInvoices(first: 1) { edges { node { id } } } }';
        $response = $this->adminGraphQL($query);
        expect($response->json('errors'))->not->toBeNull();
    }
}
