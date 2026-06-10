<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class SalesBookingsTest extends AdminApiTestCase
{
    public function test_detail_resolves_id_and_multiword_fields(): void
    {
        $bookingId = DB::table('bookings')->value('id');
        if (! $bookingId) {
            $this->markTestSkipped('No booking rows seeded in the test database.');
        }

        $admin = $this->createAdmin();
        $query = 'query Q($id: ID!) { adminBooking(id: $id) { id _id bookingType qty from fromFormatted productName order orderItem } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/bookings/'.$bookingId], $admin);

        expect($response->json('errors'))->toBeNull();
        $node = $response->json('data.adminBooking');
        expect($node)->not->toBeNull();
        expect($node['id'])->toBe('/api/admin/bookings/'.$bookingId);
        expect($node['_id'])->toBe((int) $bookingId);
    }

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
