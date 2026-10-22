<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenPair;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenService;
use Webkul\BagistoApi\Tests\Concerns\CraftsAdminJwts;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class ApiSessionUiTest extends TestCase
{
    use CraftsAdminJwts;

    // ========================================================================
    // Listing
    // ========================================================================

    /**
     * Test the listing needs a signed-in admin.
     */
    public function test_index_requires_authentication(): void
    {
        $this->get(route('admin.settings.api_sessions.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    /**
     * Test the listing renders and its grid shows every admin's sessions with their details.
     */
    public function test_index_and_datagrid_list_sessions(): void
    {
        $this->actingAdmin();

        $phone = $this->issueAdminSession($this->createAdmin(), ['name' => 'Warehouse phone', 'client_type' => 'mobile', 'ip_address' => '203.0.113.4'])->session;

        $this->get(route('admin.settings.api_sessions.index'))->assertOk();

        $row = collect($this->grid()->json('records'))->firstWhere('id', $phone->id);

        $this->assertNotNull($row);
        $this->assertSame('Warehouse phone', $row['name']);
        $this->assertSame('203.0.113.4', $row['ip_address']);
    }

    /**
     * Test the grid filters by client type.
     */
    public function test_the_grid_filters_by_client_type(): void
    {
        $this->actingAdmin();

        $admin = $this->createAdmin();
        $pos = $this->issueAdminSession($admin, ['client_type' => 'pos'])->session;
        $web = $this->issueAdminSession($admin, ['client_type' => 'web'])->session;

        $ids = collect($this->grid(['filters' => ['client_type' => ['pos']]])->json('records'))->pluck('id')->all();

        $this->assertContains($pos->id, $ids);
        $this->assertNotContains($web->id, $ids);
    }

    /**
     * Test the listing and the revoke actions are closed to a role without their permission.
     */
    public function test_each_route_needs_its_permission(): void
    {
        $target = $this->createAdmin();
        $session = $this->issueAdminSession($target)->session;

        $this->actingAdminWithPermissions(['settings']);

        $this->assertDenied($this->get(route('admin.settings.api_sessions.index')));

        $this->actingAdminWithPermissions(['settings', 'settings.api_sessions']);

        $this->grid()->assertOk();
        $this->assertDenied($this->deleteJson(route('admin.settings.api_sessions.delete', $session->id)));
        $this->assertDenied($this->postJson(route('admin.settings.api_sessions.revoke_all', $target->id)));

        $this->assertNull($session->fresh()->revoked_at);
    }

    // ========================================================================
    // Revocation
    // ========================================================================

    /**
     * Test revoking one session stops its tokens and leaves the admin's other sessions alone.
     */
    public function test_revoking_one_session(): void
    {
        $this->actingAdmin();

        $admin = $this->createAdmin();
        $lost = $this->issueAdminSession($admin);
        $kept = $this->issueAdminSession($admin);

        $this->deleteJson(route('admin.settings.api_sessions.delete', $lost->session->id))->assertOk();

        $this->assertSame(AdminApiSession::REVOKED_BY_ADMIN, $lost->session->fresh()->revoked_reason);
        $this->assertNull($kept->session->fresh()->revoked_at);

        $this->getJson('/api/admin/get', ['Authorization' => 'Bearer '.$lost->accessToken])->assertStatus(401);

        $this->deleteJson(route('admin.settings.api_sessions.delete', $lost->session->id))->assertStatus(400);
    }

    /**
     * Test revoking every session of an admin.
     */
    public function test_revoking_every_session_of_an_admin(): void
    {
        $this->actingAdmin();

        $admin = $this->createAdmin();
        $first = $this->issueAdminSession($admin)->session;
        $second = $this->issueAdminSession($admin)->session;
        $other = $this->issueAdminSession($this->createAdmin())->session;

        $this->postJson(route('admin.settings.api_sessions.revoke_all', $admin->id))->assertOk();

        $this->assertNotNull($first->fresh()->revoked_at);
        $this->assertNotNull($second->fresh()->revoked_at);
        $this->assertNull($other->fresh()->revoked_at);
    }

    /**
     * Test revoking an unknown session or admin answers 404.
     */
    public function test_revoking_unknown_records_is_not_found(): void
    {
        $this->actingAdmin();

        $this->deleteJson(route('admin.settings.api_sessions.delete', 999999999))->assertNotFound();
        $this->postJson(route('admin.settings.api_sessions.revoke_all', 999999999))->assertNotFound();
    }

    /**
     * Sign access tokens with the test key pair.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureJwtKeys();
    }

    /**
     * Load the sessions grid data.
     */
    protected function grid(array $query = []): TestResponse
    {
        return $this->getJson(route('admin.settings.api_sessions.index', $query), ['X-Requested-With' => 'XMLHttpRequest']);
    }

    /**
     * Start a login session for an admin.
     */
    protected function issueAdminSession(Admin $admin, array $client = []): AdminApiTokenPair
    {
        return app(AdminApiTokenService::class)->issue($admin, array_merge([
            'name' => 'UI test '.Str::random(6),
            'client_type' => AdminApiSession::CLIENT_TYPE_OTHER,
        ], $client));
    }

    /**
     * Sign in as a new all-access admin.
     */
    protected function actingAdmin(): Admin
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Sign in as a new admin whose role grants exactly the given permissions.
     */
    protected function actingAdminWithPermissions(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'API session UI '.Str::random(8),
            'description' => 'Created by the API session screen tests.',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin = Admin::factory()->create(['role_id' => $role->id, 'status' => 1]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * Create an all-access admin.
     */
    protected function createAdmin(): Admin
    {
        return Admin::factory()->create([
            'role_id' => Role::query()->where('permission_type', 'all')->value('id'),
            'status' => 1,
        ]);
    }

    /**
     * Assert the panel refused the request.
     */
    protected function assertDenied(TestResponse $response): void
    {
        $this->assertContains($response->getStatusCode(), [401, 403, 302], 'the request was not refused');
    }
}
