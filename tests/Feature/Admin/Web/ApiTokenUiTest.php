<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\Web;

use Illuminate\Cache\TaggableStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Webkul\BagistoApi\Admin\Http\Controllers\ApiTokenController;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\BagistoApi\Admin\Services\AdminApiTokenIssuer;
use Webkul\BagistoApi\Admin\Services\IssuedAdminApiToken;
use Webkul\BagistoApi\Tests\Concerns\CraftsAdminJwts;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class ApiTokenUiTest extends TestCase
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
        $this->get(route('admin.settings.api_tokens.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    /**
     * Test the listing renders and its grid lists the tokens without their strings.
     */
    public function test_index_and_datagrid_list_tokens(): void
    {
        $admin = $this->actingAdmin();
        $issued = $this->issue($admin, 'erp.example.com');

        $this->get(route('admin.settings.api_tokens.index'))->assertOk();

        $response = $this->getJson(route('admin.settings.api_tokens.index'), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $row = collect($response->json('records'))->firstWhere('id', $issued->token->id);

        $this->assertNotNull($row);
        $this->assertSame('erp.example.com', $row['domain']);
        $this->assertStringNotContainsString($issued->plainText, $response->getContent());
    }

    /**
     * Test the grid shows only your own tokens without manage_others.
     */
    public function test_the_grid_shows_only_own_tokens_without_manage_others(): void
    {
        $viewer = $this->actingAdminWithPermissions(['settings', 'settings.api_tokens']);

        $own = $this->issue($viewer);
        $foreign = $this->issue($this->createAdmin());

        $ids = collect($this->getJson(route('admin.settings.api_tokens.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->json('records'))->pluck('id')->all();

        $this->assertContains($own->token->id, $ids);
        $this->assertNotContains($foreign->token->id, $ids);
    }

    /**
     * Test each route is closed to a role without its permission.
     */
    public function test_each_route_needs_its_permission(): void
    {
        $owner = $this->createAdmin();
        $token = $this->issue($owner);

        $this->actingAdminWithPermissions(['settings']);

        $this->assertDenied($this->get(route('admin.settings.api_tokens.index')));

        $this->actingAdminWithPermissions(['settings', 'settings.api_tokens']);

        $this->assertDenied($this->get(route('admin.settings.api_tokens.create')));
        $this->assertDenied($this->post(route('admin.settings.api_tokens.store'), $this->validForm($owner)));
        $this->assertDenied($this->deleteJson(route('admin.settings.api_tokens.delete', $token->token->id)));

        $this->assertNull($token->token->fresh()->revoked_at);
    }

    // ========================================================================
    // Creation
    // ========================================================================

    /**
     * Test the create form renders.
     */
    public function test_the_create_form_renders(): void
    {
        $this->actingAdmin();

        $this->get(route('admin.settings.api_tokens.create'))->assertOk();
    }

    /**
     * Test a token is created with the URL reduced to its host and its string shown exactly once.
     */
    public function test_a_token_is_created_and_shown_once(): void
    {
        $admin = $this->actingAdmin();

        $response = $this->post(route('admin.settings.api_tokens.store'), $this->validForm($admin, [
            'name' => 'ERP sync',
            'domain' => 'https://ERP.Example.com/sync?full=1',
        ]));

        $token = AdminApiToken::query()->where('name', 'ERP sync')->latest('id')->first();

        $this->assertNotNull($token);
        $response->assertRedirect(route('admin.settings.api_tokens.view', $token->id));

        $plainText = session(ApiTokenController::PLAIN_TEXT_KEY);

        $this->assertSame('erp.example.com', $token->domain);
        $this->assertSame((int) $admin->id, (int) $token->created_by_admin_id);
        $this->assertSame(substr($plainText, -8), $token->token_preview);
        $this->assertSame('erp.example.com', $this->jwtClaims($plainText)['aud']);

        $this->get(route('admin.settings.api_tokens.view', $token->id))
            ->assertOk()
            ->assertSee($plainText);

        $this->get(route('admin.settings.api_tokens.view', $token->id))
            ->assertOk()
            ->assertDontSee($plainText)
            ->assertSee('erp.example.com');
    }

    /**
     * Test a chosen expiry date means the end of that day.
     */
    public function test_an_expiry_date_ends_at_the_end_of_the_day(): void
    {
        $admin = $this->actingAdmin();

        $date = now()->addDays(10)->format('Y-m-d');

        $this->post(route('admin.settings.api_tokens.store'), $this->validForm($admin, [
            'name' => 'Dated',
            'expires_mode' => 'date',
            'expires_at' => $date,
        ]))->assertRedirect();

        $token = AdminApiToken::query()->where('name', 'Dated')->latest('id')->first();

        $this->assertSame($date.' 23:59:59', $token->expires_at->format('Y-m-d H:i:s'));
    }

    /**
     * Test the form refuses addresses, wildcards, past dates and an unconfirmed missing expiry.
     */
    public function test_the_form_validates_its_input(): void
    {
        $admin = $this->actingAdmin();

        $cases = [
            ['domain', ['domain' => '203.0.113.10']],
            ['domain', ['domain' => '*.example.com']],
            ['name', ['name' => '']],
            ['name', ['name' => str_repeat('n', 101)]],
            ['expires_at', ['expires_mode' => 'date', 'expires_at' => now()->subDay()->format('Y-m-d')]],
            ['expires_at', ['expires_mode' => 'date', 'expires_at' => '']],
            ['no_expiry_confirmed', ['expires_mode' => 'never', 'no_expiry_confirmed' => null]],
            ['expires_mode', ['expires_mode' => 'forever']],
        ];

        $before = AdminApiToken::query()->count();

        foreach ($cases as [$field, $changes]) {
            $this->post(route('admin.settings.api_tokens.store'), $this->validForm($admin, $changes))
                ->assertSessionHasErrors($field);
        }

        $this->assertSame($before, AdminApiToken::query()->count());
    }

    /**
     * Test a token without expiry is refused when the setting turns them off.
     */
    public function test_no_expiry_is_refused_when_the_setting_is_off(): void
    {
        $admin = $this->actingAdmin();

        CoreConfig::query()->where('code', 'api.admin_auth.security.allow_non_expiring_api_tokens')->delete();
        CoreConfig::query()->create(['code' => 'api.admin_auth.security.allow_non_expiring_api_tokens', 'value' => '0']);

        $this->forgetCoreConfigCache();

        $this->post(route('admin.settings.api_tokens.store'), $this->validForm($admin))
            ->assertSessionHasErrors('expires_mode');
    }

    // ========================================================================
    // Escalation Rule
    // ========================================================================

    /**
     * Test creating for another admin needs manage_others.
     */
    public function test_creating_for_another_admin_needs_manage_others(): void
    {
        $this->actingAdminWithPermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.create']);

        $other = $this->createAdminWithPermissions(['settings']);

        $this->post(route('admin.settings.api_tokens.store'), $this->validForm($other))
            ->assertSessionHasErrors('admin_id');

        $this->assertSame(0, AdminApiToken::query()->where('admin_id', $other->id)->count());
    }

    /**
     * Test a limited admin cannot create a token for an admin with a broader role.
     */
    public function test_a_limited_admin_cannot_create_a_token_for_a_broader_admin(): void
    {
        $permissions = ['settings', 'settings.api_tokens', 'settings.api_tokens.create', 'settings.api_tokens.manage_others'];

        $this->actingAdminWithPermissions($permissions);

        $superAdmin = $this->createAdmin();
        $narrower = $this->createAdminWithPermissions(['settings', 'settings.api_tokens']);

        $this->post(route('admin.settings.api_tokens.store'), $this->validForm($superAdmin))
            ->assertSessionHasErrors('admin_id');

        $this->assertSame(0, AdminApiToken::query()->where('admin_id', $superAdmin->id)->count());

        $this->post(route('admin.settings.api_tokens.store'), $this->validForm($narrower))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(1, AdminApiToken::query()->where('admin_id', $narrower->id)->count());
    }

    /**
     * Test an all-access admin may create a token for any admin.
     */
    public function test_an_all_access_admin_may_create_for_anyone(): void
    {
        $this->actingAdmin();

        $other = $this->createAdmin();

        $this->post(route('admin.settings.api_tokens.store'), $this->validForm($other))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, AdminApiToken::query()->where('admin_id', $other->id)->count());
    }

    // ========================================================================
    // Rename And Revoke
    // ========================================================================

    /**
     * Test renaming changes only the name.
     */
    public function test_renaming_changes_only_the_name(): void
    {
        $admin = $this->actingAdmin();
        $issued = $this->issue($admin, 'erp.example.com');

        $this->put(route('admin.settings.api_tokens.update', $issued->token->id), [
            'name' => 'Renamed',
            'domain' => 'evil.example.com',
        ])->assertRedirect(route('admin.settings.api_tokens.view', $issued->token->id));

        $token = $issued->token->fresh();

        $this->assertSame('Renamed', $token->name);
        $this->assertSame('erp.example.com', $token->domain);
    }

    /**
     * Test revoking marks the token revoked once and keeps it in the list.
     */
    public function test_revoking_a_token(): void
    {
        $admin = $this->actingAdmin();
        $issued = $this->issue($admin);

        $this->deleteJson(route('admin.settings.api_tokens.delete', $issued->token->id))->assertOk();

        $token = $issued->token->fresh();

        $this->assertNotNull($token->revoked_at);
        $this->assertSame((int) $admin->id, (int) $token->revoked_by_admin_id);

        $this->deleteJson(route('admin.settings.api_tokens.delete', $issued->token->id))->assertStatus(400);

        $ids = collect($this->getJson(route('admin.settings.api_tokens.index'), ['X-Requested-With' => 'XMLHttpRequest'])->json('records'))->pluck('id')->all();

        $this->assertContains($issued->token->id, $ids);
    }

    /**
     * Test another admin's token is not found without manage_others.
     */
    public function test_another_admins_token_is_not_found_without_manage_others(): void
    {
        $this->actingAdminWithPermissions(['settings', 'settings.api_tokens', 'settings.api_tokens.create', 'settings.api_tokens.delete']);

        $foreign = $this->issue($this->createAdmin());

        $this->get(route('admin.settings.api_tokens.view', $foreign->token->id))->assertNotFound();
        $this->deleteJson(route('admin.settings.api_tokens.delete', $foreign->token->id))->assertNotFound();

        $this->assertNull($foreign->token->fresh()->revoked_at);
    }

    /**
     * Sign API tokens with the test key pair.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->useFixtureJwtKeys();
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
        $admin = $this->createAdminWithPermissions($permissions);

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
     * Create an admin whose role grants exactly the given permissions.
     */
    protected function createAdminWithPermissions(array $permissions): Admin
    {
        $role = Role::create([
            'name' => 'API token UI '.Str::random(8),
            'description' => 'Created by the API token screen tests.',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return Admin::factory()->create(['role_id' => $role->id, 'status' => 1]);
    }

    /**
     * Create an API token for an admin from the console.
     */
    protected function issue(Admin $admin, string $domain = 'localhost'): IssuedAdminApiToken
    {
        return app(AdminApiTokenIssuer::class)->create(null, $admin, 'UI test '.Str::random(6), $domain, null, Request::create('/'));
    }

    /**
     * A valid create form for an admin, with changes.
     */
    protected function validForm(Admin $owner, array $changes = []): array
    {
        return array_merge([
            'name' => 'Integration '.Str::random(6),
            'admin_id' => $owner->id,
            'domain' => 'erp.example.com',
            'expires_mode' => 'never',
            'no_expiry_confirmed' => '1',
        ], $changes);
    }

    /**
     * Flush the core config cache tag when the store supports tags, never the whole store.
     */
    protected function forgetCoreConfigCache(): void
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(CoreConfigRepository::class)->flush();
        }
    }

    /**
     * Assert the panel refused the request.
     */
    protected function assertDenied(TestResponse $response): void
    {
        $this->assertContains($response->getStatusCode(), [401, 403, 302], 'the request was not refused');
    }
}
