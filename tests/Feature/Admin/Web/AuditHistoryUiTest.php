<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\Core\Models\Currency;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * The Integration → History admin screen: renders without 500, surfaces the
 * before/after diff, gates deletion, and the cleanup tools work.
 */
class AuditHistoryUiTest extends TestCase
{
    protected function actingAdmin(): Admin
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    protected function seedAudit(array $overrides = []): AdminApiAudit
    {
        return AdminApiAudit::create(array_merge([
            'history_id' => (string) Str::uuid(),
            'version_id' => 1,
            'event' => 'updated',
            'auditable_type' => Currency::class,
            'auditable_id' => 5,
            'old_values' => ['name' => 'Old'],
            'new_values' => ['name' => 'New'],
            'user_type' => Admin::class,
            'user_id' => 1,
            'admin_name' => 'Jane Admin',
            'token_id' => 3,
            'token_name' => 'CI Token',
            'method' => 'PUT',
            'url' => 'https://store.test/api/admin/settings/currencies/5',
            'ip_address' => '203.0.113.4',
            'user_agent' => 'curl/8.0',
            'tags' => 'settings.currencies',
            'created_at' => now(),
        ], $overrides));
    }

    public function test_index_requires_authentication(): void
    {
        $this->get(route('admin.integration.history.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    public function test_index_renders_with_no_500(): void
    {
        $this->actingAdmin();
        $this->seedAudit();

        $this->get(route('admin.integration.history.index'))->assertOk();
    }

    public function test_index_renders_with_empty_table(): void
    {
        $this->actingAdmin();
        AdminApiAudit::query()->delete();

        $this->get(route('admin.integration.history.index'))->assertOk();
    }

    public function test_datagrid_ajax_renders_with_no_500(): void
    {
        $this->actingAdmin();
        $this->seedAudit();

        $this->get(route('admin.integration.history.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();
    }

    public function test_detail_view_shows_before_after(): void
    {
        $this->actingAdmin();
        $audit = $this->seedAudit();

        $this->get(route('admin.integration.history.view', $audit->id))
            ->assertOk()
            ->assertSee('Old')
            ->assertSee('New');
    }

    public function test_detail_view_does_not_500_on_null_values(): void
    {
        $this->actingAdmin();
        $audit = $this->seedAudit([
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => null,
            'new_values' => null,
            'event' => 'deleted',
            'admin_name' => null,
            'token_name' => null,
        ]);

        $this->get(route('admin.integration.history.view', $audit->id))->assertOk();
    }

    public function test_detail_view_404_on_missing(): void
    {
        $this->actingAdmin();
        $this->get(route('admin.integration.history.view', 99999999))->assertNotFound();
    }

    public function test_mass_delete_removes_selected_rows(): void
    {
        $this->actingAdmin();
        $a = $this->seedAudit();
        $b = $this->seedAudit();

        $this->post(route('admin.integration.history.mass_delete'), ['indices' => [$a->id]])
            ->assertOk();

        expect(AdminApiAudit::find($a->id))->toBeNull();
        expect(AdminApiAudit::find($b->id))->not->toBeNull();
    }

    public function test_mass_delete_denied_without_permission(): void
    {
        $role = Role::create([
            'name' => 'Limited '.uniqid(), 'permission_type' => 'custom',
            'permissions' => ['integration', 'integration.history'],
        ]);
        $admin = Admin::factory()->create(['role_id' => $role->id]);
        $this->actingAs($admin, 'admin');

        $row = $this->seedAudit();

        $response = $this->post(route('admin.integration.history.mass_delete'), ['indices' => [$row->id]]);

        expect(in_array($response->getStatusCode(), [401, 403, 302], true))->toBeTrue();
        expect(AdminApiAudit::find($row->id))->not->toBeNull();
    }

    public function test_cleanup_older_than_deletes_old_keeps_recent(): void
    {
        $this->actingAdmin();
        $old = $this->seedAudit(['created_at' => Carbon::now()->subDays(120)]);
        $recent = $this->seedAudit(['created_at' => Carbon::now()->subDays(2)]);

        $this->post(route('admin.integration.history.cleanup'), ['days' => 30])
            ->assertRedirect(route('admin.integration.history.index'));

        expect(AdminApiAudit::find($old->id))->toBeNull();
        expect(AdminApiAudit::find($recent->id))->not->toBeNull();
    }

    public function test_cleanup_requires_input(): void
    {
        $this->actingAdmin();
        $row = $this->seedAudit();

        $this->post(route('admin.integration.history.cleanup'), [])
            ->assertRedirect(route('admin.integration.history.index'));

        expect(AdminApiAudit::find($row->id))->not->toBeNull();
    }
}
