<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaReasonTest extends AdminApiTestCase
{
    private function createReason(string $title = 'Wrong size', array $resolutions = ['return']): int
    {
        $id = DB::table('rma_reasons')->insertGetId([
            'title'      => $title,
            'status'     => 1,
            'position'   => 1,
            'is_admin'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($resolutions as $r) {
            DB::table('rma_reason_resolutions')->insert(['rma_reason_id' => $id, 'resolution_type' => $r]);
        }

        return $id;
    }

    public function test_create(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/rma/reasons', [
            'title'           => 'Defective item',
            'status'          => 1,
            'position'        => 2,
            'resolution_type' => ['return', 'cancel_items'],
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('title'))->toBe('Defective item');
        expect($response->json('resolutionType'))->toContain('return');
        $this->assertDatabaseHas('rma_reasons', ['title' => 'Defective item']);
    }

    public function test_create_validation(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/rma/reasons', [
            'title'  => 'No resolution',
            'status' => 1,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_list_and_get(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createReason('Listable');

        $list = $this->adminGet($admin, '/api/admin/rma/reasons');
        $list->assertOk();
        $row = collect($list->json('data'))->firstWhere('id', $id);
        expect($row)->not->toBeNull();

        $get = $this->adminGet($admin, '/api/admin/rma/reasons/'.$id);
        $get->assertOk();
        expect($get->json('title'))->toBe('Listable');
        expect($get->json('resolutionType'))->toContain('return');
    }

    public function test_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createReason('Old', ['return']);

        $response = $this->putJson('/api/admin/rma/reasons/'.$id, [
            'title'           => 'New',
            'status'          => 0,
            'position'        => 5,
            'resolution_type' => ['cancel_items'],
        ], $this->adminHeaders($admin));

        expect($response->getStatusCode())->toBe(200);
        expect($response->json('title'))->toBe('New');
        expect($response->json('resolutionType'))->toBe(['cancel_items']);
        $this->assertDatabaseMissing('rma_reason_resolutions', ['rma_reason_id' => $id, 'resolution_type' => 'return']);
    }

    public function test_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createReason();

        $response = $this->deleteJson('/api/admin/rma/reasons/'.$id, [], $this->adminHeaders($admin));

        expect($response->getStatusCode())->toBeIn([200, 204]);
        $this->assertDatabaseMissing('rma_reasons', ['id' => $id]);
    }

    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createReason('A');
        $b = $this->createReason('B');

        $response = $this->adminPost($admin, '/api/admin/rma/reasons/mass-delete', ['indices' => [$a, $b]]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseMissing('rma_reasons', ['id' => $a]);
        $this->assertDatabaseMissing('rma_reasons', ['id' => $b]);
    }

    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createReason('A');
        $b = $this->createReason('B');

        $response = $this->adminPost($admin, '/api/admin/rma/reasons/mass-update-status', ['indices' => [$a, $b], 'value' => 0]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('rma_reasons', ['id' => $a, 'status' => 0]);
    }
}
