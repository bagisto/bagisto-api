<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class RmaCustomFieldTest extends AdminApiTestCase
{
    private function createField(string $code, string $type = 'text'): int
    {
        return DB::table('rma_custom_fields')->insertGetId([
            'code' => $code, 'label' => 'Label', 'type' => $type, 'is_required' => 0,
            'position' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_create_text(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/rma/custom-fields', [
            'code' => 'reason_note_'.uniqid(), 'label' => 'Reason note', 'position' => 1,
            'type' => 'text', 'is_required' => 1, 'status' => 1,
        ]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        expect($response->json('label'))->toBe('Reason note');
        $this->assertDatabaseHas('rma_custom_fields', ['id' => $response->json('id')]);
    }

    public function test_create_select_with_options(): void
    {
        $admin = $this->createAdmin();
        $code = 'condition_'.uniqid();
        $response = $this->adminPost($admin, '/api/admin/rma/custom-fields', [
            'code' => $code, 'label' => 'Condition', 'position' => 2, 'type' => 'select', 'status' => 1,
            'options' => [['name' => 'New', 'value' => 'new'], ['name' => 'Used', 'value' => 'used']],
        ]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        $id = $response->json('id');
        $this->assertDatabaseHas('rma_custom_field_options', ['rma_custom_field_id' => $id, 'name' => 'New', 'value' => 'new']);
        expect($response->json('options'))->toHaveCount(2);
    }

    public function test_create_validation_code_required(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/rma/custom-fields', ['label' => 'x', 'position' => 1, 'type' => 'text']);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_validation_select_needs_options(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/rma/custom-fields', [
            'code' => 'sel_'.uniqid(), 'label' => 'x', 'position' => 1, 'type' => 'select', 'status' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_list_and_get(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createField('list_'.uniqid());
        $list = $this->adminGet($admin, '/api/admin/rma/custom-fields');
        $list->assertOk();
        expect(collect($list->json('data'))->firstWhere('id', $id))->not->toBeNull();
        $get = $this->adminGet($admin, '/api/admin/rma/custom-fields/'.$id);
        $get->assertOk();
        expect((int) $get->json('id'))->toBe($id);
    }

    public function test_update_rebuilds_options(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createField('upd_'.uniqid(), 'select');
        DB::table('rma_custom_field_options')->insert([
            'rma_custom_field_id' => $id, 'name' => 'Old', 'value' => 'old', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $response = $this->putJson('/api/admin/rma/custom-fields/'.$id, [
            'code' => 'upd_new_'.uniqid(), 'label' => 'New label', 'position' => 3, 'type' => 'select', 'status' => 0,
            'options' => [['name' => 'Fresh', 'value' => 'fresh']],
        ], $this->adminHeaders($admin));
        expect($response->getStatusCode())->toBe(200);
        expect($response->json('label'))->toBe('New label');
        $this->assertDatabaseMissing('rma_custom_field_options', ['rma_custom_field_id' => $id, 'name' => 'Old']);
        $this->assertDatabaseHas('rma_custom_field_options', ['rma_custom_field_id' => $id, 'name' => 'Fresh']);
    }

    public function test_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createField('del_'.uniqid());
        $response = $this->deleteJson('/api/admin/rma/custom-fields/'.$id, [], $this->adminHeaders($admin));
        expect($response->getStatusCode())->toBeIn([200, 204]);
        $this->assertDatabaseMissing('rma_custom_fields', ['id' => $id]);
    }

    public function test_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->createField('a_'.uniqid());
        $b = $this->createField('b_'.uniqid());
        $response = $this->adminPost($admin, '/api/admin/rma/custom-fields/mass-delete', ['indices' => [$a, $b]]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseMissing('rma_custom_fields', ['id' => $a]);
    }

    public function test_mass_update_status(): void
    {
        $admin = $this->createAdmin();
        $id = $this->createField('t_'.uniqid());
        $response = $this->adminPost($admin, '/api/admin/rma/custom-fields/mass-update-status', ['indices' => [$id], 'value' => 0]);
        expect($response->getStatusCode())->toBeIn([200, 201]);
        $this->assertDatabaseHas('rma_custom_fields', ['id' => $id, 'status' => 0]);
    }
}
