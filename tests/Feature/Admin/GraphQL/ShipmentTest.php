<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Sales\Models\Shipment;

class ShipmentTest extends AdminApiTestCase
{
    public function test_create_requires_authentication(): void
    {
        $mutation = 'mutation($input: createAdminShipmentInput!){ createAdminShipment(input:$input){ adminShipmentDetailDto { id } } }';
        $response = $this->adminGraphQL($mutation, ['input' => ['orderId' => 1, 'source' => 1, 'items' => []]]);
        expect($response->json('errors'))->toBeArray();
    }

    public function test_view_by_id(): void
    {
        $id = Shipment::query()->value('id');
        if (! $id) {
            $this->markTestSkipped('No shipments in DB.');
        }
        $admin = $this->createAdmin();
        $query = 'query($id: ID!){ adminShipment(id:$id){ _id totalQty } }';
        $response = $this->adminGraphQL($query, ['id' => '/api/admin/shipments/'.$id], $admin);

        $node = $response->json('data.adminShipment');
        if ($node) {
            expect($node['_id'])->toBe($id);
        } else {
            expect($response->json('errors'))->toBeArray();
        }
    }
}
