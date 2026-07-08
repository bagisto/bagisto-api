<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaRuleTest extends AdminApiTestCase
{
    private function createRule(string $name = 'Rule'): int
    {
        return DB::table('rma_rules')->insertGetId([
            'name' => $name,
            'description' => 'desc',
            'status' => 1,
            'return_period' => 30,
            'default' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_create(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/rma/rules', [
            'name' => 'Electronics 15-day', 'description' => 'Electronics window', 'status' => 1, 'return_period' => 15,
        ]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('name'))->toBe('Electronics 15-day');
        expect((int) $response->json('returnPeriod'))->toBe(15);
        $this->assertDatabaseHas('rma_rules', ['id' => $response->json('id')]);
    }

    public function test_create_validation(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/rma/rules', ['name' => 'No desc', 'status' => 1]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_list_and_get(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createRule('Listable-'.uniqid());
        $list = $this->adminGet($admin, '/api/admin/rma/rules');
        $list->assertOk();
        expect(collect($list->json('data'))->firstWhere('id', $id))->not->toBeNull();
        $get = $this->adminGet($admin, '/api/admin/rma/rules/'.$id);
        $get->assertOk();
        expect((int) $get->json('id'))->toBe($id);
    }

    public function test_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createRule('Old-'.uniqid());
        $response = $this->putJson('/api/admin/rma/rules/'.$id, [
            'name' => 'New', 'description' => 'newdesc', 'status' => 0, 'return_period' => 7,
        ], $this->adminHeaders($admin));
        expect($response->getStatusCode())->toBe(200);
        expect($response->json('name'))->toBe('New');
        expect((int) $response->json('returnPeriod'))->toBe(7);
    }

    public function test_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createRule('Del-'.uniqid());
        $response = $this->deleteJson('/api/admin/rma/rules/'.$id, [], $this->adminHeaders($admin));
        expect($response->getStatusCode())->toBeIn([200, 204]);
        $this->assertDatabaseMissing('rma_rules', ['id' => $id]);
    }

    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createRule('A-'.uniqid());
        $b = $this->createRule('B-'.uniqid());
        $response = $this->adminPost($admin, '/api/admin/rma/rules/mass-delete', ['indices' => [$a, $b]]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseMissing('rma_rules', ['id' => $a]);
    }

    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createRule('T-'.uniqid());
        $response = $this->adminPost($admin, '/api/admin/rma/rules/mass-update-status', ['indices' => [$id], 'value' => 0]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('rma_rules', ['id' => $id, 'status' => 0]);
    }
}
