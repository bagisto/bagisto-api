<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaStatusTest extends AdminApiTestCase
{
    private function createStatus(string $title, int $default = 0): int
    {
        return DB::table('rma_statuses')->insertGetId([
            'title'      => $title,
            'status'     => 1,
            'color'      => '#abcdef',
            'default'    => $default,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_create(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/rma/statuses', [
            'title'  => 'Inspecting-'.uniqid(),
            'status' => 1,
            'color'  => '#00ff00',
        ]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('color'))->toBe('#00ff00');
        $this->assertDatabaseHas('rma_statuses', ['id' => $response->json('id')]);
    }

    public function test_create_duplicate_title_rejected(): void
    {
        $admin = $this->createAdmin();
        $title = 'Dupe-'.uniqid();
        $this->createStatus($title);

        $response = $this->adminPost($admin, '/api/admin/rma/statuses', ['title' => $title, 'status' => 1]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_list_and_get(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('Listable-'.uniqid());

        $list = $this->adminGet($admin, '/api/admin/rma/statuses');
        $list->assertOk();
        expect(collect($list->json('data'))->firstWhere('id', $id))->not->toBeNull();

        $get = $this->adminGet($admin, '/api/admin/rma/statuses/'.$id);
        $get->assertOk();
        expect((int) $get->json('id'))->toBe($id);
    }

    public function test_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('Old-'.uniqid());

        $response = $this->putJson('/api/admin/rma/statuses/'.$id, [
            'title'  => 'New-'.uniqid(),
            'status' => 0,
            'color'  => '#111111',
        ], $this->adminHeaders($admin));

        expect($response->getStatusCode())->toBe(200);
        expect($response->json('color'))->toBe('#111111');
    }

    public function test_delete_non_default(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('Deletable-'.uniqid());

        $response = $this->deleteJson('/api/admin/rma/statuses/'.$id, [], $this->adminHeaders($admin));

        expect($response->getStatusCode())->toBeIn([200, 204]);
        $this->assertDatabaseMissing('rma_statuses', ['id' => $id]);
    }

    public function test_delete_default_rejected(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('DefaultStatus-'.uniqid(), 1);

        $response = $this->deleteJson('/api/admin/rma/statuses/'.$id, [], $this->adminHeaders($admin));

        expect($response->getStatusCode())->toBe(422);
        $this->assertDatabaseHas('rma_statuses', ['id' => $id]);
    }

    public function test_mass_delete_skips_default(): void
    {
        $admin = $this->createAdmin();
        $keep = $this->createStatus('DefaultKeep-'.uniqid(), 1);
        $gone = $this->createStatus('Gone-'.uniqid());

        $response = $this->adminPost($admin, '/api/admin/rma/statuses/mass-delete', ['indices' => [$keep, $gone]]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('rma_statuses', ['id' => $keep]);
        $this->assertDatabaseMissing('rma_statuses', ['id' => $gone]);
    }

    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createStatus('Toggle-'.uniqid());

        $response = $this->adminPost($admin, '/api/admin/rma/statuses/mass-update-status', ['indices' => [$id], 'value' => 0]);

        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('rma_statuses', ['id' => $id, 'status' => 0]);
    }
}
