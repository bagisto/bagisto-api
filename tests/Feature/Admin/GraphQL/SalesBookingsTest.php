<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SalesBookingsTest extends AdminApiTestCase
{
    public function test_list_query(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminBookings(first: 5) {
                edges { node { id qty } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminBookings.edges'))->toBeArray();
    }

    public function test_detail_404(): void
    {
        $admin = $this->createAdmin();
        $query = 'query Q($id: ID!) { adminBooking(id: $id) { id } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/bookings/99999999'], $admin);

        $response->assertOk();
        if ($response->json('data.adminBooking') !== null) {
            expect($response->json('errors'))->not->toBeNull();
        }
    }

    public function test_requires_authentication(): void
    {
        $response = $this->adminGraphQL('query { adminBookings(first: 1) { edges { node { id } } } }');
        expect($response->json('errors'))->not->toBeNull();
    }
}
